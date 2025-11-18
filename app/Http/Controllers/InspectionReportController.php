<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InspectionReportModel;
use App\Models\InstallBaseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Matcher\Any;

class InspectionReportController extends Controller
{


public function searchInstallbase_Test(Request $request)
{
    $search = $request->input('search', '');

    $results = InstallBaseModel::query()
        ->select('ID', 'Customer_Name', 'ITEM', 'Serial_Numbers')
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Customer_Name', 'LIKE', "%{$search}%")
                  ->orWhere('ITEM', 'LIKE', "%{$search}%")
                  ->orWhere('Serial_Numbers', 'LIKE', "%{$search}%");
            });
        })
        ->orderBy('ID', 'DESC')  // 🔥 latest first
        ->distinct()
        ->limit(10)
        ->get();

    return response()->json($results);
}




public function searchInspection(Request $request)
{
    $query = InspectionReportModel::query();

    // Filter by ITEM
    if ($request->filled('Inspection_ID')) {
        $query->where('Inspection_ID', 'LIKE', '%' . $request->Inspection_ID . '%');
    }

    // Filter by Customer Name
    if ($request->filled('Customer_Name')) {
        $query->where('Customer_Name', 'LIKE', '%' . $request->Customer_Name . '%');
    }

    // Filter by Serial Numbers
    if ($request->filled('Creation_Date')) {
        $query->where('Creation_Date', 'LIKE', '%' . $request->Creation_Date . '%');
    }

    // Select fields explicitly and order by latest first
    $results = $query->select('ID', 'Inspection_ID', 'Creation_Date', 'Customer_Name','Status')
                     ->orderByDesc('ID')
                     ->get();

    return response()->json($results);
}

public function showInspectionFetch($id)
    {
        $item = InspectionReportModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($item);
    }



public function showInstallbaseFetch($id)
    {
        $item = InstallBaseModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
        return response()->json($item);
    }



