<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TnaService;
use App\Services\HMService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\JsonResponse;

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
            $source = $input->tas_data_from ?? null;

            $allowedSources = ['Highmessage', 'SMS', 'TAS'];

            // Validate mandatory and allowed values
            if (empty($source) || !in_array($source, $allowedSources)) {
                return $this->errorResponse(
                    'tas_data_from is required and must be one of: Highmessage, SMS, TAS.'
                );
            } 

            return match ($source) {
                'Highmessage' => $this->handleHighMessage($request),
                'SMS' => $this->handleSms($request),
                'TAS'         => $this->handleTaskServer($request),
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

        if ($response = $this->validateHighMessage($request)) {
            return $response;
        }

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


    private function validateHighMessage(Request $request): ?JsonResponse
    {
        // ✅ Decode JSON as ARRAY
        $input = json_decode($request->getContent(), true);

        // ✅ Handle invalid / empty JSON
        if (!is_array($input)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid JSON payload',
            ], 400);
        }

        $validator = Validator::make($input, [
            'companycode'   => 'required',
            'employeecode'  => 'required',
            'jobcode'       => 'required',
            'tas_data_from' => 'required',
            'startdate' => 'required',
            'starttime'=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        return null; // ✅ validation passed
    }



    private function handleSms(Request $request)
    {

        $input = json_decode($request->getContent());

        if (!$input) {
            return $this->errorResponse('Invalid JSON payload', 422);
        }


        if ($response = $this->validateSMSMessage($request)) {
            return $response;
        }


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


        if (
            !$this->tnaService->toCheckJobCard(
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'Invalid job card. Please verify the details and try again.'
            );
        }

        return $this->tnaService->updateSMSTask($input);
    }
 

    private function validateSMSMessage(Request $request): ?JsonResponse
    {
        // ✅ Decode JSON as ARRAY
        $input = json_decode($request->getContent(), true);

        // ✅ Handle invalid / empty JSON
        if (!is_array($input)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid JSON payload',
            ], 400);
        }

        $validator = Validator::make($input, [
            'employeecode' => 'required',
            'jobcode' => 'required',
            'tas_data_from' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        return null; // ✅ validation passed
    }
 

    private function handleTaskServer(Request $request)
    {
        $input = json_decode($request->getContent());

        if (!$input) {
            return $this->errorResponse('Invalid JSON payload', 422);
        }

        if ($response = $this->validateSMSMessage($request)) {
            return $response;
        }

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


        if (
            !$this->tnaService->toCheckJobCard(
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'Invalid job card. Please verify the details and try again.'
            );
        } 

        return $this->tnaService->updateTnaTask($input);
    }

    
}
