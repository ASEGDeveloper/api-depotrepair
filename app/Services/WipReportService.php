<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WipReportService
{
    public function getWipData(array $filters): array
    {
        $branchId  = $filters['branchId'] ?? 'ASE_ALL';
        $endDate   = $filters['endDate'] ?? null;
        $filter    = $filters['filter'] ?? 'ALL';

        $query = DB::table('deporepair.service_request as sr')
            ->leftJoin('deporepair.branches as b', 'sr.Branch_ID', '=', 'b.ID')
            ->leftJoin('deporepair.base_status_service_request as st', 'sr.StatusId', '=', 'st.Status_ID')
            ->leftJoin('deporepair.repair_order as ro', 'sr.ID', '=', 'ro.ServiceRequestId')
            ->select([
                'sr.ID as service_request_id',
                'sr.Job_Number as job_number',
                'sr.CustomerName as customer_name',
                'sr.Account_Number as account_number',
                'sr.Install_Base_ID as install_base_id',
                'sr.Vehicle_Registeration_Number as vehicle_reg_no',
                'sr.ChassisNo as chassis_no',
                'sr.Item as item',
                'sr.Driver_Name as driver_name',
                'sr.Mobile_Number as mobile_number',
                'sr.CreatedAt as created_at',
                'sr.JobCardCreatedDate as job_card_created_date',
                'sr.VehicleReportedDate as vehicle_reported_date',
                'sr.PromisedDate as promised_date',
                'sr.EstimatedAmt as estimated_amt',
                'sr.UnitCompanyName as unit_company_name',
                'sr.UserName as user_name',
                'b.Branch_Name as branch_name',
                'st.Status_Name as status_name',
                DB::raw('ISNULL(ro.TotalAmount, 0) as repair_order_amount'),
                DB::raw('ro.WorkOrderNo as work_order_no'),
            ]);

        // Branch filter
        $this->applyBranchFilter($query, $branchId);

        // End date filter: job cards created on or before end date
        if ($endDate) {
            $query->whereDate('sr.JobCardCreatedDate', '<=', $endDate);
        }

        // Exclude closed/completed statuses — WIP means still in progress
        $query->whereNotIn('sr.StatusId', $this->getClosedStatusIds());

        // Value filter
        if ($filter === 'Zero') {
            // Non-zero value job cards only
            $query->where(DB::raw('ISNULL(ro.TotalAmount, 0)'), '>', 0);
        }
        // 'ALL' includes both zero and non-zero

        $rows = $query->orderBy('sr.JobCardCreatedDate', 'asc')->get();

        return $rows->map(fn($r) => [
            'service_request_id'    => $r->service_request_id,
            'job_number'            => $r->job_number,
            'customer_name'         => $r->customer_name,
            'account_number'        => $r->account_number,
            'install_base_id'       => $r->install_base_id,
            'vehicle_reg_no'        => $r->vehicle_reg_no,
            'chassis_no'            => $r->chassis_no,
            'item'                  => $r->item,
            'driver_name'           => $r->driver_name,
            'mobile_number'         => $r->mobile_number,
            'created_at'            => $r->created_at,
            'job_card_created_date' => $r->job_card_created_date,
            'vehicle_reported_date' => $r->vehicle_reported_date,
            'promised_date'         => $r->promised_date,
            'estimated_amt'         => $r->estimated_amt,
            'unit_company_name'     => $r->unit_company_name,
            'user_name'             => $r->user_name,
            'branch_name'           => $r->branch_name,
            'status_name'           => $r->status_name,
            'repair_order_amount'   => $r->repair_order_amount,
            'work_order_no'         => $r->work_order_no,
        ])->values()->all();
    }

    public function getBranches(): array
    {
        return DB::table('deporepair.branches')
            ->orderBy('Branch_Name')
            ->get(['ID', 'Branch_Name'])
            ->map(fn($r) => ['id' => $r->ID, 'name' => $r->Branch_Name])
            ->values()->all();
    }

    private function applyBranchFilter($query, string $branchId): void
    {
        switch ($branchId) {
            case 'ASE_ALL':
                $query->where('sr.UnitCompanyName', 'ASE');
                break;
            case 'ASAMI_ALL':
                $query->where('sr.UnitCompanyName', 'ASAMI');
                break;
            case 'ASM_ALL':
                $query->where('sr.UnitCompanyName', 'ASM');
                break;
            case 'AST_ALL':
                $query->where('sr.UnitCompanyName', 'AST');
                break;
            default:
                if (is_numeric($branchId)) {
                    $query->where('sr.Branch_ID', (int) $branchId);
                }
                break;
        }
    }

    // Status IDs that mean the job card is closed/delivered — not WIP
    private function getClosedStatusIds(): array
    {
        return DB::table('deporepair.base_status_service_request')
            ->whereIn(DB::raw('LOWER(Status_Name)'), ['closed', 'delivered', 'completed', 'cancelled'])
            ->pluck('Status_ID')
            ->all();
    }
}
