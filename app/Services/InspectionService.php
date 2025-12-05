<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\CustomerSiteModel;
use App\Models\InspectionReportModel;
use App\Models\ItemModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class InspectionService
{


    public function getInspectionDetails($inspectionID){

        $inspection = InspectionReportModel::find($inspectionID);
       // $inspection = InspectionReportModel::where('Inspection_ID', $inspectionID)->first();

        $images =  $this->getInspectionImages($inspectionID);
        $inspection->images = $images;
        $signature =  $this->getSignature($inspectionID); 
        $inspection->signature = $signature;

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($inspection);

    }


    public function getInspectionImages($inspectionID) 
    {
        
        return DB::table('inspection_images')->where('inspection_id', $inspectionID)
            ->where('is_deleted', 0)->select('image_data','description')->orderBy('id','desc')->get();
        
    }


     public function getSignature($inspectionID) 
    {
        
        return DB::table('inspection_signatures')->where('inspection_id', $inspectionID)
        ->select('custSignatureName','signature_data','date')->orderBy('ID', 'desc')->first();
        
    }

    public function getCustomerID($inspectionID) 
    {
        
    return $result = DB::table('inspection_report_dpr as ird')
                ->join('installbase_items_dpr as iid', 'iid.Serial_Numbers', '=', 'ird.SerialNumber')
                ->join('installbase_dpr as insid', 'insid.ID', '=', 'iid.installbase_id')
                ->where('ird.id', $inspectionID)   // replace $inspectionID with your variable
                ->select('insid.CustomerID')
                ->first();
        
    }


    public function getCustomerEmail($customerID)
    {

        
    return DB::table('sites_dpr')
        ->select('Contact_Person', 'Email', 'Position','BillTo','ShipTo')
        ->where('Customer_ID', $customerID)
        ->get();
    }


    
}
