<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\CustomerSiteModel;
use App\Models\ItemModel;
use Illuminate\Support\Facades\DB;

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


    public function createCustomer($data)
    {
        DB::beginTransaction();

        try {

            $customer = CustomerModel::updateOrCreate(
                ['ID' => $data->CustomerId ?? 0], // Condition to check if customer exists
                [
                    'OrganizationID' => '9608',
                    'CustomerName'   => $data->CustomerName ?? null,
                    'CustomerNumber' => $data->CustomerNumber ?? null,
                    'AccountID'      => mt_rand(100000, 999999), 
                    'TRN'            => $data->TRN ?? null,
                    'PaymentTerms'   => $data->PaymentTerms ?? null,
                    // 'PaymentTermID'  => $data->PaymentTermID ?? null,
                    //'LocationNumber' => $data->LocationNumber ?? null,
                ]
            );


            $sites = $data->Sites ?? [];

            if (is_array($sites)) {
                foreach ($sites as $site) {
                    // If $site is an array, cast to object
                    $siteObj = is_array($site) ? (object) $site : $site;

                    CustomerSiteModel::updateOrCreate(
                        ['ID' => $siteObj->id ?? 0],
                        [
                            'Customer_ID' => $customer->ID,
                            'CustomerSite' => $siteObj->CustomerSite ?? null,
                            'SiteAddress' => $siteObj->SiteAddress ?? null,
                            'BillTo' => $siteObj->BillTo ?? 0,
                            'ShipTo' => $siteObj->ShipTo ?? 0,
                        ]
                    );
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
