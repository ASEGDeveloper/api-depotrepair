<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true; // allow all users, modify if needed
    }

    public function rules()
    {
        return [
            // Customer fields
            'CustomerName' => 'required|string|max:255',
            'CustomerNumber' => 'required',
            'AccountID' => 'nullable|string|max:100',
            'TRN' => 'nullable|string|max:100',
            'LocationNumber' => 'nullable|string|max:100',
            'AccountNumber' => 'nullable|string|max:100',

            // Sites array
            // 'Sites' => 'nullable|array',
            // 'Sites.*.SiteCode' => 'nullable|string|max:100',
            // 'Sites.*.SiteAddress' => 'nullable|string|max:500',
            // 'Sites.*.SiteUseCode' => 'nullable|string|max:50',
            // 'Sites.*.PaymentTermID' => 'nullable|string|max:100',
            // 'Sites.*.PaymentTerm' => 'nullable|string|max:100',
            // 'Sites.*.PaymentTermDesc' => 'nullable|string|max:255',
            // 'Sites.*.Address1' => 'nullable|string|max:255',
            // 'Sites.*.Address2' => 'nullable|string|max:255',
            // 'Sites.*.Address3' => 'nullable|string|max:255',
            // 'Sites.*.Address4' => 'nullable|string|max:255',
            // 'Sites.*.City' => 'nullable|string|max:100',
            // 'Sites.*.State' => 'nullable|string|max:100',
            // 'Sites.*.Postcode' => 'nullable|string|max:20',
            // 'Sites.*.PartyName' => 'nullable|string|max:255',
            // 'Sites.*.PartyShipToLocation' => 'nullable|string|max:255',
            // 'Sites.*.ShipToAddress' => 'nullable|string|max:255',
            // 'Sites.*.CounterName' => 'nullable|string|max:255',

            // Items array
            // 'Items' => 'nullable|array',
            // 'Items.*.ItemNumber' => 'nullable|string|max:100',
            // 'Items.*.Description' => 'nullable|string|max:500',
            // 'Items.*.PRIMARY_UOM_CODE' => 'nullable|string|max:50',
            // 'Items.*.PURCHASING_ITEM_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.SHIPPABLE_ITEM_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.CUSTOMER_ORDER_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INTERNAL_ORDER_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INV_ITEM_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.ENG_ITEM_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.SERVICE_ITEM_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INVENTORY_ASSET_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.PURENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.CUST_ORG_ENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INT_ORDER_ENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.SO_TRX_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.MTL_TRX_ENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.STOCK_ENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.BOM_ENAB_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INSPECT_REQ_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.RECEIPT_REQ_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.INV_ITEM_STATUS_CODE' => 'nullable|string|max:50',
            // 'Items.*.SERV_BIL_FLAG' => 'nullable|in:Y,N',
            // 'Items.*.MATERIAL_BILLABLE' => 'nullable|in:Y,N',
            // 'Items.*.OrganizationID' => 'nullable|string|max:100',
        ];
    }
}
