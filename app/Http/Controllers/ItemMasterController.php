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
    public function save(ItemMasterRequest $request, $itemID = null)
    {

        $uniqueItemNumber = $request->ItemNumber;

            if ($uniqueItemNumber) {
                $count = ItemMasterModel::where('ItemNumber', $uniqueItemNumber)
                            ->when($request->itemID, fn($q) => $q->where('ID', '!=', $request->itemID))
                            ->count();

                if ($count > 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Item Number already exists.'
                    ], 422);
                }
            }

       
        $item = ItemMasterModel::updateOrCreate(
            ['ID' => $request->itemID ?? 0],
            [
                'InventoryItemID'    =>  mt_rand(100000, 999999),
                'ItemNumber'         => $uniqueItemNumber,
                'TankType'           => $request->TankType ?? null,
                'Manufacturer'       => $request->Manufacturer ?? null,
                'UnPortableTankType' => $request->UnPortableTankType ?? null,
                'Capacity'           => $request->Capacity ?? null,
                'Description'        => $request->Description ?? null,
                'PrimaryUOM'         => $request->PrimaryUOM ?? null,
                'MAWP'         => $request->MAWP ?? null,
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

    public function update(Request $request, $itemID)
    { 
        try {
            // Validate incoming data
            $validated = $request->validate([
                'ItemNumber'         => 'nullable|string|max:255',
                'TankType'           => 'nullable|string|max:255',
                'Manufacturer'       => 'nullable|string|max:255',
                'UnPortableTankType' => 'nullable|string|max:255',
                'Capacity'           => 'nullable|numeric',
                'Description'        => 'nullable|string',
                'PrimaryUOM'         => 'nullable|string|max:255', 
                 'MAWP'         => 'nullable|string|max:255', 
                'PurchasingFLAG'     => 'nullable|boolean',
            ]);

            // Find the item or throw 404 if not found
            $item = ItemMasterModel::findOrFail($itemID);

            // Update existing record
            $item->update([
                'ItemNumber'         => $validated['ItemNumber'] ?? $item->ItemNumber,
                'TankType'           => $validated['TankType'] ?? $item->TankType,
                'Manufacturer'       => $validated['Manufacturer'] ?? $item->Manufacturer,
                'UnPortableTankType' => $validated['UnPortableTankType'] ?? $item->UnPortableTankType,
                'Capacity'           => $validated['Capacity'] ?? $item->Capacity,
                'Description'        => $validated['Description'] ?? $item->Description,
                'PrimaryUOM'         => $validated['PrimaryUOM'] ?? $item->PrimaryUOM,
                 'MAWP'              => $validated['MAWP'] ??  $item->MAWP,
                'PurchasingFLAG'     => $validated['PurchasingFLAG'] ?? $item->PurchasingFLAG,
                'DATALOAD_TIME'      => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully.',
                'data'    => $item,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
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



    public function getItemsList(Request $request)
    {
    
    $page   = $request->input('page', 1);
    $limit  = $request->input('limit', 10);
    $search = $request->input('search', '');
    $status = $request->input('status', '');

    $query = ItemMasterModel::query()
        ->select([
            'id',
            'InventoryItemID',
            'ItemNumber',
            'TankType',
            'Manufacturer',
            'UnPortableTankType',
            'Capacity',
            'Description',
            'PrimaryUOM', 
        ])
        ->orderBy('ID', 'desc');

    // Apply search filter
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('ItemNumber', 'like', "%{$search}%")
                ->orWhere('TankType', 'like', "%{$search}%")
                ->orWhere('Manufacturer', 'like', "%{$search}%")
                ->orWhere('UnPortableTankType', 'like', "%{$search}%")
                ->orWhere('Capacity', 'like', "%{$search}%")
                ->orWhere('Description', 'like', "%{$search}%")
                ->orWhere('PrimaryUOM', 'like', "%{$search}%");
        });
    }

    // Optional status filter (if applicable in your table)
    if (!empty($status)) {
        $query->where('PurchasingFLAG', $status);
    }

    $total = $query->count();

    $items = $query->offset(($page - 1) * $limit)
        ->limit($limit)
        ->get();

    return response()->json([
        'data'      => $items,
        'total'     => $total,
        'page'      => $page,
        'per_page'  => $limit
    ]);
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
