<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateWipReportExcel;
use App\Services\WipReportDataService;
use App\Services\WipReportExcelExport;
use App\Services\WipReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WipReportController extends Controller
{
    use ApiResponse;

    public function __construct(private WipReportService $service) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branchId' => 'nullable|string|max:50',
            'endDate'  => 'nullable|date',
            'filter'   => 'nullable|string|in:ALL,Zero',
        ]);

        $data = $this->service->getWipData($validated);

        return $this->successResponse($data, 'WIP Report fetched successfully.');
    }

    public function branches(): JsonResponse
    {
        $branches = $this->service->getBranches();

        return $this->successResponse($branches, 'Branches fetched successfully.');
    }

    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branchId' => 'nullable|string|max:50',
            'endDate'  => 'nullable|date',
            'filter'   => 'nullable|string|in:ALL,Zero',
            'email'    => 'required|email|max:255',
        ]);

        $employee    = $request->user();
        $recipientEmail = $validated['email'];
        $recipientName  = $employee->EmployeeName ?? 'Team';

        $branchLabel = match ($validated['branchId'] ?? 'ASE_ALL') {
            'ASE_ALL'   => 'ASE All',
            'ASAMI_ALL' => 'ASAMI All',
            'ASM_ALL'   => 'ASM All',
            'AST_ALL'   => 'AST All',
            default     => 'Branch ' . ($validated['branchId'] ?? ''),
        };

        $filters = [
            'branchId' => $validated['branchId'] ?? 'ASE_ALL',
            'endDate'  => $validated['endDate']  ?? null,
            'filter'   => $validated['filter']   ?? 'ALL',
        ];

        GenerateWipReportExcel::dispatch($filters, $recipientEmail, $recipientName, $branchLabel);

        return $this->response("Report is being generated. You will receive it at {$recipientEmail} shortly.");
    }

    public function download(Request $request, WipReportDataService $dataService, WipReportExcelExport $exporter): BinaryFileResponse
    {
        // response("Hello");

        // die("Download function is temporarily disabled for testing purposes. ");

        // This report runs several heavy synchronous queries; the default
        // 60s max_execution_time is not enough for larger date ranges/branches.
        set_time_limit(300);

        $validated = $request->validate([
            'branchId' => 'nullable|string|max:50',
            'endDate'  => 'nullable|date',
            'filter'   => 'nullable|string|in:ALL,Zero',
        ]);

        $filters = [
            'branchId' => $validated['branchId'] ?? 'ASE_ALL',
            'endDate'  => $validated['endDate']  ?? null,
            'filter'   => $validated['filter']   ?? 'ALL',
        ];

        $reportData = $dataService->buildReportData($filters);
        $filePath   = $exporter->generate($reportData);

        return response()->download($filePath, 'wip_report_' . now()->format('Ymd_His') . '.xlsx')
            ->deleteFileAfterSend(true);
    }
}
