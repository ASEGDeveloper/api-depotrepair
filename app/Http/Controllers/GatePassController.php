<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\GatePassApprovedMail;
use App\Mail\GatePassRejectedMail;
use App\Models\GatePass;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class GatePassController extends Controller
{
    use ApiResponse;

    public function approveGatePass($approvalId, Request $request)
    {
        try {
            $approvalId = (int) $approvalId;
            $comment    = $request->input('comment');

            DB::statement("
                UPDATE deporepair.GatePass_Approvals
                SET Action = 'APPROVED',
                    Comments = ?,
                    ActionAt = GETDATE()
                WHERE GatePassID = ?
            ", [$comment, $approvalId]);

            $approval = DB::selectOne("
                SELECT GatePassID FROM deporepair.GatePass_Approvals WHERE GatePassID = ?
            ", [$approvalId]);

            $gatePassId = $approval->GatePassID ?? 0;

            if ($gatePassId) {
                DB::statement("
                    UPDATE deporepair.gate_pass
                    SET status = 'APPROVED', updated_date = GETDATE()
                    WHERE id = ?
                ", [(int) $gatePassId]);

                $this->addLog($gatePassId, 'APPROVED', 'Approved by manager');

                $gatePass = DB::selectOne("SELECT * FROM deporepair.gate_pass WHERE id = ?", [(int) $gatePassId]);
                $items    = $this->getGatePassItems($gatePassId);

                Mail::to('haridwar.yadav@servoedge.com')
                    ->send(new GatePassApprovedMail($gatePass, $items, $comment));
            }

            return $this->successResponse(null, 'Gate pass approved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to approve gate pass', 500, $e->getMessage());
        }
    }

    public function rejectGatePass($approvalId, Request $request)
    {
        try {
            $approvalId = (int) $approvalId;
            $comment    = $request->input('comment');

            DB::statement("
                UPDATE deporepair.GatePass_Approvals
                SET Action = 'Rejected',
                    Comments = ?,
                    ActionAt = GETDATE()
                WHERE GatePassID = ?
            ", [$comment, $approvalId]);

            $approval = DB::selectOne("
                SELECT GatePassID FROM deporepair.GatePass_Approvals WHERE GatePassID = ?
            ", [$approvalId]);

            $gatePassId = $approval->GatePassID ?? 0;

            if ($gatePassId) {
                DB::statement("
                    UPDATE deporepair.gate_pass
                    SET status = 'REJECTED', updated_date = GETDATE()
                    WHERE id = ?
                ", [(int) $gatePassId]);

                $this->addLog($gatePassId, 'REJECTED', 'Rejected by approver');

                $gatePass = DB::selectOne("SELECT * FROM deporepair.gate_pass WHERE id = ?", [(int) $gatePassId]);
                $items    = $this->getGatePassItems($gatePassId);

                Mail::to('haridwar.yadav@servoedge.com')
                    ->send(new GatePassRejectedMail($gatePass, $items, $comment));
            }

            return $this->successResponse(null, 'Gate pass rejected successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to reject gate pass', 500, $e->getMessage());
        }
    }

    private function addLog($gatePassId, $action, $comment)
    {
        DB::statement("
            INSERT INTO deporepair.gate_pass_logs (gate_pass_id, action, comment, created_date)
            VALUES (?, ?, ?, GETDATE())
        ", [(int) $gatePassId, $action, $comment]);
    }

    public function getByGatePassNo($gatePassNo)
    {
        try {
            $gatePass = DB::selectOne("
                SELECT gp.id, gp.gate_pass_no, gp.wo_number, gp.customer_name, gp.customer_number,
                       gp.site, gp.department AS department_id, b.Branch_Name as department_name, gp.business_unit,
                       gp.vehicle_registration_number, gp.remarks, gp.technician_name, gp.technician_email,
                       gp.status, gp.created_by, gp.created_date, gp.updated_by, gp.updated_date,gp.driver_name,gp.driver_mobile_no
                FROM deporepair.gate_pass gp
                LEFT JOIN deporepair.branches b ON gp.department = b.id
                WHERE gp.gate_pass_no = ?
                  AND gp.status = 'PENDING_APPROVAL'
            ", [$gatePassNo]);

            if (!$gatePass) {
                return $this->failedResponse('Gate pass not found', 404);
            }

            $items = $this->getGatePassItems($gatePass->id);

            $data = (array) $gatePass;
            $data['items'] = $items;

            return $this->successResponse($data, 'Gate pass fetched successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch gate pass', 500, $e->getMessage());
        }
    }


    // public function getByGatePassNo($gatePassNo)
    // {
    //     try {
    //         $gatePass = DB::selectOne("
    //             SELECT gp.id, gp.gate_pass_no, gp.wo_number, gp.customer_name, gp.customer_number,
    //                    gp.site, gp.department AS department_id, d.department_name, gp.business_unit,
    //                    gp.vehicle_registration_number, gp.remarks, gp.technician_name, gp.technician_email,
    //                    gp.status, gp.created_by, gp.created_date, gp.updated_by, gp.updated_date,gp.driver_name,gp.driver_mobile_no
    //             FROM deporepair.gate_pass gp
    //             LEFT JOIN deporepair.departments d ON gp.department = d.id
    //             WHERE gp.gate_pass_no = ?
    //               AND gp.status = 'PENDING_APPROVAL'
    //         ", [$gatePassNo]);

    //         if (!$gatePass) {
    //             return $this->failedResponse('Gate pass not found', 404);
    //         }

    //         $items = $this->getGatePassItems($gatePass->id);

    //         $data = (array) $gatePass;
    //         $data['items'] = $items;

    //         return $this->successResponse($data, 'Gate pass fetched successfully');
    //     } catch (\Throwable $e) {
    //         return $this->errorResponse('Failed to fetch gate pass', 500, $e->getMessage());
    //     }
    // }

    public function getSecurityStats(Request $request)
    {
        try {
            $wid = intval($request->user()->Branch_ID);

            $pending = DB::selectOne("
                SELECT COUNT(*) as cnt FROM deporepair.gate_pass gp
                WHERE gp.status IN ('QUANTITY_ISSUED')
                  AND (gp.security_status IS NULL OR gp.security_status = 'PENDING')
                  AND gp.workshop_location = ?                 
            ", [$wid])->cnt ?? 0;

            $verified = DB::selectOne("
                SELECT COUNT(*) as cnt FROM deporepair.gate_pass gp
                WHERE gp.security_status = 'VERIFIED'
                  AND gp.workshop_location = ?
                  AND CAST(gp.security_verified_date AS DATE) = CAST(GETDATE() AS DATE)
            ", [$wid])->cnt ?? 0;

            $rejected = DB::selectOne("
                SELECT COUNT(*) as cnt FROM deporepair.gate_pass gp
                WHERE gp.security_status = 'REJECTED'
                  AND gp.workshop_location = ?
                  AND CAST(gp.security_verified_date AS DATE) = CAST(GETDATE() AS DATE)
            ", [$wid])->cnt ?? 0;

            $pendingReturn = DB::selectOne("
                SELECT COUNT(DISTINCT gp.id) as cnt FROM deporepair.gate_pass gp
                INNER JOIN deporepair.gate_pass_items gpi ON gp.id = gpi.gate_pass_id
                WHERE gp.status IN ('SECURITY_CLEARED')
                  AND gp.workshop_location = ?
                  AND UPPER(gpi.item_type) = 'RETURNABLE'
            ", [$wid])->cnt ?? 0;

            $total = DB::selectOne("
                SELECT COUNT(*) as cnt FROM deporepair.gate_pass
                WHERE CAST(created_date AS DATE) = CAST(GETDATE() AS DATE)
            ")->cnt ?? 0;

            return $this->successResponse([
                'pending'        => $pending,
                'verified'       => $verified,
                'rejected'       => $rejected,
                'pending_return' => $pendingReturn,
                'total_today'    => $total,
            ], 'Security stats fetched successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch security stats', 500, $e->getMessage());
        }
    }

    public function getPendingSecurityChecks(Request $request)
    {
        try {
            $workshopId = intval($request->user()->Branch_ID);

            $results = DB::select("
                SELECT gp.id, gp.gate_pass_no, gp.wo_number, gp.customer_name, gp.customer_number, gp.site,
                       gp.department AS department_id, b.Branch_Name as department_name, gp.business_unit,
                       gp.vehicle_registration_number, gp.remarks, gp.status, gp.created_by, gp.created_date,
                       gp.security_status, gp.security_verified_by, gp.security_verified_date,
                       gp.technician_name, gp.technician_email, gp.technician_contact_no,
                       gp.pass_type, gp.driver_name, gp.driver_mobile_no, gp.supplier_name, gp.supplier_mobile
                FROM deporepair.gate_pass gp
                LEFT JOIN deporepair.branches b ON gp.department = b.id
                WHERE gp.status IN ('QUANTITY_ISSUED', 'SHORTAGE_APPROVED')
                  AND (gp.security_status IS NULL OR gp.security_status = 'PENDING')
                  AND gp.workshop_location = ?
                ORDER BY gp.id DESC
            ", [$workshopId]);

            return $this->successResponse($results, 'Pending security checks fetched successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch pending security checks', 500, $e->getMessage());
        }
    }

    public function getPendingReturnableChecks(Request $request)
    {
        try {
           $workshopId = intval($request->user()->Branch_ID);

            $results = DB::select("
                SELECT DISTINCT gp.id, gp.gate_pass_no, gp.wo_number, gp.customer_name, gp.site,
                       gp.technician_name, gp.technician_email, gp.security_verified_date,
                       gp.vehicle_registration_number,
                       gp.pass_type, gp.driver_name, gp.driver_mobile_no, gp.supplier_name, gp.supplier_mobile
                FROM deporepair.gate_pass gp
                INNER JOIN deporepair.gate_pass_items gpi ON gp.id = gpi.gate_pass_id
                WHERE gp.status = 'SECURITY_CLEARED'
                  AND UPPER(gpi.item_type) = 'RETURNABLE'
                  AND gp.workshop_location = ?
                ORDER BY gp.id DESC
            ", [$workshopId]);

            return $this->successResponse($results, 'Pending returnable checks fetched successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch pending returnable checks', 500, $e->getMessage());
        }
    }

    private function getGatePassItems($gatePassId)
    {
        return DB::select("
        SELECT 
            gpi.id,
            gpi.gate_pass_id,
            gpi.item_id,
            gpi.item_name,
            gpi.uom,
            gpi.requested_qty,
            gpi.issued_qty,
            gpi.item_type,
            COALESCE(NULLIF(gpi.item_image, ''), mg.item_image) AS item_image,
            mg.item_code,
            gpi.is_returned,
            gpi.returned_at,
            gpi.returned_by
        FROM deporepair.gate_pass_items AS gpi
        LEFT JOIN deporepair.mg_item_master AS mg 
            ON gpi.item_id = mg.id
        WHERE gpi.gate_pass_id = ?
    ", [(int) $gatePassId]);
    }
}
