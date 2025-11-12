<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\CustomerModel;
use App\Models\CustomerSiteModel;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function searchCustomer(Request $request)
    {

        return $searchResult = $this->customerService->searchCustomerService($request);
    }

    public function getSingleCustomer($id)
    {

        $customer = CustomerModel::select([
            'ID',
            'OrganizationID',
            'CustomerName',
            'CustomerNumber',
            'AccountID',
            'PaymentTermID',
            'TRN',
            'PaymentTerms',
            'LocationNumber'
        ])
            ->find($id);

        if ($customer) {
            $customer->sites = CustomerSiteModel::where('Customer_ID', $id)
                ->select([
                    'ID',
                    'Customer_ID',
                    'CustomerSite',
                    'SiteAddress',
                    'BillTo',
                    'ShipTo',
                    'EnabledFlag',
                ])
                ->get();
        }


        return response()->json($customer);
    }

    // StoreCustomerRequest $request
    public function store(Request $request)
    {

        // $request->validated()
        // return response()->json($request->customerId);
        try {
            $customer = $this->customerService->createCustomer($request);

            return response()->json([
                'success' => true,
                'message' => 'Customer, Sites, and Items created successfully',
                'CustomerID' => $customer->ID
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCustomersList(Request $request)
    {
        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = CustomerModel::query()
            ->select([
                'ID',
                'OrganizationID',
                'CustomerName',
                'CustomerNumber',
                'AccountID',
                'PaymentTermID',
                'TRN',
                'PaymentTerms',
                'LocationNumber'
            ])
            ->orderBy('ID', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('CustomerName', 'like', "%{$search}%")
                    ->orWhere('CustomerNumber', 'like', "%{$search}%")
                    ->orWhere('AccountID', 'like', "%{$search}%")
                    ->orWhere('PaymentTermID', 'like', "%{$search}%")
                    ->orWhere('TRN', 'like', "%{$search}%")
                    ->orWhere('PaymentTerms', 'like', "%{$search}%")
                    ->orWhere('LocationNumber', 'like', "%{$search}%");
            });
        }

        // if (!empty($status)) {
        //     $query->where('status', $status);
        // }

        $total = $query->count();

        $employees = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'data'     => $employees,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $limit
        ];
    }
}
