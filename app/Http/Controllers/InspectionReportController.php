<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InspectionReportModel;
use App\Models\InstallBaseModel;
use App\Services\InspectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Matcher\Any;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InspectionReportMail;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;


class InspectionReportController extends Controller
{


    protected $inspectionService;

    public function __construct(InspectionService $inspectionService)
    {
        $this->inspectionService = $inspectionService;
    }


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


        if ($request->filled('Creation_Date')) {
            $query->whereDate('Creation_Date', $request->Creation_Date);
        }
        // Select fields explicitly and order by latest first
        $results = $query->select('ID', 'Inspection_ID', 'Creation_Date', 'Customer_Name', 'Status')
            ->orderByDesc('ID')
            ->get();

        return response()->json($results);
    }

    public function showInspectionFetch($id)
    { 

        $inspection = InspectionReportModel::find($id); 

        $images =  $this->inspectionService->getInspectionImages($id);
        $inspection->images = $images;
        $signature =  $this->inspectionService->getSignature($id);
        $inspection->signature = $signature;

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($inspection);
    }



    public function showInstallbaseFetch($id)
    {
        $item = InstallBaseModel::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
        return response()->json($item);
    }



    public function save(Request $request)
    { 
 
        try {

            $id = $request->id ?? null;

        //  Check if serial number already exists (excluding current record during update)
        $serialExists = InspectionReportModel::where('serialNumber', $request->serialNumber)
            ->when($id, function ($q) use ($id) {
                $q->where('ID', '!=', $id);
            })
            ->exists();

        if ($serialExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Serial number already exists.'
            ], 409); // Conflict
        } 

            $query = InspectionReportModel::updateOrCreate(
                ['ID' => $id ?? 0],
                [
                    'Inspection_ID'        => $this->generateCode(),
                    'serialNumber'          => $request->serialNumber,
                    'Unit_Number'          => $request->Unit_Number,
                    'Customer_Name'        => $request->Customer_Name,
                    'Capacity_L'             => $request->Capacity,
                    'Tank_Type'             => $request->TankType,
                    'Initial_Test_MMM_YY'          => $request->Initialtest,
                    'Last_Cargo'             => $request->LastCargo,
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
                    'mawp'             => $request->mawp,
                    'Comments'             => $request->comments,
                    'Status'               => $request->status,
                    'DATALOAD_TIME'        => now(),
                ]
            );

            return response()->json([
                'status'  => 'success',
                'last_inserted_id'  => $query->ID,
                'message' => $query->ID
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
           
            $query = InspectionReportModel::updateOrCreate(
                ['ID' => $id ?? 0],
                [
                    //'Inspection_ID'        => $this->generateCode(),
                    'Unit_Number'          => $request->Unit_Number,
                    'Customer_Name'        => $request->Customer_Name,
                    'Capacity_L'             => $request->Capacity,
                    'Initial_Test_MMM_YY'    => $request->Initialtest,
                    'Tank_Type'             => $request->TankType,
                    'Last_Cargo'             => $request->LastCargo,
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
                    'mawp'             => $request->mawp,
                    'Status'               => $request->status,
                    'DATALOAD_TIME'        => now(),
                ]
            );



            return response()->json([
                'status'  => 'success',
                'message' => $id
                    ? 'Inspection report record updated successfully.'
                    : 'Inspection report record created successfully.',
                'data'    => $query->Status
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
        //$prefix = "CRYOTECH";

        // Current date in YYYYMMDD
        $date = now()->format('Ymd');

        $unique = random_int(10000000, 99999999);
       // $finalCode = "{$prefix}{$date}{$unique}";
       $finalCode = "{$date}{$unique}";

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
                // if ($imageFile && $imageFile->isValid()) { 
                     
                //     $binary = file_get_contents($imageFile->getRealPath()); 
                //     $mimeType = $imageFile->getClientMimeType(); 
                //     $base64Data = base64_encode($binary);
                //     $dataURI = "data:{$mimeType};base64,{$base64Data}";  
                //     $data['image_data'] = $dataURI;
                //     $data['original_filename'] = $imageFile->getClientOriginalName();
                // }


if ($imageFile && $imageFile->isValid()) {

    $sourcePath = $imageFile->getRealPath();
    $mimeType   = $imageFile->getClientMimeType();

    // Target size
    $targetWidth = 760;

    // Get original image size
    list($width, $height) = getimagesize($sourcePath);
    $ratio = $width / $height;
    $targetHeight = intval($targetWidth / $ratio);

    // Create source image
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            throw new Exception('Unsupported image type');
    }

    // Create resized image
    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

    // Preserve transparency for PNG
    if ($mimeType === 'image/png') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
    }

    imagecopyresampled(
        $resizedImage,
        $sourceImage,
        0, 0, 0, 0,
        $targetWidth,
        $targetHeight,
        $width,
        $height
    );

    // Capture output buffer
    ob_start();
    if ($mimeType === 'image/jpeg') {
        imagejpeg($resizedImage, null, 75); // quality 75%
    } elseif ($mimeType === 'image/png') {
        imagepng($resizedImage, null, 6);
    } elseif ($mimeType === 'image/webp') {
        imagewebp($resizedImage, null, 75);
    }
    $imageData = ob_get_clean();

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    // Convert to Base64
    $base64Data = base64_encode($imageData);
    $dataURI = "data:{$mimeType};base64,{$base64Data}";

    $data['image_data'] = $dataURI;
    $data['original_filename'] = $imageFile->getClientOriginalName();
}




       
                $newId = DB::table('deporepair.inspection_images')->insertGetId($data);

             
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



    public function saveSignature(Request $request)
    {
        try {
            $base64Image = $request->signature;

            // If signature is coming as: data:image/png;base64,xxxxxx
            // remove the "data:image/*;base64," part
            if (strpos($base64Image, 'base64,') !== false) {
                $base64Image = explode('base64,', $base64Image)[1];
            }

            if (!empty($request->signature)) {

                DB::table('deporepair.inspection_signatures')->insert([
                    'inspection_id'     => $request->inspectionID,
                    'custSignatureName' => $request->custSignatureName,
                    'signature_data'    => $base64Image,  // PURE base64 image
                    'Type' => 'Customer',
                    'date'              => date('Y-m-d'),
                ]);
            }


            return response()->json([
                'status'  => true,
                'message' => 'Signature saved successfully.',
            ], 200);
        } catch (\Exception $e) {

            Log::error('Signature save error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while saving signature.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function saveSurveyorSignature(Request $request)
    {
        try {
            $base64Image = $request->surveyorSignature;

            // If signature is coming as: data:image/png;base64,xxxxxx
            // remove the "data:image/*;base64," part
            if (strpos($base64Image, 'base64,') !== false) {
                $base64Image = explode('base64,', $base64Image)[1];
            }

            if (!empty($request->surveyorSignature)) {

                DB::table('deporepair.inspection_signatures')->insert([
                    'inspection_id'     => $request->inspectionID,
                    'Surveyor_Name' => $request->surveyorSignatureName,
                    'signature_data'    => $base64Image,  // PURE base64 image
                    'Type' => 'Surveyor',
                    'date'  => date('Y-m-d'),
                ]);
            }

            InspectionReportModel::where('id', $request->inspectionID)
                ->update(['Status' => $request->Status]);

            Log::info("UPDATED TO:", [
                'id' => $request->inspectionID,
                'new_status' => InspectionReportModel::find($request->inspectionID)->Status
            ]);



            return response()->json([
                'status'  => true,
                'message' => 'Signature saved successfully.',
            ], 200);
        } catch (\Exception $e) {

            Log::error('Signature save error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while saving signature.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function reportStatusUpdate($inspectionID)
    {
        DB::table('deporepair.inspection_report_dpr')
            ->where('id', $inspectionID)
            ->update([
                'Status' => 'Report Generated', // set the new status 
            ]);
    }



    public function WebdownloadReport(Request $request, $inspectionID)
    {
        $data = $this->inspectionService->getInspectionDetails($inspectionID);
        $data = $data->getData(true);

      //  return  $data['logo']['logo'];

        // 🔍 Return the HTML view for testing
        return view('pdf_template', compact('data'));
    }


    public function downloadReport(Request $request, $inspectionID)
    {

        $data =  $this->inspectionService->getInspectionDetails($inspectionID); 
        $data = $data->getData(true); // 'true' makes it an associative array    

        $pdf = Pdf::loadView('pdf_template', compact('data'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isFontSubsettingEnabled' => true,
            ]);

        return $pdf->download("inspection_report.pdf");
    }



    public function showInspectionImages($id)
    {

        $images = DB::table('deporepair.inspection_images')
            ->where('inspection_id', $id)
            ->where('is_deleted', 0)
            ->select('id', 'inspection_id', 'description', 'image_data')
            ->orderBy('id', 'ASC')
            ->get();
        if (!$images) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }
        return response()->json($images);
    }

 

    public function delete(Request $request, $id)
    {
        try {
            DB::table('deporepair.inspection_images')
                ->where('id', $id)
                ->update([
                    'is_deleted' => 1
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Image deleted successfully.',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Delete failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getEmails($inspectionID)
    { 
       
        $data =  $this->inspectionService->getCustomerID($inspectionID);         
        return $this->inspectionService->getCustomerEmail($data->CustomerID);
    }


    


    public function sendEmail(Request $request)
    {
        $contacts = $request->input('contacts');

        // Get inspection ID from first contact
        $inspectionID = $contacts[0]['inspectionID'];

        // Fetch inspection details
        $data = $this->inspectionService->getInspectionDetails($inspectionID);
        $data = $data->getData(true); // convert to array 


         $surveyType= $data['Survey_Type'];
       //  $surveyDate= $data['Survey_Date'];
        $surveyDate =   Carbon::parse($data['Survey_Date'])->format('d-m-y');
         $itemNumber = $data['Unit_Number'];

        // Generate PDF
        $pdf = Pdf::loadView('pdf_template', compact('data'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isFontSubsettingEnabled' => true,
            ]);

        // PDF binary data
        $pdfContent = $pdf->output();

        // Send email to each contact
        foreach ($contacts as $c) {

            $email = $c['email'];
            $name  = $c['name'];

            Mail::to($email)->send(new InspectionReportMail($name, $surveyType, $surveyDate, $itemNumber, $pdfContent));
        }

        return response()->json(['status' => 'Emails sent successfully']);
    }


    public function getSurvorSignature($inspectionID)
    {

        return  $this->inspectionService->getSurSignature($inspectionID);
    }
}
