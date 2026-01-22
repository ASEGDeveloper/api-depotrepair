<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InstallBaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerModel;
use App\Models\InstallBaseItemModel;
use App\Models\ItemMasterModel;
use Illuminate\Support\Facades\Auth;

class InstallBaseController extends Controller
{
 
    public function save(Request $request, $id = null)
    {
        DB::beginTransaction(); // ✅ Start Transaction

        try {
            // Validate required fields
            // $validated = $request->validate([
            //     'CustomerName' => 'required|string|max:150',
            // ]);

            // Create or update install base record
            $installbase = InstallBaseModel::updateOrCreate(
                ['ID' => $id ?? 0],
                [
                    'CustomerID' => $request->CustomerID,
                    'DATALOAD_TIME' => now(),
                ]
            );

            $items = $request->Items ?? [];

            if (is_array($items)) {
                foreach ($items as $item) {

                    // Convert array to object
                    $itemObject = is_array($item) ? (object)$item : $item;

                    // Check if updating existing item
                    $lookupId = (!empty($itemObject->id) && $itemObject->id > 0) ? $itemObject->id : null;

                    // Common data for both insert & update
                    $data = [
                        'installbase_id' => $installbase->ID,
                        'Item_Numbers'   => $itemObject->ItemNumber ?? null,
                        'Serial_Numbers' => str_replace(' ','',$itemObject->SerialNumber) ?? null, 
                    ];

                    if ($lookupId) {
                        // Update existing item
                        InstallBaseItemModel::where('ID', $lookupId)->update($data);
                    } else {
                        // Insert new item → add creation fields
                        $data['created_by']   = 88888;
                        $data['created_date'] = now();

                        InstallBaseItemModel::create($data);
                    }
                }
            }

            DB::commit(); // ✅ Commit Transaction

            return response()->json([
                'status'  => 'success',
                'message' => $id ? 'Install base record updated successfully.' : 'Install base record created successfully.',
                'data'    => $installbase
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack(); // ❌ Rollback on validation error

            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack(); // ❌ Rollback on system error

            return response()->json([
                'status'  => 'error',
                'message' => 'An unexpected error occurred while saving the install base record.',
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),   // 👈 Helpful for debugging
                'file'    => $e->getFile(),   // 👈 Helpful for debugging
            ], 500);
        }
    }



public function update(Request $request, $id = null)
{
    DB::beginTransaction();

    try {
        $items = $request->Items ?? [];

        foreach ($items as $item) {

            $itemObj = (object) $item; // normalize array/object

            // Prepare fields
            $data = [
                'Item_Numbers'   => $itemObj->ItemNumber ?? null,
                'Serial_Numbers' =>  str_replace(' ','',$itemObj->SerialNumber) ?? null, 
                'installbase_id' => $id,  // parent ID
            ];

            if (!empty($itemObj->id) && $itemObj->id > 0) {

                // 🔄 UPDATE
                InstallBaseItemModel::where('ID', $itemObj->id)->update($data);

            } else {

                // ➕ INSERT (no id found)
                // $data['created_by']   = auth()->id() ?? 1;
                // $data['created_date'] = now();

                InstallBaseItemModel::create($data);
            }
        }

        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => 'Install base items saved successfully.',
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'An unexpected error occurred.',
            'error'   => $e->getMessage(),
            'line'    => $e->getLine(),
        ], 500);
    }
}
 

   
public function getInstallBase(Request $request)
{
    try {
        $page   = (int) $request->input('page', 1);
        $limit  = (int) $request->input('limit', 10);
        $search = trim($request->input('search', ''));

        // Base query with join
        $query = DB::table('deporepair.installbase_dpr as ib')
            ->join('deporepair.installbase_items_dpr as ibi', 'ib.ID', '=', 'ibi.installbase_id')
            ->join('deporepair.customers_dpr as cd','cd.ID','=','ib.CustomerID')
            ->select(
                'ib.ID',
                'cd.CustomerName as Customer_Name',
                'ibi.Item_Numbers',
                'ibi.Serial_Numbers'
            );

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ib.Customer_Name', 'like', "%{$search}%")
                  ->orWhere('ibi.Item_Numbers', 'like', "%{$search}%")
                  ->orWhere('ibi.Serial_Numbers', 'like', "%{$search}%");
            });
        }

        // Get total count (distinct users)
        $total = $query->distinct('ib.ID')->count('ib.ID');

        // Apply pagination
        $records = $query->orderBy('ib.ID', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Group items per user
        $grouped = $records->groupBy('ID')->map(function ($items, $key) {
            return [
                'ID'           => $key,
                'Customer_Name'=> $items->first()->Customer_Name,
                'Items'        => $items->map(function ($i) {
                    return [
                        'Item_Number'   => $i->Item_Numbers,
                        'Serial_Number' => $i->Serial_Numbers
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'status'      => 'success',
            'data'        => $grouped,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $limit,
            'total_pages' => ceil($total / $limit)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to fetch install base records.',
            'error'   => $e->getMessage()
        ], 500);
    }
}




    // public function update(Request $request, $id)
    // {
    //     try {
    //         // ✅ Validate incoming data (fields based on installbase_dpr)
    //         $validated = $request->validate([
    //             'ITEM'           => 'nullable|string|max:255',
    //             'SerialNumbers' => 'nullable|string|max:255',
    //             'CustomerName'   => 'nullable|string|max:255',
    //         ]);

    //         // ✅ Find the record or throw 404 if not found
    //         $installbase = InstallBaseModel::findOrFail($id);

    //         // ✅ Update existing record
    //         $installbase->update([
    //             'ITEM'           => $validated['ITEM'] ?? $installbase->ITEM,
    //             'Serial_Numbers' => $validated['SerialNumbers'] ?? $installbase->Serial_Numbers,
    //             'Customer_Name'   => $validated['CustomerName'] ?? $installbase->Customer_Name,

    //             'DATALOAD_TIME'  => now(),
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Install base record updated successfully.',
    //             'data'    => $installbase,
    //         ], 200);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Install base record not found.',
    //         ], 404);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong while updating the install base record.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }


// public function searchInstallBase(Request $request)
// {  
//     try {
//         $query = DB::table('deporepair.installbase_dpr as ib')
//             ->join('deporepair.installbase_items_dpr as ibi', 'ib.ID', '=', 'ibi.installbase_id')
//             ->join('deporepair.customers_dpr as cd', 'cd.ID', '=', 'ib.customerID')
//             ->leftJoin('deporepair.inspection_report_dpr as ird', 'ird.serialNumber', '=', 'ibi.Serial_Numbers')
//             ->whereNull('ird.serialNumber')
//             ->select(
//                 'ib.ID',
//                 'cd.CustomerName as Customer_Name',
//                 'ibi.Item_Numbers',
//                 'ibi.Serial_Numbers',
//                 'ird.id as InspectionReportID',
//             );
//            // ->where("ird.serialNumber", '==','');  

        
//          if (!empty($request->Customer_Name)) {
//             $query->where('cd.CustomerName', 'LIKE', '%' . $request->Customer_Name . '%');
//         }
 

//         // Specific filters applied individually
//         if (!empty($request->Item_Numbers)) {
//             $query->where('ibi.Item_Numbers', 'LIKE', '%' . $request->Item_Numbers . '%');
//         }

//         if (!empty($request->Serial_Numbers)) {
//             $query->where('ibi.Serial_Numbers', 'LIKE', '%' . $request->Serial_Numbers . '%');
//         }
        
//         $results = $query->orderBy('ib.ID', 'desc')->get();

//         return response()->json([
//             'status'  => 'success',
//             'message' => $results->isEmpty() ? 'No matching records found.' : 'Install base records fetched successfully.',
//             'data'    => $results
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Failed to search install base records.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }


public function searchInstallBase(Request $request)
{
    try {
        $query = DB::table('deporepair.installbase_dpr as ib')
            ->join('deporepair.installbase_items_dpr as ibi', 'ib.ID', '=', 'ibi.installbase_id')
            ->join('deporepair.customers_dpr as cd', 'cd.ID', '=', 'ib.customerID')
            ->leftJoin('deporepair.inspection_report_dpr as ird', function ($join) {
                $join->on('ird.serialNumber', '=', 'ibi.Serial_Numbers');
            })
            ->where(function ($q) {
                $q->whereNull('ird.serialNumber')
                  ->orWhere('ird.serialNumber', '');
            })
            ->select(
                'ib.ID',
                'cd.CustomerName as Customer_Name',
                'ibi.Item_Numbers',
                'ibi.Serial_Numbers',
                DB::raw('NULL as InspectionReportID')
            );

        // Apply filters safely
        if ($request->filled('Customer_Name')) {
            $query->where('cd.CustomerName', 'LIKE', '%' . trim($request->Customer_Name) . '%');
        }

        if ($request->filled('Item_Numbers')) {
            $query->where('ibi.Item_Numbers', 'LIKE', '%' . trim($request->Item_Numbers) . '%');
        }

        if ($request->filled('Serial_Numbers')) {
            $query->where('ibi.Serial_Numbers', 'LIKE', '%' . trim($request->Serial_Numbers) . '%');
        }

        $results = $query->orderByDesc('ib.ID')->get();

        return response()->json([
            'status'  => 'success',
            'message' => $results->isEmpty()
                ? 'No matching records found.'
                : 'Install base records fetched successfully.',
            'data'    => $results
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to search install base records.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



    public function show($id)
    {
        $item = InstallBaseModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
        return response()->json($item);
    }


public function getItems($serialNumber)
{
    // Check if the record exists in installbase_dpr
   // $installBase = DB::table('installbase_dpr')->find($id);

   $installBase=DB::table('deporepair.installbase_items_dpr')->where('Serial_Numbers',$serialNumber)->first();

    if (!$installBase) {
        return response()->json(['message' => 'Item not found.'], 404);
    }

    // Fetch full joined data from related tables
    $data = DB::table('deporepair.installbase_items_dpr as ibi')
    ->join('deporepair.item_master_dpr as im', 'ibi.Item_Numbers', '=', 'im.ItemNumber')
    ->join('deporepair.installbase_dpr as ib', 'ib.ID', '=', 'ibi.installbase_id')
    ->join('deporepair.deporepair.customers_dpr as cd', 'cd.ID', '=', 'ib.CustomerID')
    ->where('ibi.Serial_Numbers', $serialNumber)
    ->select(
        'ibi.Item_Numbers',
        'ibi.Serial_Numbers',
        'im.MAWP',
        'cd.CustomerName as Customer_Name',
        'im.TankType',
        'im.Manufacturer',
        'im.UnPortableTankType',
        'im.Capacity',
        'im.PrimaryUOM'
    )
    ->first();


    if (!$data) {
        return response()->json(['message' => 'No details found for this item.'], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}



public function getCustomerName($id)
{
    // Fetch main install base (single row)
    $installBase = DB::table('deporepair.installbase_dpr as ib') // alias should match
    ->join('deporepair.customers_dpr as cd', 'cd.ID', '=', 'ib.CustomerID')
    ->where('ib.ID', $id)
    ->select('cd.CustomerName as Customer_Name')
    ->first();


    if (!$installBase) {
        return response()->json(['message' => 'Install base not found.'], 404);
    }

    // Fetch all related installbase_items_dpr rows
    $items = DB::table('deporepair.installbase_items_dpr')
        ->where('installbase_id', $id)
        ->select(
            'ID as InstallBaseDetailsID',
            'Item_Numbers',
            'Serial_Numbers'
        )
        ->get();

    return response()->json([
        'status' => 'success',
        'CustomerName' => $installBase->Customer_Name,
        'items' => $items
    ]);
}





    public function searchCustomers(Request $request)
    {

        $search = $request->input('search', '');

        $customers = CustomerModel::query()
            ->select('ID', 'CustomerName')
            ->when($search, function ($query, $search) {
                $query->where('CustomerName', 'LIKE', "%{$search}%");
            })
            ->distinct()
            ->limit(10)
            ->get();

        return response()->json($customers);
    }


    public function searchItems(Request $request)
    {

        $search = $request->input('search', '');

        $items = ItemMasterModel::query()
            ->select('ID', 'ItemNumber')
            ->when($search, function ($query, $search) {
                $query->where('ItemNumber', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($items);
    }
}
