<?php

namespace App\Jobs;

use App\Mail\WipReportMail;
use App\Services\WipReportDataService;
use App\Services\WipReportExcelExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateWipReportExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 1200;

    public function __construct(
        private array  $filters,
        private string $recipientEmail,
        private string $recipientName,
        private string $branchLabel
    ) {}

    public function handle(WipReportDataService $dataService, WipReportExcelExport $exporter): void
    {
        $reportData = $dataService->buildReportData($this->filters);
        $filePath   = $exporter->generate($reportData);

        Mail::to($this->recipientEmail)->send(
            new WipReportMail(
                recipientName: $this->recipientName,
                endDateLabel:  $reportData['end_date_label'],
                branchLabel:   $this->branchLabel,
                filePath:      $filePath,
            )
        );

        // Clean up the file after sending
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateWipReportExcel job failed', [
            'filters' => $this->filters,
            'email'   => $this->recipientEmail,
            'error'   => $e->getMessage(),
        ]);
    }
}
