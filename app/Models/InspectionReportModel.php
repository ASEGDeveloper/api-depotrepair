<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InspectionReportModel extends Model
{
     protected $table = 'inspection_report_dpr';
     protected $primaryKey = 'id';

    public $timestamps = true;

    // Match your SQL Server column names
    const CREATED_AT = 'Creation_date';
   /// const UPDATED_AT = 'Updated_date';


    protected $fillable = [
    'Inspection_ID',
    'Unit_Number',
    'Customer_Name', 
    'Tank_Type',
    'Capacity_L',
    'Initial_Test_MMM_YY',
    'Last_Cargo',
    'Inner_Tank_Material',
    'Last_2_5yr_Test_MMM_YY',
    'Last_5yr_Test_MMM_YY',
    'Location_of_Inspection',
    'Manufacturer',
    'Max_Gross_Weight_kg',
    'Next_CSC_Due',
    'Next_Test_Due_MMM_YY',
    'Outer_Tank_Material',
    'Results',
    'Survey_Date',
    'Survey_Type',
    'Surveyor',
    'Tare_Weight_kg',
    'Un_Portable_Tank_Type',
    'Vacuum_reading',
    'Comments',
    'Status',
    'DATALOAD_TIME',
    'Created_By'
];



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $userID = Auth::check() ? Auth::user()->EmployeeID : 'system';
            $model->Created_by = $userID; 
            $model->Creation_date = now();
          
        });

         
    }
}
