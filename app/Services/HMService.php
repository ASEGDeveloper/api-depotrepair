<?php

namespace App\Services;

use App\Models\TnaEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Constants\Status;
use Exception;

class HMService
{
    protected TnaService $tnaService;

    public function __construct(TnaService $tnaService)
    {
        $this->tnaService = $tnaService;
    }

    public function createHM($request): array
    { 
        try {
            // Check if job card is already open
            $isJobOpen = $this->tnaService->checkJobCardPunchingStatus(
                $request->employeecode,
                $request->jobcode
            );

            if ($isJobOpen) {
                return [
                    'success' => false,
                    'message' => 'This job is already open. Please close the existing job before proceeding.'
                ];
            }

            DB::transaction(function () use ($request) {
                TnaEntry::create([
                    'COMPANYCODE'        => '01',
                    'EMPLOYEECODE'       => $request->employeecode,
                    'JOBCODE'            => $request->jobcode,
                    'STARTDATE'          => $request->startdate,
                    'STARTTIME'          => $request->starttime,
                    'SD'                 => $request->startdate,
                    'JOBSEQNO'          => 1,
                    'EXPORTFLAG'        => 'Y',
                    'OPST'              => 0,
                    'OR_UPD_FLG'        => 'U',
                    'ENTRY_MODE'        => 'Auto',
                    'IS_MANUAL'         => 'N',
                    'TAS_DATA_FROM'      => $request->tas_data_from,
                    'PROJECTEDENDDATE'   => '2025-10-12',
                    'PROJECTEDENDTIME'   => '18:00',
                    'Action'    =>Status::START   
                ]);
            });

            return [
                'success' => true,
                'message' => 'Record created successfully.'
            ];

        } catch (Exception $e) {

            Log::error('HM creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create record. Please try again later.'
            ];
        }
    }


public function updateHM($request): array
{
    try {
        DB::transaction(function () use ($request) {

            $affectedRows = TnaEntry::where('EMPLOYEECODE', $request->employeecode)
                ->where('JOBCODE', $request->jobcode)
                ->whereNull('ENDDATE') // optional safety check
                ->update([
                    'ENDDATE' => $request->enddate,
                    'ENDTIME' => $request->endtime,
                    'ED'  => $request->enddate,
                    'Action'    =>Status::CLOSED               
                ]);

            if ($affectedRows === 0) {
                throw new \Exception('No matching record found to update.');
            }
        });

        return [
            'success' => true,
            'message' => 'Record updated successfully.'
        ];

    } catch (\Throwable $e) {

        Log::error('HM update failed', [
            'error'        => $e->getMessage(),
            'EMPLOYEECODE' => $request->employeecode,
            'JOBCODE'      => $request->jobcode,
        ]);

        return [
            'success' => false,
            'message' => 'Failed to update record. Please try again later.'
        ];
    }
}



 public function createFullHM($request)
    {
        try {
            // Check if job card is already open
            $isJobOpen = $this->tnaService->checkJobCardPunchingStatus(
                $request->employeecode,
                $request->jobcode
            );

            if ($isJobOpen) {
                return [
                    'success' => false,
                    'message' => 'This job is already open. Please close the existing job before proceeding.'
                ];
            }

            DB::transaction(function () use ($request) {
                TnaEntry::create([
                    'COMPANYCODE'        => '01',
                    'EMPLOYEECODE'       => $request->employeecode,
                    'JOBCODE'            => $request->jobcode,
                    'STARTDATE'          => $request->startdate,
                    'STARTTIME'          => $request->starttime,
                    'ED'                 => $request->startdate,
                    'SD'  => $request->enddate,  
                    'ENDDATE' => $request->enddate,
                    'ENDTIME' => $request->endtime,   
                    'JOBSEQNO'          => 1,
                    'EXPORTFLAG'        => 'Y',
                    'OPST'              => 0,
                    'OR_UPD_FLG'        => 'U',
                    'ENTRY_MODE'        => 'Auto',
                    'IS_MANUAL'         => 'N',                
                    'TAS_DATA_FROM'      => $request->tas_data_from,
                    'PROJECTEDENDDATE'   => '2025-10-12',
                    'PROJECTEDENDTIME'   => '18:00',
                    'Action'    =>Status::FULL
                ]);
            });

            return [
                'success' => true,
                'message' => 'Record created successfully.'
            ];

        } catch (Exception $e) {

            Log::error('HM creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create record. Please try again later.'
            ];
        }
    }

    
}
