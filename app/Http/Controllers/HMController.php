<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TnaService;
use App\Services\HMService;
use App\Traits\ApiResponse;
use App\Http\Requests\TnaRequest;
use Illuminate\Support\Facades\Validator;

class HMController extends Controller
{
 
  use ApiResponse;
  public $tnaService;
  public $hmService;


  public function __construct(TnaService  $tnaService,HMService $hmService)
  {
    $this->tnaService = $tnaService;
    $this->hmService = $hmService;
  }


public function store(Request $request)
{

 
    try {
        // 0. Request validation
 

$input = json_decode($request->getContent());  
 

if (!$input) {
    return $this->errorResponse('Invalid JSON payload', 422);
}


$validator = Validator::make((array) $input, [ // convert object to array for validator
    'employeecode'   => 'required',
    'jobcode'        => 'required',    
    'tas_data_from'  => 'required',
]);

if ($validator->fails()) {
    return $this->errorResponse(
        $validator->errors()->first(),
        422
    );
}


 
        
        // 1. Check employee status
        if (
            !$this->tnaService->toCheckUserStatusTaskNo(
                $input->employeecode,
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'The specified employee does not exist or is currently inactive in the Depot Repair system'
            );
        }
       
        

        // 2. Update HM (if enddate exists)
        if (!empty($input->enddate)) {
            return $this->hmService->updateHM($input);
        } 

     
          if (
            !$this->tnaService->toCheckJobCard( 
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'The specified employee does not exist or is currently inactive in the Depot Repair system'
            );
        }
    
        // 4. Create HM
        return $this->hmService->createHM($input);

    } catch (\Throwable $e) {

        FacadesLog::error('TNA Create/Update Error', [
            'message'      => $e->getMessage(),
            'file'         => $e->getFile(),
            'line'         => $e->getLine(),
            'EMPLOYEECODE' => $input->employeecode,
            'JOBCODE'      => $input->jobcode,
            'SOURCE'       => $input->source ?? null,
        ]);

        return $this->errorResponse(
            'An unexpected error occurred. Please try again later.'
        );
    }
}


 public function addFullRecords(Request $request)
{
    
    try {
        // 0. Request validation
        //$input = json_decode($request->getContent(), true);
        $input = json_decode($request->getContent());

    $validator = Validator::make((array) $input, [ // convert object to array for validator
    'employeecode'   => 'required',
    'jobcode'        => 'required', 
    'startdate'      => 'required',
    'starttime'      => 'required',
    'enddate'        => 'required', 
    'endtime'        => 'required', 
    'tas_data_from'  => 'required',
]);

if ($validator->fails()) {
    return $this->errorResponse(
        $validator->errors()->first(),
        422
    );
}

        // 1. Check employee status
        if (
            !$this->tnaService->toCheckUserStatusTaskNo(
                $input->employeecode,
                $input->jobcode
            )
        ) {
            return $this->errorResponse(
                'The specified employee does not exist or is currently inactive in the Depot Repair system'
            );
        }
 
         

         return $this->hmService->fullCreateHM($input);

        

    } catch (\Throwable $e) {

        FacadesLog::error('TNA Create/Update Error', [
            'message'      => $e->getMessage(),
            'file'         => $e->getFile(),
            'line'         => $e->getLine(),
            'EMPLOYEECODE' => $request->employeecode,
            'JOBCODE'      => $request->jobcode,
            'SOURCE'       => $request->source ?? null,
        ]);

        return $this->errorResponse(
            'An unexpected error occurred. Please try again later.'
        );
    }
}



// public function store(Request $request)
// {
//     try {
//         // 1. Check employee status
//         $isEmployeeActive = $this->tnaService
//             ->toCheckUserStatusTaskNo($request->employeecode, $request->jobcode);

//         if (!$isEmployeeActive) {
//             return $this->errorResponse(
//                 'The specified employee does not exist or is currently inactive in the Depot Repair system'
//             );
//         }
 

//         // 2. If enddate exists → UPDATE HM
//         if (!empty($request->enddate)) {
//             return $this->hmService->updateHM($request);
//         }

//         //  if (!empty($request->startdate) && !empty($request->enddate)) {
//         //         return $this->hmService->fullCreateHM($request);
//         //     }
            

//         // 3. Validate job card before CREATE
//         $isJobCardValid = $this->tnaService->toCheckJobCard($request->jobcode);

//         if (!$isJobCardValid) {
//             return $this->errorResponse(
//                 'Unable to create the task due to an issue with the job card. Please verify the job details and try again'
//             );
//         }

//         // 4. CREATE HM
//         return $this->hmService->createHM($request);

//     } catch (\Throwable $e) {

//         FacadesLog::error('TNA Create/Update Error', [
//             'message'      => $e->getMessage(),
//             'file'         => $e->getFile(),
//             'line'         => $e->getLine(),
//             'EMPLOYEECODE' => $request->employeecode,
//             'JOBCODE'      => $request->jobcode,
//             'SOURCE'       => $request->source ?? null,
//         ]);

//         return $this->errorResponse(
//             'An unexpected error occurred. Please try again later.'
//         );
//     }
// }


}
