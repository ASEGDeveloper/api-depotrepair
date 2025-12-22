<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemMasterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $itemId = $this->route('id');

        return [
            'InventoryItemID' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($itemId) {
                    $query = DB::connection('sqlsrv')
                        ->table('item_master_dpr')
                        ->where('InventoryItemID', $value);
                    
                    if ($itemId) {
                        $query->where('ID', '!=', $itemId);
                    }
                    
                    if ($query->exists()) {
                        $fail('This Inventory Item ID already exists.');
                    }
                },
            ],
            'ItemNumber' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($itemId) {
                    $query = DB::connection('sqlsrv')
                        ->table('item_master_dpr')
                        ->where('ItemNumber', $value);
                    
                    if ($itemId) {
                        $query->where('ID', '!=', $itemId);
                    }
                    
                    if ($query->exists()) {
                        $fail('This Item Number already exists.');
                    }
                },
            ],
            'TankType' => 'nullable|string|max:100',
            'Manufacturer' => 'nullable|string|max:150',
            'UnPortableTankType' => 'nullable|string|max:150',
            'Capacity' => 'nullable|numeric',
            'Description' => 'nullable|string',
            'PrimaryUOM' => 'nullable|string|max:50',
            'MAWP' => 'nullable|numeric',
            'PurchasingFLAG' => 'nullable|boolean',
            'OrganizationID' => 'nullable|integer',
            'DATALOAD_TIME' => 'nullable|date',
        ];
    }
}