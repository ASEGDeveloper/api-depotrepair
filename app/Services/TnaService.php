<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TnaEntry;

class TnaService
{


    public function toCheckUserStatusTaskNo($empId, $taskNo)
    {
        return DB::table('employee')
            ->where('EmployeeID', $empId)
            ->where('EmployeeStatus', 'Active')
            ->first(); 
    }

    public function toCheckJobCard($taskNo)
    {
        return DB::table('quotation_repair_order_jobs')
            ->where('Task_No', $taskNo)
            ->first();
    }


    public function updateTnaTask($request)
    {
        $currentDateAndTime = Carbon::now();

        $currentTime = $currentDateAndTime->format('H') . '.' . $currentDateAndTime->format('i');

        $default = $this->getDefaultTnaData(
            $request->employeecode,
            $request->jobcode,
            $currentDateAndTime,
            $currentTime,
            $request->tas_data_from,
            '2025-10-12',
            '18:00'
        );


        $exists = $this->checkJobCardPunchingStatus($request->employeecode, $request->jobcode);

        if ($exists) {

            TnaEntry::where('EMPLOYEECODE', $request->employeecode)
                ->where('JOBCODE', $request->jobcode)
                ->whereNull('ED')
                ->update(['ED' => $currentDateAndTime, 'ENDDATE' => $currentDateAndTime, 'ENDTIME' => $currentTime]);
            return 'Record updated';
        } else {
            TnaEntry::create(array_merge($default, [
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE' => $request->jobcode,
            ]));
            return 'Record created';
        }
    }


    public function updateHightMessagingTask($request)
    {

        $default = $this->getDefaultTnaData(
            $request->employeecode,
            $request->jobcode,
            $request->startdate,
            $request->starttime,
            $request->tas_data_from
        );

        $exists = $this->checkJobCardPunchingStatus($request->employeecode, $request->jobcode);

        if ($exists) {

            TnaEntry::where('EMPLOYEECODE', $request->employeecode)
                ->where('JOBCODE', $request->jobcode)
                ->whereNull('ED')
                ->update(['ED' => $request->startdate, 'ENDDATE' => $request->enddate, 'ENDTIME' => $request->endtime]);
            return 'High Messaging task updated';
        } else {
            TnaEntry::create(array_merge($default, [
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE' => $request->jobcode,
            ]));
            return 'High Messaging task created';
        }
    }


    public function updateSMSTask($request)
    {
        return 'SMS Task updated';
    }



    protected function getDefaultTnaData(
        $employeeCode,
        $jobCode,
        $startDate,
        $startTime,
        $tasDataFrom,
        $projectedEndDate = null,
        $projectedEndTime = null
    ) {
        return [
            'COMPANYCODE'       => '01',
            'EMPLOYEECODE'      => $employeeCode,
            'JOBCODE'           => $jobCode,
            'STARTDATE'         => $startDate,
            'STARTTIME'         => $startTime,
            'ENDDATE'           => null,
            'ENDTIME'           => null,
            'TAS_DATA_FROM'     => $tasDataFrom,
            'JOBSEQNO'          => 1,
            'EXPORTFLAG'        => 'Y',
            'OPST'              => 0,
            'PROJECTEDENDDATE'  => $projectedEndDate ?? date('Y-m-d', strtotime('+1 day')), // default tomorrow
            'PROJECTEDENDTIME'  => $projectedEndTime ?? '18:00',
            'OR_UPD_FLG'        => 'U',
            'ENTRY_MODE'        => 'Auto',
            'IS_MANUAL'         => 'N',
            'SD'                => $startDate,
            'ED'                => null
        ];
    }



    // Returns true if a record with ED = null exists
    private function checkJobCardPunchingStatus($EMPLOYEECODE, $JOBCODE)
    {
        return DB::table('tna_entry_duplicate')
            ->where('EMPLOYEECODE', $EMPLOYEECODE)
            //->where('JOBCODE', $job_code)
            ->whereNull('ED')
            ->exists();
    }
}
