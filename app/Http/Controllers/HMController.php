<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TnaService;
use App\Services\HMService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HMController extends Controller
{

    use ApiResponse;
    public $tnaService;
    public $hmService;


    public function __construct(TnaService $tnaService, HMService $hmService)
    {
        $this->tnaService = $tnaService;
        $this->hmService = $hmService;
    }
 

    public function store(Request $request)
    {
        try {
            $input = json_decode($request->getContent());
            $source = $input->tas_data_from;

            return match ($source) {
                'Highmessage' => $this->handleHighMessage($request),
                'SMS' => $this->handleSms($request),
                default => $this->handleTaskServer($request),
            };

        } catch (\Throwable $e) {
            Log::error('TNA Store Error', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return $this->errorResponse(
                'An unexpected error occurred. Please try again later.'
            );
        }
    }



    private function handleHighMessage(Request $request)
    {
        $input = json_decode($request->getContent());

        if (!$input) {
            return $this->errorResponse('Invalid JSON payload', 422);
        }

        $this->validateHighMessage($input);

        $hasStart = !empty($input->startdate) && !empty($input->starttime);
        $hasEnd = !empty($input->enddate) && !empty($input->endtime);

        // Employee validation
        if (
            !$this->tnaService->toCheckUserStatusTaskNo(
                $input->employeecode,
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'Employee does not exist or is inactive'
            );
        }

        $isTaskOpen = $this->tnaService->isTaskOpen(
            $input->employeecode,
            $input->jobcode
        );

        return $this->resolveTaskAction(
            $input,
            $hasStart,
            $hasEnd,
            $isTaskOpen
        );
    }


    private function resolveTaskAction($input, bool $hasStart, bool $hasEnd, bool $isTaskOpen)
    {
        // CASE 1: End task
        if ($hasEnd && $isTaskOpen) {
            return $this->hmService->updateHM($input);
        }

        // CASE 2: Full entry (start + end)
        if ($hasStart && $hasEnd && !$isTaskOpen) {

            if (
                !$this->tnaService->toCheckJobCard(
                    $input->jobcode
                )
            ) {
                return $this->errorResponse(
                    'Invalid job card. Please verify the details and try again.'
                );
            }

            return $this->hmService->createFullHM($input);
        }

        // CASE 3: Start task
        if ($hasStart && !$hasEnd && !$isTaskOpen) {

            if (!$this->tnaService->toCheckJobCard($input->jobcode)) {
                return $this->errorResponse('Invalid job card. Please verify the details and try again.');
            } 

            return $this->hmService->createHM($input);
        }

        return $this->errorResponse(
            'This job is already open. Please close the existing job before proceeding',
            422
        );
    }


    private function validateHighMessage($input): void
    {
        $validator = Validator::make((array) $input, [
            'employeecode' => 'required',
            'jobcode' => 'required',
            'tas_data_from' => 'required',
        ]);

        if ($validator->fails()) {
            abort(
                response()->json([
                    'message' => $validator->errors()->first(),
                ], 422)
            );
        }
    }


    private function handleSms(Request $request)
    {
        
    $input = json_decode($request->getContent());

        if (!$input) {
            return $this->errorResponse('Invalid JSON payload', 422);
        }

       return $this->tnaService->updateSMSTask( $input);

    }

    private function handleTaskServer(Request $request)
    {
        $input = json_decode($request->getContent());

        if (!$input) {
            return $this->errorResponse('Invalid JSON payload', 422);
        }

       return $this->tnaService->updateTnaTask( $input);
    }


 

}
