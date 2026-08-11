<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WipReportExcelExport
{
    public function generate(array $reportData): string
    {
        $companyName       = $reportData['company_name'];
        $endDateLabel      = $reportData['end_date_label'];
        $filter            = $reportData['filter'];
        $dataSet           = $reportData['dataset'];
        $branchMap         = $reportData['branch_map'];
        $rtMap             = $reportData['repair_type_map'];
        $repairOrderStatus = $reportData['repair_order_status'];
        $AD                = $reportData['AD'];
        $QD                = $reportData['QD'];
        $SR                = $reportData['SR'];
        $excludeWO         = $reportData['exclude_work_orders'];

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // --- Header rows ---
        $row = 1;
        $sheet->setCellValue("A{$row}", strtoupper($companyName));
        $sheet->mergeCells("A{$row}:Y{$row}");
        $sheet->getStyle("A{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:Y{$row}")->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue("A{$row}", 'WIP Report (Service)');
        $sheet->mergeCells("A{$row}:Y{$row}");
        $sheet->getStyle("A{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:Y{$row}")->getFont()->setBold(true);

        $row++;
        $sheet->setCellValue("A{$row}", "As of {$endDateLabel}");
        $sheet->mergeCells("A{$row}:Y{$row}");
        $sheet->getStyle("A{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- Column headers ---
        $row++;
        $headers = [
            'A' => 'Branch',          'B' => 'Repair Type',       'C' => 'Service Request',
            'D' => 'Repair Number',   'E' => 'Order Date',         'F' => 'Owner',
            'G' => 'Sales Person',    'H' => 'Customer Code',      'I' => 'Customer Name',
            'J' => 'Contact Name',    'K' => 'Item',               'L' => 'Chassis Number',
            'M' => 'Service Request Type', 'N' => 'Problem Summary', 'O' => 'WIP Remark',
            'P' => 'Status',          'Q' => 'Material Cost',      'R' => 'Sublet/Boughtout',
            'S' => 'Labour Cost',     'T' => 'Other Cost',         'U' => 'Total Cost',
            'V' => 'Delivery Date',   'W' => 'Worked Hours',       'X' => 'Other Material Cost',
            'Y' => 'Promise Date',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}{$row}", $label);
        }
        $sheet->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("O{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A{$row}:Y{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:Y{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:Y{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(40);
        foreach (range('C', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        if (empty($dataSet)) {
            return $this->writeFile($spreadsheet);
        }

        $reportTotal = $reportMaterial = $reportOtherMat = $reportSB = $reportOther = $reportWH = 0.0;

        foreach ($dataSet as $branchId => $branchData) {
            $bName = $branchMap[$branchId] ?? $this->fetchBranchName($branchId);

            [$bMat, $bOtherMat, $bSB, $bOther, $bTotal, $bWH] =
                $this->sumTotals($branchData, $filter, $excludeWO, true);

            $row++;
            $sheet->setCellValue("A{$row}", $bName);
            $this->writeSubtotalRow($sheet, $row, $bMat, $bSB, 0, $bOther, $bTotal, $bWH, $bOtherMat);
            $sheet->mergeCells("C{$row}:N{$row}");
            $sheet->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("O{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row}:Y{$row}")->getFont()->setBold(true);
            $sheet->getStyle("Q{$row}:U{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            foreach ($branchData as $repairTypeId => $rowData) {
                $rtName = $rtMap[$repairTypeId] ?? $this->fetchRepairTypeName($repairTypeId);

                [$rtMat, $rtOtherMat, $rtSB, $rtOther, $rtTotal, $rtWH] =
                    $this->sumTotals([$repairTypeId => $rowData], $filter, $excludeWO, false);

                $row++;
                $sheet->setCellValue("B{$row}", $rtName);
                $this->writeSubtotalRow($sheet, $row, $rtMat, $rtSB, 0, $rtOther, $rtTotal, $rtWH, $rtOtherMat);
                $sheet->mergeCells("C{$row}:N{$row}");
                $sheet->getStyle("B{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("O{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("B{$row}:U{$row}")->getFont()->setBold(true);
                $sheet->getStyle("Q{$row}:U{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                foreach ($rowData as $r) {
                    if (in_array($r['REPAIR_NUMBER'], $excludeWO)) continue;
                    if ($filter !== 'ALL' && $r['TOTAL'] <= 0) continue;

                    $RN             = $r['REPAIR_NUMBER'];
                    $orderDate      = $r['ORDER_DATE'];
                    $problemSummary = $r['PROBLEM_SUMMARY'];
                    $deliveryDate   = $r['DELIVERY_DATE'];
                    $status         = $r['STATUS'];
                    $wipRemark      = $r['WIP_REMARK'] ?? '';

                    if (isset($AD[$RN])) {
                        $orderDate      = $AD[$RN]['CREATION_DATE'];
                        $problemSummary = $AD[$RN]['JOB_DESC'];
                        $deliveryDate   = $AD[$RN]['DELIVERY_DATE'];
                        $status         = $repairOrderStatus[$AD[$RN]['STATUS']] ?? 'Open';
                        $wipRemark      = $AD[$RN]['WIP_REMARK'];
                    } elseif (isset($QD[$RN])) {
                        $orderDate      = $QD[$RN]['CREATION_DATE'];
                        $problemSummary = $QD[$RN]['JOB_DESC'];
                        $status         = $repairOrderStatus[$QD[$RN]['STATUS']] ?? 'Open';
                        $wipRemark      = $QD[$RN]['WIP_REMARK'];
                    }

                    $custNo   = $r['CUSTOMER_NUMBER'];
                    $custName = $r['CUSTOMER_NAME'];
                    $contact  = $r['CONTACT_NAME'];
                    $item     = $r['ITEM'];
                    $serial   = $r['SERIAL_NUMBER'];
                    $srType   = $r['SERVICE_REQUEST_TYPE'];

                    if (isset($SR[$r['SERVICE_REQUEST_NO']])) {
                        $sd       = $SR[$r['SERVICE_REQUEST_NO']];
                        $custNo   = $sd->CustomerCode;
                        $custName = $sd->CustomerName;
                        $contact  = $sd->ContactName;
                        $item     = $sd->Item;
                        $serial   = $sd->ChassisNo;
                        $srType   = $sd->ServiceRequestType;
                    }

                    if (isset($QD[$RN])) {
                        $orderDate = $QD[$RN]['CREATION_DATE'];
                    }

                    $row++;
                    $sheet->setCellValue("C{$row}", $r['SERVICE_REQUEST_NO']);
                    $sheet->setCellValue("D{$row}", $RN);
                    $sheet->setCellValue("E{$row}", $orderDate);
                    $sheet->setCellValue("F{$row}", $r['OWNER']);
                    $sheet->setCellValue("G{$row}", $r['SALES_PERSON']);
                    $sheet->setCellValue("H{$row}", $custNo);
                    $sheet->setCellValue("I{$row}", $custName);
                    $sheet->setCellValue("J{$row}", $contact);
                    $sheet->setCellValue("K{$row}", $item);
                    $sheet->setCellValue("L{$row}", $serial);
                    $sheet->setCellValue("M{$row}", $srType);
                    $sheet->setCellValue("N{$row}", $problemSummary);
                    $sheet->setCellValue("O{$row}", $wipRemark);
                    $sheet->setCellValue("P{$row}", $status);
                    $sheet->setCellValue("Q{$row}", $r['MATERIAL']);
                    $sheet->setCellValue("R{$row}", $r['BOUGHTOUT']);
                    $sheet->setCellValue("S{$row}", 0);
                    $sheet->setCellValue("T{$row}", $r['OTHER']);
                    $sheet->setCellValue("U{$row}", $r['TOTAL']);
                    $sheet->setCellValue("V{$row}", $deliveryDate);
                    $sheet->setCellValue("W{$row}", number_format(WipReportDataService::convertToHm($r['WH']), 2));
                    $sheet->setCellValue("X{$row}", $r['OTHERMATERIAL']);
                    $sheet->setCellValue("Y{$row}", $r['PROMISE_DATE']);

                    $sheet->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("O{$row}:Y{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("Q{$row}:U{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $reportMaterial  += $r['MATERIAL'];
                    $reportOtherMat  += $r['OTHERMATERIAL'];
                    $reportSB        += $r['BOUGHTOUT'];
                    $reportOther     += $r['OTHER'];
                    $reportTotal     += $r['TOTAL'];
                    $reportWH        += $r['WH'];
                }
            }
        }

        // --- Grand total row ---
        $row++;
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $this->writeSubtotalRow($sheet, $row, $reportMaterial, $reportSB, 0, $reportOther, $reportTotal, (int) $reportWH, $reportOtherMat);
        $sheet->getStyle("A{$row}:Y{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:Y{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_DOUBLE);

        return $this->writeFile($spreadsheet);
    }

    private function writeSubtotalRow($sheet, int $row, float $mat, float $sb, float $labour, float $other, float $total, int $wh, float $otherMat): void
    {
        $sheet->setCellValue("Q{$row}", $mat);
        $sheet->setCellValue("R{$row}", $sb);
        $sheet->setCellValue("S{$row}", $labour);
        $sheet->setCellValue("T{$row}", $other);
        $sheet->setCellValue("U{$row}", $total);
        $sheet->setCellValue("W{$row}", number_format(WipReportDataService::convertToHm($wh), 2));
        $sheet->setCellValue("X{$row}", $otherMat);
    }

    private function sumTotals(array $branchData, string $filter, array $excludeWO, bool $nested): array
    {
        $mat = $otherMat = $sb = $other = $total = $wh = 0.0;

        $iterate = $nested
            ? array_merge(...array_values($branchData))
            : array_values($branchData)[0] ?? [];

        foreach ($iterate as $r) {
            if (in_array($r['REPAIR_NUMBER'], $excludeWO)) continue;
            if ($filter !== 'ALL' && $r['TOTAL'] <= 0) continue;

            $mat      += $r['MATERIAL'];
            $otherMat += $r['OTHERMATERIAL'];
            $sb       += $r['BOUGHTOUT'];
            $other    += $r['OTHER'];
            $total    += $r['TOTAL'];
            $wh       += $r['WH'];
        }

        return [$mat, $otherMat, $sb, $other, $total, (int) $wh];
    }

    private function fetchBranchName(int $id): string
    {
        $row = \Illuminate\Support\Facades\DB::table('deporepair.branches')->where('ID', $id)->first(['Branch_Name']);
        return $row->Branch_Name ?? '';
    }

    private function fetchRepairTypeName(int $id): string
    {
        $row = \Illuminate\Support\Facades\DB::table('deporepair.repair_order_types')->where('ID', $id)->first(['Service_Type']);
        return $row->Service_Type ?? 'Undefined';
    }

    private function writeFile(Spreadsheet $spreadsheet): string
    {
        $path = storage_path('app/reports/wip_report_' . now()->format('Ymd_His') . '.xlsx');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        (new Xlsx($spreadsheet))->save($path);
        return $path;
    }
}
