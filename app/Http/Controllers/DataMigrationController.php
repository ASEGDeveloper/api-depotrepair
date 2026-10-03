<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DataMigrationController extends Controller
{
    use ApiResponse;

    // 17 columns per row; keeps each insert under SQL Server's 2100 parameter limit
    private const INSERT_CHUNK = 100;

    private const TARGET_TABLE = 'deporepair.tna_entries_uat';

    /**
     * Migrate bulk data from the external TAS database (TAS_JOBTIMESHEET)
     * into deporepair.tna_entries_uat. Replaces the old tna cron script.
     */
    public function migrateFromTas(Request $request): JsonResponse
    {
        set_time_limit(0);

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        try {
            $rows = DB::connection('tas')
                ->table('TAS_JOBTIMESHEET')
                ->whereBetween('STARTDATE', [$startDate, $endDate])
                ->orderBy('STARTDATE')
                ->cursor();

            DB::statement('TRUNCATE TABLE ' . self::TARGET_TABLE);

            $success = 0;
            $errors  = 0;
            $batch   = [];

            foreach ($rows as $row) {
                $batch[] = $this->mapRow($row);

                if (count($batch) >= self::INSERT_CHUNK) {
                    [$ok, $failed] = $this->insertBatch($batch);
                    $success += $ok;
                    $errors  += $failed;
                    $batch = [];
                }
            }

            if ($batch) {
                [$ok, $failed] = $this->insertBatch($batch);
                $success += $ok;
                $errors  += $failed;
            }

            return $this->successResponse([
                'noOfRecordUpdate' => $success,
                'noOfError'        => $errors,
            ], 'TAS migration completed.');
        } catch (\Throwable $e) {
            Log::error('TAS migration failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('TAS migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Insert a chunk in one statement; if it fails, retry row by row so one
     * bad record doesn't drop the whole chunk.
     */
    private function insertBatch(array $batch): array
    {
        try {
            DB::table(self::TARGET_TABLE)->insert($batch);
            return [count($batch), 0];
        } catch (\Throwable $e) {
            $ok = 0;
            $failed = 0;
            foreach ($batch as $record) {
                try {
                    DB::table(self::TARGET_TABLE)->insert($record);
                    $ok++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('TAS migration row failed', ['row' => $record, 'error' => $e->getMessage()]);
                }
            }
            return [$ok, $failed];
        }
    }

    private function mapRow(object $row): array
    {
        $startTime = $this->toFloat($row->STARTTIME);
        $endTime   = $this->toFloat($row->ENDTIME);

        $startDate = Carbon::parse($row->STARTDATE);
        $endDate   = $row->ENDDATE ? Carbon::parse($row->ENDDATE) : null;

        return [
            'COMPANYCODE'      => $row->COMPANYCODE,
            'EMPLOYEECODE'     => $row->EMPLOYEECODE,
            'JOBCODE'          => (int) str_replace('/', '', trim((string) $row->JOBCODE)),
            'STARTDATE'        => $startDate->format('Y-m-d H:i:s'),
            'STARTTIME'        => $startTime,
            'ENDDATE'          => $endDate?->format('Y-m-d H:i:s'),
            'ENDTIME'          => $endTime,
            'JOBSEQNO'         => $row->JOBSEQNO,
            'EXPORTFLAG'       => $row->EXPORTFLAG,
            'OPST'             => $row->OPST,
            'PROJECTEDENDDATE' => $row->PROJECTEDENDDATE ? Carbon::parse($row->PROJECTEDENDDATE)->format('Y-m-d H:i:s') : null,
            'PROJECTEDENDTIME' => $row->PROJECTEDENDTIME,
            'OR_UPD_FLG'       => $row->OR_UPD_FLG,
            'TAS_DATA_FROM'    => $row->TAS_DATA_FROM,
            'ENTRY_MODE'       => 1,
            'SD'               => $startDate->format('Y-m-d') . ' ' . $this->hm($startTime) . ':00',
            'ED'               => $endDate ? $endDate->format('Y-m-d') . ' ' . $this->hm($endTime) . ':00' : '1900-01-01 00:00:00',
        ];
    }

    /**
     * PDO returns SQL Server floats as strings like "8.5199999999999996";
     * round-trip through float so they read "8.52", as the old sqlsrv_* code saw them.
     */
    private function toFloat($value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Convert TAS decimal time (8.52, 7.3) to "HH:MM" (08:52, 07:30).
     * A single minute digit is tens of minutes.
     */
    private function hm(?float $time): string
    {
        $t = explode('.', (string) ($time ?? 0));

        $hh = str_pad($t[0], 2, '0', STR_PAD_LEFT);
        $mm = isset($t[1])
            ? (strlen($t[1]) > 1 ? $t[1] : $t[1] . '0')
            : '00';

        return $hh . ':' . $mm;
    }
}
