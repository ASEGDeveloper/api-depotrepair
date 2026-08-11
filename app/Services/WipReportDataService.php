<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WipReportDataService
{
    private array $excludeWorkOrders = ['7771332', '7792251'];

    public function buildReportData(array $filters): array
    {
        $branchId  = $filters['branchId'] ?? 'ASE_ALL';
        $endDate   = $filters['endDate'] ?? null;
        $filter    = $filters['filter'] ?? 'ALL';

        [$branchWh, $branchSr, $companyBranchId] = $this->resolveBranchConditions($branchId);
        [$filterWarehouse, $filterWarehouse2]     = $this->resolveWarehouseFilters($companyBranchId);

        $endWh = $endDate ? " AND CREATION_DATE <= '" . $endDate . " 23:59:59'" : '';
        $endOd = $endDate ? " AND a.CreationDate <= '" . $endDate . " 23:59:59'" : '';

        $endDateLabel    = $endDate ? date('d-M-Y', strtotime($endDate)) : date('d-M-Y');
        $reportCompanyName = $this->getCompanyName($companyBranchId);

        // --- Invoiced repair numbers (exclude these) ---
        $invoiced = DB::select("
            SELECT os.REPAIR_NUMBER
            FROM deporepair.oracle_sales os
            JOIN deporepair.sales_orders so ON so.Header_Id = os.HEADER_ID
            WHERE os.TRX_NUMBER != ''
              AND os.CREATION_DATE > '2022-01-01 00:00:00'
              AND os.REPAIR_NUMBER = so.Work_Order_No
            GROUP BY os.REPAIR_NUMBER
        ");
        $invoicedNos = array_column($invoiced, 'REPAIR_NUMBER');

        // --- Oracle sales base data ---
        $arraySo = DB::select("
            SELECT REPAIR_NUMBER, SERVICE_REQUEST_NO, ORIG_SYS_DOCUMENT_REF,
                   CUSTOMER_NUMBER, CUSTOMER_NAME, SERIAL_NUMBER, MODEL_NUMBER,
                   JOB_DESC, REPAIR_OWNER, CREATION_DATE, DELIVERY_DATE,
                   PRIMARY_SALESREP_NAME AS SALES_PERSON
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND REPAIR_NUMBER != ''
              AND REPAIR_ORDER_STATUS != 'C' {$branchWh} {$endWh}
              AND ID > 1065019 AND SERVICE_REQUEST_NO IS NOT NULL
            GROUP BY REPAIR_NUMBER, SERVICE_REQUEST_NO, ORIG_SYS_DOCUMENT_REF,
                     CUSTOMER_NUMBER, CUSTOMER_NAME, SERIAL_NUMBER, MODEL_NUMBER,
                     JOB_DESC, REPAIR_OWNER, CREATION_DATE, DELIVERY_DATE,
                     PRIMARY_SALESREP_NAME
        ");

        $array1 = DB::select("
            SELECT REPAIR_NUMBER, SERVICE_REQUEST_NO, BRANCH_ID, REPAIR_ORDER_TYPE_ID, REPAIR_OWNER
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND REPAIR_ORDER_STATUS != 'C'
              {$branchWh} {$endWh} AND ID > 1065019
              AND SERVICE_REQUEST_NO IS NOT NULL
            GROUP BY REPAIR_NUMBER, SERVICE_REQUEST_NO, BRANCH_ID, REPAIR_ORDER_TYPE_ID, REPAIR_OWNER
        ");

        // --- Material costs ---
        $array3 = DB::select("
            SELECT REPAIR_NUMBER,
                   SUM(CAST(ACTUAL_COST AS float) * CAST(ORDERED_QUANTITY AS float) * CAST(CURRENCY_CONVERSION_RATE AS float)) AS AMOUNT
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND REPAIR_ORDER_STATUS != 'C'
              AND FLOW_STATUS_CODE = 'CLOSED' AND MATERIAL_BILLABLE_FLAG = 'S'
              {$filterWarehouse} {$branchWh} AND ID > 1065019
              AND SERVICE_REQUEST_NO IS NOT NULL
            GROUP BY REPAIR_NUMBER
        ");

        $array3new = DB::select("
            SELECT REPAIR_NUMBER,
                   SUM(CAST(ACTUAL_COST AS float) * CAST(ORDERED_QUANTITY AS float) * CAST(CURRENCY_CONVERSION_RATE AS float)) AS AMOUNT
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND REPAIR_ORDER_STATUS != 'C'
              AND FLOW_STATUS_CODE = 'CLOSED' AND MATERIAL_BILLABLE_FLAG = 'S'
              {$filterWarehouse2} {$branchWh} AND ID > 1065019
              AND SERVICE_REQUEST_NO IS NOT NULL
            GROUP BY REPAIR_NUMBER
        ");

        $array5 = DB::select("
            SELECT REPAIR_NUMBER,
                   SUM(CAST(UNIT_SELLING_PRICE AS float) * CAST(ORDERED_QUANTITY AS float)) AS AMOUNT
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND SERVICE_REQUEST_NO IS NOT NULL
              AND REPAIR_ORDER_STATUS != 'C' AND FLOW_STATUS_CODE = 'CLOSED'
              AND MATERIAL_BILLABLE_FLAG = 'E' {$branchWh} AND ID > 1065019
            GROUP BY REPAIR_NUMBER
        ");

        $array6 = DB::select("
            SELECT REPAIR_NUMBER,
                   SUM(CAST(ACTUAL_COST AS float) * CAST(ORDERED_QUANTITY AS float) * CAST(CURRENCY_CONVERSION_RATE AS float)) AS AMOUNT
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND SERVICE_REQUEST_NO IS NOT NULL
              AND REPAIR_ORDER_STATUS != 'C' AND MATERIAL_BILLABLE_FLAG = 'M'
              AND (ORDERED_ITEM != 'BOUGHTOUT' OR ORDERED_ITEM != 'SUBLET')
              {$filterWarehouse} {$branchWh} AND ID > 1065019
            GROUP BY REPAIR_NUMBER
        ");

        $array6new = DB::select("
            SELECT REPAIR_NUMBER,
                   SUM(CAST(ACTUAL_COST AS float) * CAST(ORDERED_QUANTITY AS float) * CAST(CURRENCY_CONVERSION_RATE AS float)) AS AMOUNT
            FROM deporepair.oracle_sales
            WHERE TRX_NUMBER = '' AND SERVICE_REQUEST_NO IS NOT NULL
              AND REPAIR_ORDER_STATUS != 'C' AND MATERIAL_BILLABLE_FLAG = 'M'
              AND (ORDERED_ITEM != 'BOUGHTOUT' OR ORDERED_ITEM != 'SUBLET')
              {$filterWarehouse2} {$branchWh} AND ID > 1065019
            GROUP BY REPAIR_NUMBER
        ");

        // --- Sublet / Bought-out ---
        $arraySubBou = DB::select("
            SELECT Ref_No, Item_Code,
                   SUM((CAST(Unit_Price AS float) * CAST(Qty_Received AS float)) * CAST(Conversion_Rate AS float)) AS Amount
            FROM deporepair.po_sublet_alt
            WHERE CAST(Qty_Received AS float) > 0 AND Ref_No != ''
            GROUP BY Ref_No, Item_Code
        ");

        // --- Quotation repair orders (not already closed as actual) ---
        $baseQueryQuote = "
            SELECT a.ID, a.Work_Order_No AS REPAIR_NUMBER, a.Service_request_id AS SERVICE_REQUEST_NO,
                   a.Repair_Order_Status, a.Repair_Order_Desc, a.Repair_Order_Remark_Wip,
                   a.CreationDate, a.SalesPersonId, a.Repair_Order_Type_ID
            FROM deporepair.quotation_repair_orders AS a
            JOIN deporepair.service_request sr ON sr.ID = a.Service_request_id
            WHERE a.Repair_Order_Status != '3' AND a.Status != '100'
              {$branchSr} {$endOd}
        ";
        $baseArrayQuote = DB::select($baseQueryQuote);

        $baseArrayActualClosed = DB::select(
            "SELECT Quotation_ID FROM deporepair.actual_repair_orders WHERE Repair_Order_status = '3'"
        );
        $actualClosedQuoteIds = array_column($baseArrayActualClosed, 'Quotation_ID');

        $arrayQdRaw = [];
        $qids       = [];
        foreach ($baseArrayQuote as $r) {
            if (!in_array($r->ID, $actualClosedQuoteIds)) {
                $arrayQdRaw[$r->REPAIR_NUMBER] = $r;
                $qids[] = $r->ID;
            }
        }

        // --- Actual repair orders linked to open quotations ---
        $arrayAdRaw = [];
        if (!empty($qids)) {
            $qidsStr        = implode(',', $qids);
            $baseArrayActual = DB::select("
                SELECT a.Work_Order_No AS REPAIR_NUMBER, a.Service_request_id AS SERVICE_REQUEST_NO,
                       a.Repair_Order_Status, a.Repair_Order_Desc, a.Repair_Order_Remark_Wip,
                       a.CreationDate, a.Delivery_Date, a.SalesPersonId, a.Repair_Order_Type_ID
                FROM deporepair.actual_repair_orders a
                WHERE a.Repair_Order_Status != '3' AND a.Status != '100'
                  AND a.Quotation_ID IN ({$qidsStr})
            ");
            foreach ($baseArrayActual as $r) {
                $arrayAdRaw[$r->REPAIR_NUMBER] = $r;
            }
        }

        // --- TNA worked hours ---
        $acTnaRows = DB::select("
            SELECT a.Work_Order_No, SUM(DATEDIFF(MINUTE, t.SD, t.ED)) AS WorkedHour
            FROM deporepair.tna_entry t
            JOIN deporepair.actual_repair_order_jobs j ON j.Task_No = TRY_CAST(t.JOBCODE AS INT)
            JOIN deporepair.actual_repair_orders a ON a.ID = j.Repair_Order_ID
            WHERE t.ENDDATE != '1900-01-01 00:00:00'
            GROUP BY a.Work_Order_No
        ");
        $qcTnaRows = DB::select("
            SELECT a.Work_Order_No, SUM(DATEDIFF(MINUTE, t.SD, t.ED)) AS WorkedHour
            FROM deporepair.tna_entry t
            JOIN deporepair.quotation_repair_order_jobs j ON j.Task_No = TRY_CAST(t.JOBCODE AS INT)
            JOIN deporepair.quotation_repair_orders a ON a.ID = j.Repair_Order_ID
            WHERE t.ENDDATE != '1900-01-01 00:00:00'
            GROUP BY a.Work_Order_No
        ");
        $acTna = array_column($acTnaRows, 'WorkedHour', 'Work_Order_No');
        $qcTna = array_column($qcTnaRows, 'WorkedHour', 'Work_Order_No');

        // --- Excluded work orders ---
        $exRecRows  = DB::select("SELECT Work_Order_No FROM deporepair.wip_exclude_records ORDER BY ID ASC");
        $exRecArray = array_column($exRecRows, 'Work_Order_No');

        // --- Sales persons ---
        $spRows = DB::select("SELECT TOP 1 EmployeeName FROM deporepair.employee ORDER BY EmployeeID ASC");
        $defaultSp = $spRows[0]->EmployeeName ?? '';
        $spAllRows = DB::select("SELECT EmployeeID, EmployeeName FROM deporepair.employee");
        $spMap     = array_column($spAllRows, 'EmployeeName', 'EmployeeID');

        // --- Repair order statuses ---
        $roStatusRows = DB::select("SELECT ID, Status_Name FROM deporepair.base_status_service_order");
        $repairOrderStatus = array_column($roStatusRows, 'Status_Name', 'ID');

        // --- Build lookup maps ---
        $OD       = [];
        foreach ($arraySo as $v) { $OD[$v->REPAIR_NUMBER] = $v; }

        $MATERIAL  = array_column($array3,    'AMOUNT', 'REPAIR_NUMBER');
        $MATERIAL2 = array_column($array3new, 'AMOUNT', 'REPAIR_NUMBER');
        $EXPENSE   = array_column($array5,    'AMOUNT', 'REPAIR_NUMBER');
        $WS        = array_column($array6,    'AMOUNT', 'REPAIR_NUMBER');
        $WS2       = array_column($array6new, 'AMOUNT', 'REPAIR_NUMBER');

        $PO_SB = [];
        foreach ($arraySubBou as $v) {
            if ($v->Item_Code === 'SUBLET') {
                $PO_SB[$v->Ref_No]['SUBLET'] = $v->Amount;
            } else {
                $PO_SB[$v->Ref_No]['BOUGHTOUT'] = $v->Amount;
            }
        }

        $AD = [];
        foreach ($arrayAdRaw as $v) {
            $AD[$v->REPAIR_NUMBER] = [
                'SERVICE_REQUEST_NO'   => $v->SERVICE_REQUEST_NO,
                'CREATION_DATE'        => $this->fmtDate($v->CreationDate),
                'DELIVERY_DATE'        => $this->fmtDate($v->Delivery_Date),
                'JOB_DESC'             => $v->Repair_Order_Desc,
                'STATUS'               => $v->Repair_Order_Status,
                'SALES_PERSON'         => $v->SalesPersonId,
                'REPAIR_ORDER_TYPE_ID' => $v->Repair_Order_Type_ID,
                'WIP_REMARK'           => $v->Repair_Order_Remark_Wip,
            ];
        }

        $QD = [];
        foreach ($arrayQdRaw as $v) {
            $QD[$v->REPAIR_NUMBER] = [
                'SERVICE_REQUEST_NO'   => $v->SERVICE_REQUEST_NO,
                'CREATION_DATE'        => $this->fmtDate($v->CreationDate),
                'JOB_DESC'             => $v->Repair_Order_Desc,
                'STATUS'               => $v->Repair_Order_Status,
                'REPAIR_ORDER_TYPE_ID' => $v->Repair_Order_Type_ID,
                'WIP_REMARK'           => $v->Repair_Order_Remark_Wip,
                'SALES_PERSON'         => $v->SalesPersonId,
            ];
        }

        // --- Build rnData (unique repair numbers not invoiced, not excluded) ---
        $rn     = [];
        $sn     = [];
        $rnData = [];

        foreach ($array1 as $v) {
            if (!in_array($v->REPAIR_NUMBER, $invoicedNos) && !in_array($v->REPAIR_NUMBER, $exRecArray)) {
                $rn[]     = $v->REPAIR_NUMBER;
                $rnData[] = (array) $v;
                if (strlen((string) $v->SERVICE_REQUEST_NO) > 4 && !in_array($v->SERVICE_REQUEST_NO, $sn)) {
                    $sn[] = $v->SERVICE_REQUEST_NO;
                }
            }
        }

        foreach ($arrayAdRaw as $v) {
            if (!in_array($v->REPAIR_NUMBER, $invoicedNos) && !in_array($v->REPAIR_NUMBER, $exRecArray)) {
                if (!in_array($v->REPAIR_NUMBER, $rn)) {
                    $rn[]     = $v->REPAIR_NUMBER;
                    $rnData[] = (array) $v;
                }
                if (strlen((string) $v->SERVICE_REQUEST_NO) > 4 && !in_array($v->SERVICE_REQUEST_NO, $sn)) {
                    $sn[] = $v->SERVICE_REQUEST_NO;
                }
            }
        }

        foreach ($arrayQdRaw as $v) {
            if (!in_array($v->REPAIR_NUMBER, $invoicedNos) && !in_array($v->REPAIR_NUMBER, $exRecArray)) {
                if (!in_array($v->REPAIR_NUMBER, $rn)) {
                    $rn[]     = $v->REPAIR_NUMBER;
                    $rnData[] = (array) $v;
                }
                if (strlen((string) $v->SERVICE_REQUEST_NO) > 4 && !in_array($v->SERVICE_REQUEST_NO, $sn)) {
                    $sn[] = $v->SERVICE_REQUEST_NO;
                }
            }
        }

        // --- Load service requests ---
        $SR = [];
        if (!empty($sn)) {
            $validSn = array_filter($sn, fn($id) => is_numeric($id) && $id > 0);
            if (!empty($validSn)) {
                $srids = implode(',', array_map(fn($id) => "'" . (int) $id . "'", $validSn));
                $srRows = DB::select("
                    SELECT sr.ID, sr.Account_Number AS CustomerCode, sr.CustomerName,
                           sr.AltCustomerName AS ContactName, sr.Item, sr.ChassisNo,
                           sr.ContactPerson, sr.PromisedDate, sr.Branch_ID AS BRANCH_ID,
                           (SELECT Request_Type FROM deporepair.service_request_type WHERE ID = sr.Request_Type) AS ServiceRequestType,
                           (SELECT TOP(1) EmployeeName FROM deporepair.employee WHERE EmployeeID = sr.createdBy) AS Owner
                    FROM deporepair.service_request AS sr
                    WHERE CAST(sr.ID AS NVARCHAR) IN ({$srids})
                ");
                foreach ($srRows as $v) { $SR[$v->ID] = $v; }
            }
        }

        // --- Enrich rnData ---
        $enriched = [];
        foreach ($rnData as $v) {
            $RN = $v['REPAIR_NUMBER'];
            $WH = $acTna[$RN] ?? $qcTna[$RN] ?? 0;

            $row = $this->enrichRow($v, $RN, $OD, $AD, $QD, $SR, $spMap, $defaultSp, $repairOrderStatus, $WH);

            $BO  = ($PO_SB[$RN]['BOUGHTOUT'] ?? 0) + ($PO_SB[$RN]['SUBLET'] ?? 0);
            $MAT = ($WS[$RN] ?? 0) + ($MATERIAL[$RN] ?? 0);
            $MAT2 = ($WS2[$RN] ?? 0) + ($MATERIAL2[$RN] ?? 0);
            $OTHER = $EXPENSE[$RN] ?? 0;

            $row['BOUGHTOUT']     = $BO;
            $row['MATERIAL']      = $MAT;
            $row['OTHERMATERIAL'] = $MAT2;
            $row['LABOUR']        = 0;
            $row['OTHER']         = $OTHER;
            $row['TOTAL']         = $BO + $MAT + $MAT2 + $OTHER;
            $row['WH']            = $WH;

            $enriched[] = $row;
        }

        // --- Group by branch → repair type ---
        $dataSet = [];
        foreach ($enriched as $row) {
            $bid = $row['BRANCH_ID'] ?? 0;
            $rtid = $row['REPAIR_ORDER_TYPE_ID'] ?? 0;
            if ($bid > 0 && $rtid > 0) {
                $dataSet[$bid][$rtid][] = $row;
            }
        }

        // --- Branch / repair type name maps ---
        $branchRows = DB::select("SELECT ID, Branch_Name FROM deporepair.branches");
        $branchMap  = array_column($branchRows, 'Branch_Name', 'ID');

        $rtRows  = DB::select("SELECT ID, Service_Type FROM deporepair.repair_order_types");
        $rtMap   = array_column($rtRows, 'Service_Type', 'ID');

        return [
            'company_name'       => $reportCompanyName,
            'end_date_label'     => $endDateLabel,
            'filter'             => $filter,
            'dataset'            => $dataSet,
            'branch_map'         => $branchMap,
            'repair_type_map'    => $rtMap,
            'repair_order_status'=> $repairOrderStatus,
            'AD'                 => $AD,
            'QD'                 => $QD,
            'SR'                 => $SR,
            'exclude_work_orders'=> $this->excludeWorkOrders,
        ];
    }

    private function enrichRow(
        array $v, string $RN,
        array $OD, array $AD, array $QD, array $SR,
        array $spMap, string $defaultSp,
        array $repairOrderStatus, int $WH
    ): array {
        $base = [
            'BRANCH_ID'            => $v['BRANCH_ID'] ?? 0,
            'REPAIR_ORDER_TYPE_ID' => $v['REPAIR_ORDER_TYPE_ID'] ?? 0,
            'REPAIR_NUMBER'        => $RN,
            'SERVICE_REQUEST_NO'   => '',
            'ORDER_DATE'           => '',
            'OWNER'                => '',
            'SALES_PERSON'         => $defaultSp,
            'CUSTOMER_NUMBER'      => '',
            'CUSTOMER_NAME'        => '',
            'CONTACT_NAME'         => '',
            'ITEM'                 => '',
            'SERIAL_NUMBER'        => '',
            'SERVICE_REQUEST_TYPE' => '',
            'PROBLEM_SUMMARY'      => '',
            'STATUS'               => '',
            'DELIVERY_DATE'        => '',
            'PROMISE_DATE'         => '',
            'WIP_REMARK'           => '',
            'WH'                   => $WH,
        ];

        if (isset($OD[$RN])) {
            $SO  = $OD[$RN];
            $SRN = (strlen((string) $SO->SERVICE_REQUEST_NO) > 4) ? $SO->SERVICE_REQUEST_NO : $SO->ORIG_SYS_DOCUMENT_REF;

            $base['SERVICE_REQUEST_NO'] = $SRN;
            $base['ORDER_DATE']         = $this->fmtDate($SO->CREATION_DATE);
            $base['OWNER']              = $SO->REPAIR_OWNER ?? '';
            $base['SALES_PERSON']       = $SO->SALES_PERSON ?? $defaultSp;
            $base['CUSTOMER_NUMBER']    = $SO->CUSTOMER_NUMBER;
            $base['CUSTOMER_NAME']      = $SO->CUSTOMER_NAME;
            $base['ITEM']               = $SO->MODEL_NUMBER;
            $base['SERIAL_NUMBER']      = $SO->SERIAL_NUMBER;
            $base['PROBLEM_SUMMARY']    = $SO->JOB_DESC;
            $base['STATUS']             = 'Open';
            $base['DELIVERY_DATE']      = $this->fmtDate($SO->DELIVERY_DATE);

            if (isset($SR[$SRN])) {
                $SD = $SR[$SRN];
                $base['CONTACT_NAME']         = $SD->ContactName;
                $base['SERVICE_REQUEST_TYPE'] = $SD->ServiceRequestType;
                $base['PROMISE_DATE']         = $this->fmtDate($SD->PromisedDate);
                if (empty($base['OWNER']))         $base['OWNER']         = $SD->Owner;
                if (empty($base['ITEM']))          $base['ITEM']          = $SD->Item;
                if (empty($base['SERIAL_NUMBER'])) $base['SERIAL_NUMBER'] = $SD->ChassisNo;
            }
            return $base;
        }

        $alt = $AD[$RN] ?? $QD[$RN] ?? null;
        if ($alt) {
            $SRN = (strlen((string) $alt['SERVICE_REQUEST_NO']) > 4) ? $alt['SERVICE_REQUEST_NO'] : '';
            $base['SERVICE_REQUEST_NO']   = $SRN;
            $base['ORDER_DATE']           = $alt['CREATION_DATE'];
            $base['PROBLEM_SUMMARY']      = $alt['JOB_DESC'];
            $base['STATUS']               = $repairOrderStatus[$alt['STATUS']] ?? 'Open';
            $base['DELIVERY_DATE']        = $alt['DELIVERY_DATE'] ?? '';
            $base['WIP_REMARK']           = $alt['WIP_REMARK'] ?? '';
            $base['REPAIR_ORDER_TYPE_ID'] = $alt['REPAIR_ORDER_TYPE_ID'];
            $base['SALES_PERSON']         = $spMap[$alt['SALES_PERSON']] ?? $defaultSp;

            if (isset($SR[$SRN])) {
                $SD = $SR[$SRN];
                $base['BRANCH_ID']            = $SD->BRANCH_ID;
                $base['CUSTOMER_NUMBER']      = $SD->CustomerCode;
                $base['CUSTOMER_NAME']        = $SD->CustomerName;
                $base['CONTACT_NAME']         = $SD->ContactName;
                $base['OWNER']                = $SD->Owner;
                $base['ITEM']                 = $SD->Item;
                $base['SERIAL_NUMBER']        = $SD->ChassisNo;
                $base['SERVICE_REQUEST_TYPE'] = $SD->ServiceRequestType;
                $base['PROMISE_DATE']         = $this->fmtDate($SD->PromisedDate);
            }
            return $base;
        }

        // Unresolved
        $SRN = (strlen((string) ($v['SERVICE_REQUEST_NO'] ?? '')) > 4) ? $v['SERVICE_REQUEST_NO'] : '';
        $base['SERVICE_REQUEST_NO'] = 'UN_' . $SRN;
        return $base;
    }

    private function resolveBranchConditions(string $branchId): array
    {
        return match ($branchId) {
            'ASE_ALL'  => [" AND TRY_CAST(BRANCH_ID AS int) < '26'",  " AND sr.Branch_ID < '26'",  ''],
            'ASAMI_ALL'=> [" AND TRY_CAST(BRANCH_ID AS int) IN (26,27,28,29)", " AND sr.Branch_ID IN (26,27,28,29)", 26],
            'ASM_ALL'  => [" AND TRY_CAST(BRANCH_ID AS int) IN (30,31)", " AND sr.Branch_ID IN (30,31)", 30],
            'AST_ALL'  => [" AND TRY_CAST(BRANCH_ID AS int) > '31'",  " AND sr.Branch_ID > '31'",  32],
            default    => [
                " AND TRY_CAST(BRANCH_ID AS int) = '" . (int) $branchId . "'",
                " AND sr.Branch_ID = '" . (int) $branchId . "'",
                (int) $branchId,
            ],
        };
    }

    private function resolveWarehouseFilters(mixed $companyBranchId): array
    {
        $unitCompany = $this->getUnitCompany($companyBranchId);

        $wh1 = match ($unitCompany) {
            'ASI' => " AND (WAREHOUSE='ASP' OR WAREHOUSE='SPA')",
            'ASM' => " AND (WAREHOUSE='ME3' OR WAREHOUSE='ME4')",
            'AST' => " AND WAREHOUSE IN ('SIE','SME','SPT')",
            default => " AND WAREHOUSE='ASG'",
        };
        $wh2 = match ($unitCompany) {
            'ASI' => " AND WAREHOUSE NOT IN ('ASP','SPA')",
            'ASM' => " AND WAREHOUSE NOT IN ('ME3','ME4')",
            'AST' => " AND WAREHOUSE NOT IN ('SIE','SME','SPT')",
            default => " AND WAREHOUSE != 'ASG'",
        };

        return [$wh1, $wh2];
    }

    private function getUnitCompany(mixed $branchId): string
    {
        if (empty($branchId)) return 'ASE';
        $row = DB::table('deporepair.company')
            ->join('deporepair.branches', 'branches.ORG_ID', '=', 'company.Org_ID')
            ->where('branches.ID', $branchId)
            ->first(['company.UnitCompanyName']);
        return $row->UnitCompanyName ?? 'ASE';
    }

    private function getCompanyName(mixed $branchId): string
    {
        if (empty($branchId)) return 'AL SHIRAWI ENTERPRISES L.L.C.';
        $row = DB::table('deporepair.company')
            ->join('deporepair.branches', 'branches.ORG_ID', '=', 'company.Org_ID')
            ->where('branches.ID', $branchId)
            ->first(['company.Company_Name']);
        return $row->Company_Name ?? 'AL SHIRAWI ENTERPRISES L.L.C.';
    }

    private function fmtDate(?string $date): string
    {
        if (empty($date) || $date === '1900-01-01 00:00:00') return '';
        try {
            return date('d-M-Y', strtotime($date));
        } catch (\Throwable) {
            return '';
        }
    }

    public static function convertToHm(int $minutes): float
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return (float) ($h . '.' . str_pad($m, 2, '0', STR_PAD_LEFT));
    }
}
