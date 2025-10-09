<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TnaEntry;

class TnaService{

 
public function toCheckUserStatus($empId)
{
    return DB::table('employee')
        ->select('*') // choose columns you want
        ->where('EmployeeID', $empId)
        ->where('EmployeeStatus', 'Active')
        ->first();  
}
 

public function updateTheTnaRecords($request)
{
    $currentDateAndTime = Carbon::now();

    $currentTime = $currentDateAndTime->format('H:i'); 

   //return $request;
    $default = [ 
        'COMPANYCODE' => '01',
        'EMPLOYEECODE' => $request->emp_code,
        'JOBCODE', $request->job_code,
        'STARTDATE' => $currentDateAndTime,    
        'STARTTIME' => $currentTime,   
        'ENDDATE' => null,     
        'ENDTIME' => null , 
        'TAS_DATA_FROM'=>$request->tas_data_from,
        'JOBSEQNO' => 1,
        'EXPORTFLAG' => 'Y',
        'OPST' => 0,
        'PROJECTEDENDDATE' => '2025-10-12',
        'PROJECTEDENDTIME' => '18:00',
        'OR_UPD_FLG' => 'U',
        'ENTRY_MODE' => 'Auto',
        'IS_MANUAL' => 'N',
        'SD' => $currentDateAndTime,
        'ED' => null       

    ]; 

    

    // Check if there is an existing record with ED = null
    $exists = $this->checkJobCardPunchingStatus($request->emp_code, $request->job_code);

    if ($exists) {
        // Only update ED of the existing record
          TnaEntry::where('EMPLOYEECODE', $request->emp_code)
            ->where('JOBCODE', $request->job_code)
            ->whereNull('ED')
            ->update(['ED' => $currentDateAndTime,'ENDDATE'=>$currentDateAndTime,'ENDTIME'=>$currentTime]);
    } else {
        // Create a new record if none exists
         TnaEntry::create(array_merge($default, [
            'EMPLOYEECODE' => $request->emp_code,
            'JOBCODE' => $request->job_code,
        ]));
    }
}

// Returns true if a record with ED = null exists
private function checkJobCardPunchingStatus($emp_code, $job_code)
{
    return DB::table('tna_entry_duplicate')
        ->where('EMPLOYEECODE', $emp_code)
        //->where('JOBCODE', $job_code)
        ->whereNull('ED')
        ->exists();
}


}


?>