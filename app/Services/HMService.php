<?php

namespace App\Services;

use App\Models\TnaEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Constants\Status;
use Exception;
use App\Traits\ApiResponse;


class HMService
{
    use ApiResponse;

    protected TnaService $tnaService;

    public function __construct(TnaService $tnaService)
    {
        $this->tnaService = $tnaService;
    }


    public function createHM($request)
    {
        try {
            // Check if job card is already open
            $isJobOpen = $this->tnaService->checkJobCardPunchingStatus(
                $request->employeecode,
                $request->jobcode
            );

            if ($isJobOpen) {
                // Return immediately if job is already open
                return $this->errorResponse('This job is already open. Please close the existing job before proceeding.', 422);
            }

            $jobCardOpen = $this->tnaService->getOpenJobCode($request->employeecode);  // employee wise

            if ($jobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$jobCardOpen' is already open ", 422);
            }

            $duplicate = $this->checkDuplicate($request);

            if (!$duplicate['success']) {
                return $this->errorResponse($duplicate['message'], 422);
            }

            // Initialize $data outside closure
            $data = null;

            DB::transaction(function () use ($request, &$data) {
                $data = TnaEntry::create([
                    'COMPANYCODE'        => $request->companycode,
                    'EMPLOYEECODE'       => $request->employeecode,
                    'JOBCODE'            => $request->jobcode,
                    'STARTDATE'          => $request->startdate,
                    'STARTTIME'          => $request->starttime,
                    'SD'                 => $request->startdate,
                    'JOBSEQNO'           => 1,
                    'EXPORTFLAG'         => 'Y',
                    'OPST'               => 0,
                    'OR_UPD_FLG'         => 'U',
                    'ENTRY_MODE'         => 'Auto',
                    'IS_MANUAL'          => 'N',
                    'TAS_DATA_FROM'      => $request->tas_data_from,
                    'PROJECTEDENDDATE'   => '2025-10-12',
                    'PROJECTEDENDTIME'   => '18:00',
                    'Action'             => Status::START,
                ]);
            });

            // ✅ Return created record
            return $this->successResponse($data, 'Record created successfully.');
        } catch (\Exception $e) {

            Log::error('HM creation failed', [
                'error'   => $e->getMessage(),
                'request' => (array) $request
            ]);

            return $this->errorResponse('Failed to create record. Please try again later.');
        }
    }



public function updateHM($request): array
{
    try {
        $affectedRows = TnaEntry::where('COMPANYCODE', $request->companycode)
            ->where('EMPLOYEECODE', $request->employeecode)
            ->where('JOBCODE', $request->jobcode)
            ->whereNull('ENDTIME')
            ->update([
                'ENDDATE' => $request->enddate,
                'ENDTIME' => $request->endtime,
                'ED'      => $request->enddate,
                'Action'  => Status::CLOSED
            ]);

        Log::info('Affected rows', [
            'count'        => $affectedRows,
            'EMPLOYEECODE' => $request->employeecode,
            'JOBCODE'      => $request->jobcode,
        ]);

        if (!$affectedRows) {
            throw new \Exception(
                "No open job card found for employee '{$request->employeecode}' " .
                "with job code '{$request->jobcode}'."
            );
        }

        return [
            'success' => true,
            'message' => 'Job card updated and closed successfully.'
        ];

    } catch (\Throwable $e) {
        Log::error('HM update failed', [
            'error'        => $e->getMessage(),
            'EMPLOYEECODE' => $request->employeecode,
            'JOBCODE'      => $request->jobcode,
        ]);

        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}


// public function updateHM($request): array
// {
//     try {
//         $affectedRows = TnaEntry::where('COMPANYCODE', $request->companycode)
//             ->where('EMPLOYEECODE', $request->employeecode)
//             ->where('JOBCODE', $request->jobcode)
//             ->where('STARTDATE', $request->startdate)
//             ->where('STARTTIME', $request->starttime)
//             ->update([
//                 'ENDDATE' => $request->enddate,
//                 'ENDTIME' => $request->endtime,
//                 'ED'      => $request->enddate,
//                 'Action'  => Status::CLOSED
//             ]);

//         Log::info('Affected rows', [
//             'count'        => $affectedRows,
//             'EMPLOYEECODE' => $request->employeecode,
//             'JOBCODE'      => $request->jobcode,
//         ]);

//         if (!$affectedRows) {
//             throw new \Exception(
//                 "No matching job card found for employee '{$request->employeecode}' " .
//                 "with job code '{$request->jobcode}' on '{$request->startdate} {$request->starttime}'."
//             );
//         }

//         return [
//             'success' => true,
//             'message' => 'Job card updated and closed successfully.'
//         ];

//     } catch (\Throwable $e) {
//         Log::error('HM update failed', [
//             'error'        => $e->getMessage(),
//             'EMPLOYEECODE' => $request->employeecode,
//             'JOBCODE'      => $request->jobcode,
//         ]);

//         return [
//             'success' => false,
//             'message' => $e->getMessage()
//         ];
//     }
// }


    // public function updateHM($request): array
    // {
    //     try {
    //         $affectedRows = 0;

    //         DB::transaction(function () use ($request, &$affectedRows) {
    //             $affectedRows = TnaEntry::where('COMPANYCODE', $request->companycode)
    //                 ->where('EMPLOYEECODE', $request->employeecode)
    //                 ->where('JOBCODE', $request->jobcode)
    //                 ->where('STARTDATE', $request->startdate)
    //                 ->where('STARTTIME', $request->starttime)
    //                 ->update([
    //                     'ENDDATE' => $request->enddate,
    //                     'ENDTIME' => $request->endtime,
    //                     'ED'      => $request->enddate,
    //                     'Action'  => Status::CLOSED
    //                 ]);
    //         });

    //         Log::error('affect rows', [
    //             'error'        => $affectedRows,
    //             'EMPLOYEECODE' => $request->employeecode,
    //             'JOBCODE'      => $request->jobcode,
    //         ]);


 

    //         // Now check outside the transaction
    //         if (!$affectedRows) {
    //         throw new \Exception(
    //             "No matching job card found for employee '{$request->employeecode}' " .
    //             "with job code '{$request->jobcode}' on '{$request->startdate} {$request->starttime}'."
    //         );
    //     }

    //         return [
    //             'success' => true,
    //             'message' => 'Job card update and closed successfully.'
    //         ];
    //     } catch (\Throwable $e) {

    //         Log::error('HM update failed', [
    //             'error'        => $e->getMessage(),
    //             'EMPLOYEECODE' => $request->employeecode,
    //             'JOBCODE'      => $request->jobcode,
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Failed to update job card. Please try again later.'
    //         ];
    //     }
    // }



    // public function updateHM($request): array
    // {
    //     try {
    //         DB::transaction(function () use ($request) {

    //             $affectedRows = TnaEntry::where('COMPANYCODE', $request->companycode)
    //                 ->where('EMPLOYEECODE', $request->employeecode)
    //                 ->where('JOBCODE', $request->jobcode)
    //                 ->where('STARTDATE', $request->startdate)
    //                 ->where('STARTTIME', $request->starttime)
    //                 ->update([
    //                     'ENDDATE' => $request->enddate,
    //                     'ENDTIME' => $request->endtime,
    //                     'ED'  => $request->enddate,
    //                     'Action'    => Status::CLOSED
    //                 ]);


    //             if ($affectedRows === 0) {
    //                 return [
    //                     'success' => true,
    //                     'message' => "No matching job card found for employee '{$request->employeecode}' with job code '{$request->jobcode}' on '{$request->startdate} {$request->starttime}'."
    //                 ];
    //             }
    //         });

    //         return [
    //             'success' => true,
    //             'message' => 'Record updated successfully.'
    //         ];
    //     } catch (\Throwable $e) {

    //         Log::error('HM update failed', [
    //             'error'        => $e->getMessage(),
    //             'EMPLOYEECODE' => $request->employeecode,
    //             'JOBCODE'      => $request->jobcode,
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Failed to update record. Please try again later.'
    //         ];
    //     }
    // }



    public function createFullHM($request)
    {
        try {
            // Check if job card is already open
            $isJobOpen = $this->tnaService->checkJobCardPunchingStatus(
                $request->employeecode,
                $request->jobcode
            );

            if ($isJobOpen) {
                return $this->successResponse('', 'This job is already open. Please close the existing job before proceeding.');
            }


            $jobCardOpen = $this->tnaService->getOpenJobCode($request->employeecode);  // employee wise

            if ($jobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$jobCardOpen' is already open ", 422);
            }

            $duplicate = $this->checkDuplicate($request);

            if (!$duplicate['success']) {
                return $this->errorResponse($duplicate['message'], 422);
            }

            $data = null;

            DB::transaction(function () use ($request, &$data) {
                $data = TnaEntry::create([
                    'COMPANYCODE'        => $request->companycode,
                    'EMPLOYEECODE'       => $request->employeecode,
                    'JOBCODE'            => $request->jobcode,
                    'STARTDATE'          => $request->startdate,
                    'STARTTIME'          => $request->starttime,
                    'ED'                 => $request->startdate,
                    'SD'                 => $request->enddate,
                    'ENDDATE'            => $request->enddate,
                    'ENDTIME'            => $request->endtime,
                    'JOBSEQNO'           => 1,
                    'EXPORTFLAG'         => 'Y',
                    'OPST'               => 0,
                    'OR_UPD_FLG'         => 'U',
                    'ENTRY_MODE'         => 'Auto',
                    'IS_MANUAL'          => 'N',
                    'TAS_DATA_FROM'      => $request->tas_data_from,
                    'PROJECTEDENDDATE'   => '2025-10-12',
                    'PROJECTEDENDTIME'   => '18:00',
                    'Action'             => Status::FULL,
                ]);
            });

            // ✅ Return the created record in the response
            return $this->successResponse($data, 'Full Task created successfully.');
        } catch (Exception $e) {

            Log::error('HM creation failed', [
                'error' => $e->getMessage(),
                'request' => (array) $request
            ]);
            return $this->errorResponse(
                'Failed to create record. Please try again later.'
            );
        }
    }


    /**
     * Check for a duplicate / overlapping job card entry before creating a new record.
     *
     * - Rejects an exact duplicate: same company/employee/job on the same start date.
     * - When an end date/time is supplied, also rejects any time-range overlap with
     *   an existing (non-deleted) entry for the same employee on the same date.
     *
     * @param  $request
     * @return array{success: bool, message?: string}
     */
    private function checkDuplicate($request): array
    {
        $companycode  = $request->companycode ?? null;
        $employeecode = $request->employeecode ?? null;
        $jobcode      = $request->jobcode ?? null;
        $startdate    = $request->startdate ?? null;
        $starttime    = $request->starttime ?? null;
        $enddate      = $request->enddate ?? null;
        $endtime      = $request->endtime ?? null;

        if (empty($companycode) || empty($employeecode) || empty($jobcode) || empty($startdate) || empty($starttime)) {
            return [
                'success' => false,
                'message' => 'Missing required field(s) for duplicate check.',
            ];
        }

        // Exact duplicate: same company/employee/job with the same start date+time
        $exactDuplicate = TnaEntry::where('COMPANYCODE', $companycode)
            ->where('EMPLOYEECODE', $employeecode)
            ->where('JOBCODE', $jobcode)
            ->whereDate('STARTDATE', $startdate)
            ->where('STARTTIME', $starttime)
            ->exists();

        if ($exactDuplicate) {
            return [
                'success' => false,
                'message' => 'Duplicate record found for this employee/job at the same start date/time.',
            ];
        }

        // Time-overlap check only applies when an end date/time is supplied (full entry)
        if (!empty($enddate) && !empty($endtime)) {
            $startDateTime = strtotime($this->extractDatePart($startdate) . " {$starttime}");
            $endDateTime   = strtotime($this->extractDatePart($enddate) . " {$endtime}");

            if ($endDateTime <= $startDateTime) {
                return [
                    'success' => false,
                    'message' => 'End date/time must be greater than start date/time.',
                ];
            }

            $sameDayEntries = TnaEntry::where('COMPANYCODE', $companycode)
                ->where('EMPLOYEECODE', $employeecode)
                ->whereDate('STARTDATE', $startdate)
                ->whereNotNull('ENDDATE')
                ->whereNotNull('ENDTIME')
                ->get(['STARTDATE', 'STARTTIME', 'ENDDATE', 'ENDTIME']);

            foreach ($sameDayEntries as $entry) {
                $existingStart = strtotime($this->extractDatePart($entry->STARTDATE) . " {$entry->STARTTIME}");
                $existingEnd   = strtotime($this->extractDatePart($entry->ENDDATE) . " {$entry->ENDTIME}");

                if ($existingStart === false || $existingEnd === false) {
                    continue;
                }

                // Overlap when existing.start < new.end AND existing.end > new.start
                if ($existingStart < $endDateTime && $existingEnd > $startDateTime) {
                    return [
                        'success' => false,
                        'message' => 'Time slot overlap detected with existing record.',
                    ];
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Extract just the date portion (YYYY-MM-DD) from a date value that may have
     * extra content appended (e.g. a stray time or AM/PM suffix), so it can be
     * safely combined with a separate time field for comparison.
     */
    private function extractDatePart(?string $date): string
    {
        if (empty($date)) {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', trim($date), $matches)) {
            return $matches[0];
        }

        return trim($date);
    }
}
