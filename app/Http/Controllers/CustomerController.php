<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function store(StoreCustomerRequest $request)
    {
       // return $request;
        try {
            $customer = $this->customerService->createCustomer($request->validated());

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
}
