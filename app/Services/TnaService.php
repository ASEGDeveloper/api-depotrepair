<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TnaEntry;
use App\Constants\Status;
use App\Traits\ApiResponse;

class TnaService
{

    use ApiResponse;

    public function toCheckUserStatusTaskNo($empId, $taskNo)
    {
        return DB::table('deporepair.employee')
            ->where('EmployeeID', $empId)
            ->where('EmployeeStatus', 'Active')
            ->first();
    }

    public function toCheckJobCard($taskNo)
    {
        return DB::table('deporepair.quotation_repair_order_jobs')
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
                ->where('TAS_DATA_FROM', $request->tas_data_from)
                ->whereNull('ED')
                ->update(['ED' => $currentDateAndTime, 'ENDDATE' => $currentDateAndTime, 'ENDTIME' => $currentTime]);
             
            $data = ['EMPLOYEECODE' => $request->employeecode, 'JOBCODE' => $request->jobcode, 'ED' => $currentDateAndTime, 'ENDDATE' => $currentDateAndTime, 'ENDTIME' => $currentTime];
            return $this->successResponse($data, 'Task updated successfully.');
        } else {
            $jobCardOpen = $this->getOpenJobCode($request->employeecode);

            if ($jobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$jobCardOpen' is already open");
            }

            $anyJobCardOpen= $this->isJobCodeAlreadyOpen($request->jobcode);

            if ($anyJobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$anyJobCardOpen' is already open ");
            }


            $data = TnaEntry::create(array_merge($default, [
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE' => $request->jobcode,

            ]));
            return $this->successResponse($data, 'Task created successfully.');
            // return 'Record created';
        }
    }


    public function updateHightMessagingTask($request)
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



        // $default = $this->getDefaultTnaData(
        //     $request->employeecode,
        //     $request->jobcode,
        //     $request->startdate,
        //     $request->starttime,
        //     $request->tas_data_from
        // );

        // $exists = $this->checkJobCardPunchingStatus($request->employeecode, $request->jobcode);

        // if ($exists) {

        //     TnaEntry::where('EMPLOYEECODE', $request->employeecode)
        //         ->where('JOBCODE', $request->jobcode)
        //         ->whereNull('ED')
        //         ->update(['ED' => $request->startdate, 'ENDDATE' => $request->enddate, 'ENDTIME' => $request->endtime]);
        //     return 'High Messaging task updated';
        // } else {
        //     TnaEntry::create(array_merge($default, [
        //         'EMPLOYEECODE' => $request->employeecode,
        //         'JOBCODE' => $request->jobcode,
        //     ]));
        //     return 'High Messaging task created';
        // }
    }


    public function updateSMSTask($request)
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
                ->where('TAS_DATA_FROM', $request->tas_data_from)
                ->whereNull('ED')
                ->update(['ED' => $currentDateAndTime, 'ENDDATE' => $currentDateAndTime, 'ENDTIME' => $currentTime, 'Action' => Status::CLOSED]);

            $data = [
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE'      => $request->jobcode,
                'ED'           => $currentDateAndTime,
                'ENDDATE'      => $currentDateAndTime,
                'ENDTIME'      => $currentTime,
            ];
            return $this->successResponse($data, 'Task updated successfully.');
        } else {
            $jobCardOpen = $this->getOpenJobCode($request->employeecode);

            if ($jobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$jobCardOpen' is already open ");
            }

           $anyJobCardOpen= $this->isJobCodeAlreadyOpen($request->jobcode);

            if ($anyJobCardOpen) {
                // Return a message that job card is already open
                return $this->errorResponse("Job card '$anyJobCardOpen' is already open ");
            }


            $create = TnaEntry::create(array_merge($default, [
                'EMPLOYEECODE' => $request->employeecode,
                'JOBCODE' => $request->jobcode,
                'Action'    => Status::START
            ]));
            return $this->successResponse($create, 'Task created successfully.');
        }
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
            'COMPANYCODE' =>'01',
            'EMPLOYEECODE'      => $employeeCode,
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
    public function checkJobCardPunchingStatus($EMPLOYEECODE, $JOBCODE)
    {
        return TnaEntry::where('EMPLOYEECODE', $EMPLOYEECODE)
            ->where('JOBCODE', $JOBCODE)
            //  ->whereNull('ED')
            ->whereNull('ENDTIME')
            ->exists();
    }



    public function isTaskOpen(string $employeeCode, string $jobCode): bool
    {
        return TnaEntry::where('employeecode', $employeeCode)
            ->where('jobcode', $jobCode)
            ->where(function ($query) {
                $query->whereNull('enddate')
                    ->orWhereNull('endtime');
            })
            ->exists();
    }


    // Returns true if a record with ED = null exists
    public function getOpenJobCode($EMPLOYEECODE)
    {
        return TnaEntry::where('EMPLOYEECODE', $EMPLOYEECODE)
            ->whereNull('ED')
            ->value('JOBCODE'); // returns the first matching JOBCODE or null
    }


public function isJobCodeAlreadyOpen($JOBCODE)
{
    return TnaEntry::where('JOBCODE', $JOBCODE)
        ->whereNull('ED')      // still open
        ->value('JOBCODE');            // true if at least one open record exists
}

}
