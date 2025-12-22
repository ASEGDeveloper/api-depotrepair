<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemMasterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set to true to allow any authenticated user
    }

    public function rules()
    {
        return [
            // InventoryItemID will be auto-generated, so we remove 'required'
            'InventoryItemID' => 'nullable|string|max:50|unique:deporepair.item_master_dpr,InventoryItemID',
            'ItemNumber' => 'required|string|max:100|unique:deporepair.item_master_dpr,ItemNumber',
            'TankType' => 'nullable|string|max:100',
            'Manufacturer' => 'nullable|string|max:150',
            'UnPortableTankType' => 'nullable|string|max:150',
            'Capacity' => 'nullable|numeric',
            'Description' => 'nullable|string',
            'PrimaryUOM' => 'nullable|string|max:50', 
            'DATALOAD_TIME' => 'nullable|date',
        ];
    }
}
