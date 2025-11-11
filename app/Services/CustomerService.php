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


    public function createCustomer(array $data)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Create Customer
            // $customer = CustomerModel::create([
            //     'CustomerName' => $data['CustomerName'],
            //     'CustomerNumber' => $data['CustomerNumber'] ?? null,
            //     'AccountID' => $data['AccountID'] ?? null,
            //     'TRN' => $data['TRN'] ?? null,
            //     'LocationNumber' => $data['LocationNumber'] ?? null,
            //     'AccountNumber' => $data['AccountNumber'] ?? null,
            // ]);

            $customer = CustomerModel::create([
                'OrganizationID' => '9608',
                'CustomerName'   => $data['CustomerName'],
                'CustomerNumber' => $data['CustomerNumber'] ?? null,
                'AccountID'      => $data['AccountID'] ?? null,
                'PaymentTermID'  => $data['PaymentTermID'] ?? null,
                'TRN'            => $data['TRN'] ?? null,
                'PaymentTerms'   => $data['PaymentTerms'] ?? null,
                'LocationNumber' => $data['LocationNumber'] ?? null,
            ]);



            // 2️⃣ Create Sites
            // if (!empty($data['Sites'])) {
            //     foreach ($data['Sites'] as $siteData) {
            //         CustomerSiteModel::create(array_merge($siteData, [
            //             'CustomerID' => $customer->ID
            //         ]));
            //     }
            // }

            // 3️⃣ Create Items
            // if (!empty($data['Items'])) {
            //     foreach ($data['Items'] as $itemData) {
            //         ItemModel::create(array_merge($itemData, [
            //             'CustomerID' => $customer->ID
            //         ]));
            //     }
            // }

            DB::commit();
            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
