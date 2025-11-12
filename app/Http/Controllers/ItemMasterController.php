<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\ItemMasterRequest;
use App\Models\ItemMasterModel;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    // Create or Update Item
    // ItemMasterRequest
    public function save(ItemMasterRequest $request, $id = null)
    {
        $item = ItemMasterModel::updateOrCreate(
            ['ID' => $id ?? 0], // Check if the item exists, if $id is null it will create new
            [
                'InventoryItemID'    =>  mt_rand(100000, 999999),
                'ItemNumber'         => $request->ItemNumber ?? null,
                'TankType'           => $request->TankType ?? null,
                'Manufacturer'       => $request->Manufacturer ?? null,
                'UnPortableTankType' => $request->UnPortableTankType ?? null,
                'Capacity'           => $request->Capacity ?? null,
                'Description'        => $request->Description ?? null,
                'PrimaryUOM'         => $request->PrimaryUOM ?? null,
                'PurchasingFLAG'     => $request->PurchasingFLAG ?? 0,
                'OrganizationID'     => '9608',
                'DATALOAD_TIME'      => $request->DATALOAD_TIME ?? now(),
            ]
        );

        return response()->json([
            'message' => 'Item created successfully.',
            'data' => $item
        ], 200);
    }


 public function searchItems(Request $request)
{
    $query = ItemMasterModel::query();

    $hasFilter = $request->filled(['ItemNumber', 'TankType', 'Manufacturer']);

    if ($hasFilter) {
        if ($request->filled('ItemNumber')) {
            $query->where('ItemNumber', 'LIKE', '%' . $request->ItemNumber . '%');
        }

        if ($request->filled('TankType')) {
            $query->orWhere('TankType', 'LIKE', '%' . $request->TankType . '%');
        }

        if ($request->filled('Manufacturer')) {
            $query->orWhere('Manufacturer', 'LIKE', '%' . $request->Manufacturer . '%');
        }
    }

    // Explicitly select the fields you want to return, including id
    return $query->select('id', 'ItemNumber', 'TankType', 'Manufacturer')->get();
}





    // Get all items
    public function index()
    {
        $items = ItemMasterModel::all();
        return response()->json($items);
    }

    // Get single item
    public function show($id)
    {
        $item = ItemMasterModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
        return response()->json($item);
    }
}
