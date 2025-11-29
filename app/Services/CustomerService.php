<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\CustomerSiteModel;
use App\Models\ItemModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class CustomerService
{


    public function searchCustomerService($request)
    {
        $query = CustomerModel::query();

        if (!empty($request->CustomerName)) {
            $query->where('CustomerName', 'LIKE', '%' . $request->CustomerName . '%');
        }

        if (!empty($request->CustomerNumber)) {
            $query->orWhere('CustomerNumber', 'LIKE', '%' . $request->CustomerNumber . '%');
        }

        if (!empty($request->AccountID)) {
            $query->orWhere('AccountID', 'LIKE', '%' . $request->AccountID . '%');
        }

        if (!empty($request->TRN)) {
            $query->orWhere('TRN', 'LIKE', '%' . $request->TRN . '%');
        }

        return $query->get();
    }


// public function createCustomer($data)
// {
//     DB::beginTransaction();

//     try {
//         // Ensure customer is unique by CustomerNumber
//         $customer = CustomerModel::firstOrCreate(
//             ['CustomerNumber' => $data->CustomerNumber ?? null], // unique key
//             [
//                 'OrganizationID' => '9608',
//                 'CustomerName'   => $data->CustomerName ?? null,
//                 'AccountID'      => mt_rand(100000, 999999),
//                 'TRN'            => $data->TRN ?? null,
//                 'PaymentTerms'   => $data->PaymentTerms ?? null,
//             ]
//         );

//         // Insert customer sites
//         $sites = $data->Sites ?? [];
//         if (is_array($sites)) {
//             foreach ($sites as $site) {
//                 $siteObj = is_array($site) ? (object) $site : $site;

//                 CustomerSiteModel::create([
//                     'Customer_ID'    => $customer->ID,
//                     'CustomerSite'   => $siteObj->CustomerSite ?? $siteObj->customerSite ?? null,
//                     'SiteAddress'    => $siteObj->SiteAddress ?? $siteObj->siteAddress ?? null,
//                     'Contact_Person' => $siteObj->Contact_Person ?? $siteObj->contactPerson ?? null,
//                     'Email'          => $siteObj->Email ?? $siteObj->email ?? null,
//                     'Mobile_Number'  => $siteObj->Mobile_Number ?? $siteObj->mobile ?? null,
//                     'Position'       => $siteObj->Position ?? $siteObj->position ?? null,
//                     'BillTo'         => $siteObj->BillTo ?? $siteObj->billTo ?? 0,
//                     'ShipTo'         => $siteObj->ShipTo ?? $siteObj->shipTo ?? 0,
//                 ]);
//             }
//         }

//         DB::commit();
//         return response()->json([
//             'success' => true,
//             'message' => 'Customer created successfully',
//             'customer' => $customer,
//         ]);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to create customer',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }



    public function createCustomer($data)
    {
        DB::beginTransaction();

        try {
            
        
            if($data->CustomerId == null){
        $existingCustomer = CustomerModel::where('CustomerNumber', $data->CustomerNumber)->first();
        if ($existingCustomer) {
            // Throw exception if duplicate
            throw new \Exception("Customer with number {$data->CustomerNumber} already exists.");
        }
    }   


            $customer = CustomerModel::updateOrCreate(
                ['ID' => $data->CustomerId ?? 0], // Condition to check if customer exists
                [
                    'OrganizationID' => '9608',
                    'CustomerName'   => $data->CustomerName ?? null,
                    'CustomerNumber' => $data->CustomerNumber ?? null,
                    'AccountID'      => mt_rand(100000, 999999),
                    'TRN'            => $data->TRN ?? null,
                    'PaymentTerms'   => $data->PaymentTerms ?? null, 
                ]
            );


            $sites = $data->Sites ?? [];

            if (is_array($sites)) {
                foreach ($sites as $site) {

                    // Convert array to object
                    $siteObj = is_array($site) ? (object) $site : $site;

                    // Check if ID exists
                    $lookupId = isset($siteObj->id) && $siteObj->id > 0 ? $siteObj->id : null;

                    // Prepare data
                    $data = [
                        'Customer_ID'   => $customer->ID,
                        'CustomerSite'  => $siteObj->CustomerSite  ?? $siteObj->customerSite ?? null,
                        'SiteAddress'   => $siteObj->SiteAddress   ?? $siteObj->siteAddress ?? null,
                        'Contact_Person' => $siteObj->Contact_Person ?? $siteObj->contactPerson ?? null,
                        'Email'         => $siteObj->Email         ?? $siteObj->email ?? null,
                        'Mobile_Number' => $siteObj->Mobile_Number ?? $siteObj->mobile ?? null,
                        'Position'      => $siteObj->Position      ?? $siteObj->position ?? null,
                        'BillTo'        => $siteObj->BillTo        ?? $siteObj->billTo ?? 0,
                        'ShipTo'        => $siteObj->ShipTo        ?? $siteObj->shipTo ?? 0,
                    ];

                    if ($lookupId) {
                        // ✅ Update existing record
                        CustomerSiteModel::where('ID', $lookupId)->update($data);
                    } else {
                        // ✅ Insert new record
                        CustomerSiteModel::create($data);
                    }
                }
            }  

            DB::commit();
            return $customer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
