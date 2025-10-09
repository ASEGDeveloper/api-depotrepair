<?php
 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TnaEntry;
use Illuminate\Http\Request;

class TnaEntryController extends Controller
{
    //  Fetch all records
    public function index()
    {
        $data = TnaEntry::all();
        return response()->json($data);
    }

    //  Fetch a single record by ID
    public function show($id)
    {
        $record = TnaEntry::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }
        return response()->json($record);
    }

    //  Create a new record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'COMPANYCODE' => 'nullable|string',
            'EMPLOYEECODE' => 'nullable|string',
            'JOBCODE' => 'nullable|string',
            'JOBSEQNO' => 'nullable|string',
            'EXPORTFLAG' => 'nullable|string',
            'OPST' => 'nullable|string',
            'PROJECTEDENDDATE' => 'nullable|string',
            'PROJECTEDENDTIME' => 'nullable|string',
            'OR_UPD_FLG' => 'nullable|string',
            'TAS_DATA_FROM' => 'nullable|string',
            'ENTRY_MODE' => 'nullable|string',
            'IS_MANUAL' => 'nullable|string',
            'SD' => 'nullable|date',
            'ED' => 'nullable|date',
        ]);

        $record = TnaEntry::create($validated);
        return response()->json(['message' => 'Record created successfully', 'data' => $record], 201);
    }

    //  Update a record by ID
    public function update(Request $request, $id)
    {
        $record = TnaEntry::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $record->update($request->all());
        return response()->json(['message' => 'Record updated successfully', 'data' => $record]);
    }

    //  Delete a record by ID
    public function destroy($id)
    {
        $record = TnaEntry::find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $record->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }
}