public function save(Request $request, $id = null)
{
 
    try {

        // Validate fields
        // $validated = $request->validate([             
        //     'Unit_Number'           => 'required|string|max:100',
        //     'Customer_Name'         => 'required|string|max:150',
        //     'Capacity'              => 'nullable|string|max:255',
        //     'Initialtest'           => 'nullable|string|max:255',
        //     'InnertankMaterial'     => 'nullable|string|max:255',
        //     'Last2_to_5yrTest'      => 'nullable|string|max:255',
        //     'Last5yrTest'           => 'nullable|string|max:255',
        //     'LocationOfInspection'  => 'nullable|string|max:255',
        //     'Manufacturer'          => 'nullable|string|max:255',
        //     'MaxGrossWeight'        => 'nullable|string|max:255',
        //     'NextCSCDue'            => 'nullable|string|max:255',
        //     'NexttestDue'           => 'nullable|string|max:255',
        //     'Outertankmaterial'     => 'nullable|string|max:255',
        //     'Results'               => 'nullable|string|max:255',
        //     'SurveyDate'            => 'nullable|date',
        //     'Survey_Type'            => 'nullable|string|max:50',
        //     'Surveyor'              => 'nullable|string|max:255',
        //     'TareWeight'            => 'nullable|string|max:255',
        //     'UnPortableTankType'    => 'nullable|string|max:255',
        //     'Vacuum_reading'        => 'nullable|string|max:255',
        //     'comments'              => 'nullable|string|max:500',
        // ]);

       // dd( $validated);
        // Create/update record
        $query = InspectionReportModel::updateOrCreate(
            ['ID' => $id ?? 0],
            [
                'Inspection_ID'        => $this->generateCode(),
                'Unit_Number'          => $request->Unit_Number,
                'Customer_Name'        => $request->Customer_Name,
                'Capacity_L'             => $request->Capacity,
                'Initial_Test_MMM_YY'          => $request->Initialtest,
                'Last_Cargo '             => $request->LastCargo,
                'Inner_Tank_Material'    => $request->InnertankMaterial,
                'Last_2_5yr_Test_MMM_YY'     => $request->Last_2_5yr_Test_MMM_YY,
                'Last_5yr_Test_MMM_YY'          => $request->Last_5yr_Test_MMM_YY,
                'Location_of_Inspection' => $request->LocationOfInspection,
                'Manufacturer'         => $request->Manufacturer,
                'Max_Gross_Weight_kg'       => $request->MaxGrossWeight,
                'Next_CSC_Due'           => $request->NextCSCDue,
                'Next_Test_Due_MMM_YY'          => $request->NexttestDue,
                'Outer_Tank_Material'    => $request->OuterTankMaterial,
                'Results'              => $request->Results,
                'Survey_Date'           => $request->SurveyDate,
                'Survey_Type'           => $request->SurveyType,
                'Surveyor'             => $request->Surveyor,
                'Tare_Weight_kg'           => $request->TareWeight,
                'Un_Portable_Tank_Type'   => $request->UnPortableTankType,
                'Vacuum_reading'       => $request->Vacuum_reading,
                'Comments'             => $request->comments,
                'Status'               => $request->status,
                'DATALOAD_TIME'        => now(),
            ]
        );

 


        return response()->json([
            'status'  => 'success',
            'message' => $id 
                ? 'Inspection report record updated successfully.' 
                : 'Inspection report record created successfully.',
            'data'    => $query
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => 'error',
            'message' => 'An unexpected error occurred while saving the inspection report.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function update(Request $request, $id = null)
{
   
    try {

        // Validate fields
        // $validated = $request->validate([             
        //     'Unit_Number'           => 'required|string|max:100',
        //     'Customer_Name'         => 'required|string|max:150',
        //     'Capacity'              => 'nullable|string|max:255',
        //     'Initialtest'           => 'nullable|string|max:255',
        //     'InnertankMaterial'     => 'nullable|string|max:255',
        //     'Last2_to_5yrTest'      => 'nullable|string|max:255',
        //     'Last5yrTest'           => 'nullable|string|max:255',
        //     'LocationOfInspection'  => 'nullable|string|max:255',
        //     'Manufacturer'          => 'nullable|string|max:255',
        //     'MaxGrossWeight'        => 'nullable|string|max:255',
        //     'NextCSCDue'            => 'nullable|string|max:255',
        //     'NexttestDue'           => 'nullable|string|max:255',
        //     'Outertankmaterial'     => 'nullable|string|max:255',
        //     'Results'               => 'nullable|string|max:255',
        //     'SurveyDate'            => 'nullable|date',
        //     'Survey_Type'            => 'nullable|string|max:50',
        //     'Surveyor'              => 'nullable|string|max:255',
        //     'TareWeight'            => 'nullable|string|max:255',
        //     'UnPortableTankType'    => 'nullable|string|max:255',
        //     'Vacuum_reading'        => 'nullable|string|max:255',
        //     'comments'              => 'nullable|string|max:500',
        // ]);

       // dd( $validated);
        // Create/update record
        $query = InspectionReportModel::updateOrCreate(
            ['ID' => $id ?? 0],
            [
                //'Inspection_ID'        => $this->generateCode(),
                'Unit_Number'          => $request->Unit_Number,
                'Customer_Name'        => $request->Customer_Name,
                'Capacity_L'             => $request->Capacity,
                'Initial_Test_MMM_YY'          => $request->Initialtest,
                'Last_Cargo '             => $request->LastCargo,
                'Inner_Tank_Material'    => $request->InnertankMaterial,
                'Last_2_5yr_Test_MMM_YY'     => $request->Last_2_5yr_Test_MMM_YY,
                'Last_5yr_Test_MMM_YY'          => $request->Last_5yr_Test_MMM_YY,
                'Location_of_Inspection' => $request->LocationOfInspection,
                'Manufacturer'         => $request->Manufacturer,
                'Max_Gross_Weight_kg'       => $request->MaxGrossWeight,
                'Next_CSC_Due'           => $request->NextCSCDue,
                'Next_Test_Due_MMM_YY'          => $request->Next_Test_Due_MMM_YY,
                'Outer_Tank_Material'    => $request->OuterTankMaterial,
                'Results'              => $request->Results,
                'Survey_Date'           => $request->SurveyDate,
                'Survey_Type'           => $request->SurveyType,
                'Surveyor'             => $request->Surveyor,
                'Tare_Weight_kg'           => $request->TareWeight,
                'Un_Portable_Tank_Type'   => $request->UnPortableTankType,
                'Vacuum_reading'       => $request->Vacuum_reading,
                'Comments'             => $request->comments,
                'Status'               => $request->status,
                'DATALOAD_TIME'        => now(),
            ]
        );

 


        return response()->json([
            'status'  => 'success',
            'message' => $id 
                ? 'Inspection report record updated successfully.' 
                : 'Inspection report record created successfully.',
            'data'    => $query
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => 'error',
            'message' => 'An unexpected error occurred while saving the inspection report.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



public function generateCode()
{
    // Prefix
    $prefix = "CRYOTECH";

    // Current date in YYYYMMDD
    $date = now()->format('Ymd');

    // Auto-increment sequence (fetch last sequence from DB)
    // $lastRecord = InspectionReportModel::orderBy('ID', 'DESC')->first();
    // $lastSequence = $lastRecord ? intval($lastRecord->sequence ?? 0) : 0;
    // $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);

    // // Final Code
    // $finalCode = "{$prefix}|{$date}|{$customerNumber}|{$newSequence}";

    $unique = random_int(10000000, 99999999);
    $finalCode = "{$prefix}{$date}{$unique}";

    return $finalCode;
}


public function saveInspection(Request $request) 
{
    // ... (Initial validation and data collection remains the same)
    $validated = $request->all();
    $images = collect($request->file('images'));
    $descriptions = $validated['descriptions'] ?? [];
    $inspectionID = $validated['inspectionID'] ?? null;

    if (empty($descriptions) && $images->isEmpty()) {
        return response()->json([
            'message' => 'No descriptions or images provided.',
        ], 400);
    }

    $savedRecords = [];

    try {
        DB::beginTransaction();

        foreach ($descriptions as $index => $description) {

            $imageFile = $images->get($index);

            // Base data
            $data = [
                'inspection_id' => $inspectionID,
                'description'   => $description ?? '',
                'image_data'    => null, // Initialize
                'original_filename' => null, // Initialize
                'created_at'    => now(),
                'updated_at'    => now(),
                'is_deleted'    => 0,
            ];

            // Add image data if uploaded
            if ($imageFile && $imageFile->isValid()) {
                
                // 1. Read raw binary content safely
                $binary = file_get_contents($imageFile->getRealPath());
                
                // 2. Get the MIME type (e.g., 'image/jpeg')
                $mimeType = $imageFile->getClientMimeType();

                // 3. CORRECTLY construct the Base64 Data URI string.
                // This ensures only the Base64 encoded string is used in the text field.
                $base64Data = base64_encode($binary);
                $dataURI = "data:{$mimeType};base64,{$base64Data}";

                // Store the Base64 Data URI in the data array
                $data['image_data'] = $dataURI; 
                $data['original_filename'] = $imageFile->getClientOriginalName();
            }

            // Insert into the database
            $newId = DB::table('inspection_images')->insertGetId($data);

            // IMPORTANT: Never return the full Base64 string in the JSON response
            // The JSON encoder might still struggle with very large data strings,
            // even if they are valid UTF-8, and it severely bloats the response.
            // If you need the image in the response, fetch it separately or send the ID.

            // Only include safe fields in the response
            $savedRecords[] = [
                'id'     => $newId,
                'action' => 'created',
            ];
        }

        DB::commit();

        return response()->json([
            'message'       => 'Inspection data inserted successfully.',
            'inspection_id' => $inspectionID,
            'records'       => $savedRecords,
        ], 200);

    } catch (\Exception $e) {
        // ... (Error handling remains the same)
        DB::rollBack();
        Log::error('Inspection save error: ' . $e->getMessage(), ['exception' => $e]);

        return response()->json([
            'message' => 'An error occurred during inspection save.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


   

public function showInspectionImages($id)
    {
        
        $images = DB::table('inspection_images')
                    ->where('inspection_id', $id)
                    ->where('is_deleted', 0)
                    ->select('id', 'inspection_id', 'description', 'image_data')
                    ->orderBy('id', 'DESC')
                    ->get();
        if (!$images) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($images);
    }




    }