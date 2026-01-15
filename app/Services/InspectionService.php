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
         $surSignature =  $this->getSurSignature($inspectionID);
         $inspection->surveyor = $surSignature;
         $inspection->logo =  $this->getLogo();
       

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($inspection);

    }

    public function getLogo(){
      return  DB::table('deporepair.cryotech_logo')->select('logo')->first();
    }

    public function getInspectionImages($inspectionID) 
    {
        
        return DB::table('deporepair.inspection_images')->where('inspection_id', $inspectionID)
            ->where('is_deleted', 0)->select('image_data','description')->orderBy('id','asc')->get();
        
    }


     public function getSignature($inspectionID) 
    {
        
        return DB::table('deporepair.inspection_signatures')->where('inspection_id', $inspectionID)
        ->select('custSignatureName','Type','signature_data','date')->where('Type','Customer')->orderBy('ID', 'desc')->first();
        
    }

    public function getCustomerID($inspectionID) 
    {

      return  DB::table('deporepair.inspection_report_dpr as ird')
                ->join('deporepair.installbase_items_dpr as iid', 'iid.Serial_Numbers', '=', 'ird.SerialNumber')
                ->join('deporepair.installbase_dpr as insid', 'insid.ID', '=', 'iid.installbase_id')
                ->where('ird.id', '=', $inspectionID)
                ->select('insid.CustomerID')
                ->first();

    
        
    }


    public function getCustomerEmail($customerID)
    {

        
    return DB::table('deporepair.sites_dpr')
        ->select('Contact_Person', 'Email', 'Position','BillTo','ShipTo')
        ->where('Customer_ID', $customerID)
        ->get();
    }


     public function getSurSignature($inspectionID) 
    {
        
        return DB::table('deporepair.inspection_signatures')->where('inspection_id', $inspectionID)
        ->select('Surveyor_Name','Type','signature_data','date')->where('Type','Surveyor')->first();
        
    }


    
}
