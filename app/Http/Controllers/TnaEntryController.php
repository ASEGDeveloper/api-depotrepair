<?php
 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TnaRequest;

use App\Services\TnaService;
use Illuminate\Http\Request;

class TnaEntryController extends Controller
{
  public $tnaService;
   
   public function __construct(TnaService  $tnaService) {
        $this->tnaService = $tnaService;
    }

    //  Create a new record
    public function createUpdate(TnaRequest $request)
    {   

      $status = $this->tnaService->toCheckUserStatus($request->emp_code); 

  

        if($status){

          return  $this->tnaService->updateTheTnaRecords($request);

            return "success";

        }else{
            return "false";
        }
 

        return response()->json(['message' => 'Record created successfully', 'data' => $record], 201);
    }

    
    
}
