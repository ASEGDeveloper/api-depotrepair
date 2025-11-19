<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InstallBaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerModel;
use App\Models\ItemMasterModel;

class InstallBaseController extends Controller
{
public function save(Request $request, $id = null)
{
   // return $request->SerialNumbers;
    try {
        // ✅ Validate required fields
        $validated = $request->validate([
            'ITEM'           => 'required|string|max:100',
            'SerialNumbers' => 'required|string|max:100|unique:installbase_dpr,Serial_Numbers',
            'CustomerName'   => 'required|string|max:150',
        ]);

        // ✅ Create or update record
        $installbase = InstallBaseModel::updateOrCreate(
            ['ID' => $id ?? 0],
            [
                'ITEM'           => $request->ITEM,
                'Serial_Numbers' => $request->SerialNumbers,
                'Customer_Name'  => $request->CustomerName,
                'DATALOAD_TIME'  => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => $id ? 'Install base record updated successfully.' : 'Install base record created successfully.',
            'data'    => $installbase
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // ❌ Handle validation errors
        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        // ❌ Handle unexpected errors
        return response()->json([
            'status'  => 'error',
            'message' => 'An unexpected error occurred while saving the install base record.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function getInstallBase(Request $request)
{
    try {
        $page   = (int) $request->input('page', 1);
        $limit  = (int) $request->input('limit', 10);
        $search = trim($request->input('search', ''));

        $query = InstallBaseModel::query()
            ->select(['ID', 'ITEM', 'Serial_Numbers', 'Customer_Name'])
            ->orderBy('ID', 'desc');

        // ✅ Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('ITEM', 'like', "%{$search}%")
                  ->orWhere('Serial_Numbers', 'like', "%{$search}%")
                  ->orWhere('Customer_Name', 'like', "%{$search}%");
            });
        }

        // ✅ Get total before pagination
        $total = $query->count();

        // ✅ Apply pagination
        $records = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return response()->json([
            'status'     => 'success',
            'data'       => $records,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $limit,
            'total_pages'=> ceil($total / $limit)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Failed to fetch install base records.',
            'error'   => $e->getMessage()
        ], 500);
    }
}



public function update(Request $request, $id)
{
    try {
        // ✅ Validate incoming data (fields based on installbase_dpr)
        $validated = $request->validate([
            'ITEM'           => 'nullable|string|max:255',
            'SerialNumbers' => 'nullable|string|max:255',
            'CustomerName'   => 'nullable|string|max:255',
        ]);

        // ✅ Find the record or throw 404 if not found
        $installbase = InstallBaseModel::findOrFail($id);

        // ✅ Update existing record
        $installbase->update([
            'ITEM'           => $validated['ITEM'] ?? $installbase->ITEM,
            'Serial_Numbers' => $validated['SerialNumbers'] ?? $installbase->Serial_Numbers,
            'Customer_Name'   => $validated['CustomerName'] ?? $installbase->Customer_Name,
            
            'DATALOAD_TIME'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Install base record updated successfully.',
            'data'    => $installbase,
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Install base record not found.',
        ], 404);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while updating the install base record.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function searchInstallBase(Request $request)
{
    try {
        $query = InstallBaseModel::query();

        // Apply grouped search filters
        $query->when(
            $request->filled(['ITEM', 'SerialNumbers', 'CustomerName']),
            function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    if ($request->filled('ITEM')) {
                        $sub->where('ITEM', 'LIKE', '%' . $request->ITEM . '%');
                    }

                    if ($request->filled('SerialNumbers')) {
                        $sub->orWhere('Serial_Numbers', 'LIKE', '%' . $request->SerialNumbers . '%');
                    }

                    if ($request->filled('CustomerName')) {
                        $sub->orWhere('Customer_Name', 'LIKE', '%' . $request->CustomerName . '%');
                    }
                });
            }
        );

        // Apply date filter separately (AND condition)
        if ($request->filled('Creation_Date')) {
            $query->whereDate('Creation_Date', $request->Creation_Date);
        }

        // Select specific fields
        $results = $query->select('ID', 'ITEM', 'Serial_Numbers', 'Customer_Name')
                         ->orderBy('ID', 'desc')
                         ->get();

        return response()->json([
            'status'  => 'success',
            'message' => $results->isEmpty() ? 'No matching records found.' : 'Install base records fetched successfully.',
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



// public function searchInstallBase(Request $request)
// {
//     try {
//         $query = InstallBaseModel::query();

//         // ✅ Check if any search filters are provided
//         $hasFilter = $request->filled(['ITEM', 'SerialNumbers', 'CustomerName','Creation_Date']);

//         if ($hasFilter) {
//             if ($request->filled('ITEM')) {
//                 $query->where('ITEM', 'LIKE', '%' . $request->ITEM . '%');
//             }

//             if ($request->filled('SerialNumbers')) {
//                 $query->orWhere('Serial_Numbers', 'LIKE', '%' . $request->SerialNumbers . '%');
//             }

//             if ($request->filled('CustomerName')) {
//                 $query->orWhere('Customer_Name', 'LIKE', '%' . $request->CustomerName . '%');
//             }

//           if ($request->filled('Creation_Date')) {
//             $query->whereDate('Creation_Date', $request->Creation_Date);
//         }

//                 }

//         // ✅ Select the fields you want to return (include ID)
//         $results = $query->select('ID', 'ITEM', 'Serial_Numbers', 'Customer_Name')
//                          ->orderBy('ID', 'desc')
//                          ->get();

//         return response()->json([
//             'status'  => 'success',
//             'message' => $results->isEmpty() ? 'No matching records found.' : 'Install base records fetched successfully.',
//             'data'    => $results
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status'  => 'error',
//             'message' => 'Failed to search install base records.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }

public function show($id)
    {
        $item = InstallBaseModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
        return response()->json($item);
    }
 
     public function searchCustomers(Request $request)
    {
         
        $search = $request->input('search', '');

        $customers = CustomerModel::query()
            ->select('ID','CustomerName')
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
            ->select('ID','ItemNumber')
            ->when($search, function ($query, $search) {
                $query->where('ItemNumber', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($items);
    }


}
