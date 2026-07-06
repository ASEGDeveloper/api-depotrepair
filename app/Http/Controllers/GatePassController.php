<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\GatePassApprovedMail;
use App\Mail\GatePassRejectedMail;
use App\Mail\GatePassReturnedMail;
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






    // private function addLog($gatePassId, $action, $comment)
    // {
    //     DB::statement("INSERT INTO deporepair.gate_pass_logs (gate_pass_id, action, comment, created_date)
    //         VALUES (?, ?, ?, GETDATE())
    //     ", [(int) $gatePassId, $action, $comment]);
    // }

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
                //'total_today'    => $total,
            ], 'Security stats fetched successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch security stats', 500, $e->getMessage());
        }
    }

    public function getPendingSecurityChecks(Request $request)
    {
        try {
            $workshopId = intval($request->user()->Branch_ID);

            $page    = max(1, (int) $request->input('page', 1));
            $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
            $offset  = ($page - 1) * $perPage;
            $search  = trim((string) $request->input('search', ''));

            $searchSql = '';
            $searchParams = [];
            if ($search !== '') {
                $searchSql = " AND (
                    gp.technician_name LIKE ?
                    OR gp.customer_name LIKE ?
                    OR gp.gate_pass_no LIKE ?
                    OR gp.vehicle_registration_number LIKE ?
                )";
                $like = '%' . $search . '%';
                $searchParams = [$like, $like, $like, $like];
            }

            $total = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM deporepair.gate_pass gp
                WHERE gp.status IN ('QUANTITY_ISSUED', 'SHORTAGE_APPROVED')
                  AND (gp.security_status IS NULL OR gp.security_status = 'PENDING')
                  AND gp.workshop_location = ?
                  $searchSql
            ", array_merge([$workshopId], $searchParams))->total;

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
                  $searchSql
                ORDER BY gp.id DESC
                OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
            ", array_merge([$workshopId], $searchParams, [$offset, $perPage]));

            foreach ($results as $gatePass) {
                if (empty($gatePass->pass_type)) {
                    $gatePass->pass_type = 'General';
                }

                if (strtoupper($gatePass->pass_type) !== 'BOUGHTOUT') {
                    unset($gatePass->supplier_name, $gatePass->supplier_mobile);
                }

                $gatePass->items = $this->getGatePassItems($gatePass->id);
            }

            return $this->successResponse($results, 'Pending security checks fetched successfully', 200, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch pending security checks', 500, $e->getMessage());
        }
    }


    public function getPendingReturnableChecks(Request $request)
    {
        try {
            $workshopId = intval($request->user()->Branch_ID);

            $page    = max(1, (int) $request->input('page', 1));
            $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
            $offset  = ($page - 1) * $perPage;
            $search  = trim((string) $request->input('search', ''));

            $searchSql = '';
            $searchParams = [];
            if ($search !== '') {
                $searchSql = " AND (
                    gp.technician_name LIKE ?
                    OR gp.customer_name LIKE ?
                    OR gp.gate_pass_no LIKE ?
                    OR gp.vehicle_registration_number LIKE ?
                )";
                $like = '%' . $search . '%';
                $searchParams = [$like, $like, $like, $like];
            }

            $total = DB::selectOne("
                SELECT COUNT(DISTINCT gp.id) AS total
                FROM deporepair.gate_pass gp
                INNER JOIN deporepair.gate_pass_items gpi ON gp.id = gpi.gate_pass_id
                WHERE gp.status = 'SECURITY_CLEARED'
                  AND UPPER(gpi.item_type) = 'RETURNABLE'
                  AND (gpi.is_returned IS NULL OR gpi.is_returned = 0)
                  AND gp.workshop_location = ?
                  $searchSql
            ", array_merge([$workshopId], $searchParams))->total;

            $results = DB::select("
                SELECT DISTINCT gp.id, gp.gate_pass_no, gp.wo_number, gp.customer_name, gp.site,
                       gp.technician_name, gp.technician_email, gp.security_verified_date,
                       gp.vehicle_registration_number,
                       gp.pass_type, gp.driver_name, gp.driver_mobile_no, gp.supplier_name, gp.supplier_mobile
                FROM deporepair.gate_pass gp
                INNER JOIN deporepair.gate_pass_items gpi ON gp.id = gpi.gate_pass_id
                WHERE gp.status = 'SECURITY_CLEARED'
                  AND UPPER(gpi.item_type) = 'RETURNABLE'
                  AND (gpi.is_returned IS NULL OR gpi.is_returned = 0)
                  AND gp.workshop_location = ?
                  $searchSql
                ORDER BY gp.id DESC
                OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
            ", array_merge([$workshopId], $searchParams, [$offset, $perPage]));

            foreach ($results as $gatePass) {
                if (empty($gatePass->pass_type)) {
                    $gatePass->pass_type = 'General';
                }

                if (strtoupper($gatePass->pass_type) !== 'BOUGHTOUT') {
                    unset($gatePass->supplier_name, $gatePass->supplier_mobile);
                }

                $items = $this->getGatePassItems($gatePass->id);
                $gatePass->items = array_values(array_filter($items, function ($item) {
                    return strtoupper(trim($item->item_type ?? '')) === 'RETURNABLE'
                        && empty($item->is_returned);
                }));
            }

            return $this->successResponse($results, 'Pending returnable checks fetched successfully', 200, [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch pending returnable checks', 500, $e->getMessage());
        }
    }

    public function verifyGatePass(Request $request)
    {
        try {

            $gatePassId = trim($request->input('gate_pass_id'));
            $status     = strtoupper(trim($request->input('status', '')));
            $employeeId = intval($request->user()->EmployeeID);
            $remarks    = trim($request->input('remarks', ''));

            // return $this->failedResponse($gatePassId, 403);

            if (!$gatePassId || !in_array($status, ['VERIFIED', 'REJECTED'])) {
                return $this->failedResponse('Invalid parameters', 422);
            }

            if ($status === 'REJECTED' && $remarks === '') {
                return $this->failedResponse('Remarks are required when rejecting a gate pass', 422);
            }

            $mainStatus = $status === 'VERIFIED' ? 'SECURITY_CLEARED' : 'SECURITY_REJECTED';

            DB::update("
    UPDATE deporepair.gate_pass
    SET security_status = ?,
        security_verified_by = ?,
        security_verified_date = GETDATE(),
        security_remarks = ?,
        status = ?,
        updated_by = ?,
        updated_date = GETDATE()
    WHERE gate_pass_no = ?
", [
                $status,
                $employeeId,
                $remarks ?: null,
                $mainStatus,
                $employeeId,
                $gatePassId
            ]);

            $emp = DB::table('deporepair.employee')
                ->select('EmployeeName')
                ->where('EmployeeID', $employeeId)
                ->first();
            $employeeLabel = $emp->EmployeeName ?? $employeeId;

            $logMsg = 'Security ' . $status . ' by ' . $employeeLabel;
            if ($remarks !== '') {
                $logMsg .= '. Remarks: ' . $remarks;
            }

            // return $logMsg.$gatePassId;

            $id = DB::table('deporepair.gate_pass')
               // ->where('gate_pass_no', $gatePassId)
               ->where('gate_pass_no', $gatePassId)
                ->value('id'); // primary key



            $this->addLog($id, $mainStatus, $logMsg);

            $msg = $status === 'VERIFIED' ? 'Gate pass verified and exit allowed' : 'Gate pass rejected';
            return $this->successResponse(null, $msg);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to verify gate pass', 500, $e->getMessage());
        }
    }

    public function returnItem(Request $request)
    {
        try {
            $id  = (int) $request->input('id');
            $gatePassId = (int) $request->input('gate_pass_id');
            $employeeId = intval($request->user()->EmployeeID);
            $returnQty  = $request->input('qty');


            if (!$id || !$gatePassId) {
                return $this->failedResponse('Invalid parameters', 422);
            }

            $gp = DB::table('deporepair.gate_pass')
                ->select([
                    'id as gatepass_id',
                    'gate_pass_no',
                    'wo_number',
                    'customer_name',
                    'site',
                    'technician_name',
                    'technician_email',
                    'security_verified_date',
                    'vehicle_registration_number',
                    'pass_type',
                    'driver_name',
                    'driver_mobile_no',
                    'supplier_name',
                    'supplier_mobile',
                    'status'
                ])
                ->where('id', $gatePassId)
                ->first();

                

            if (!$gp) {
                return $this->failedResponse('Gate pass not found', 404);
            }

           

           
            if (empty($gp->pass_type)) {
                $gp->pass_type = 'General';
            }

            if (strtoupper($gp->pass_type) !== 'BOUGHTOUT') {
                unset($gp->supplier_name, $gp->supplier_mobile);
            }

            $gpStatus = strtoupper(trim($gp->status ?? ''));

           // $eligibleStatuses = ['SECURITY_CLEARED', 'QUANTITY_ISSUED', 'SHORTAGE_APPROVED', 'APPROVED'];
           $eligibleStatuses = ['SECURITY_CLEARED'];


            

            if (!in_array($gpStatus, $eligibleStatuses)) {
                return $this->failedResponse('Gate pass not eligible for return marking', 422);
            } 

            $item = DB::table('deporepair.gate_pass_items')
                ->where('id', $id)
                ->first();

            if (!$item) {
                return $this->failedResponse('Gate pass item not found', 404);
            }

            $targetQty = (float) ($item->requested_qty ?? 0);
            $alreadyReturned = (float) ($item->returned_qty ?? 0);

            $qtyToAdd = $returnQty !== null ? (float) $returnQty : ($targetQty - $alreadyReturned);
            if ($qtyToAdd < 0) {
                $qtyToAdd = 0;
            }

            $newReturnedQty = $alreadyReturned + $qtyToAdd;

            if ($newReturnedQty > $targetQty) {
                return $this->failedResponse(
                    'Return quantity exceeds requested quantity. Remaining: ' . ($targetQty - $alreadyReturned),
                    422
                );
            }
            $itemFullyReturned = $targetQty > 0 && $newReturnedQty >= $targetQty;

            DB::table('deporepair.gate_pass_items')
                ->where('id', $id)
                ->update(array_merge([
                    'returned_qty' => $newReturnedQty,
                ], $itemFullyReturned ? [
                    'is_returned' => 1,
                    'returned_at' => DB::raw('GETDATE()'),
                    'returned_by' => $employeeId,
                ] : []));

            $pendingCount = DB::table('deporepair.gate_pass_items')
                ->where('gate_pass_id', $gatePassId)
                ->whereRaw("UPPER(item_type) = 'RETURNABLE'")
                ->where(function ($q) {
                    $q->whereNull('is_returned')->orWhere('is_returned', 0);
                })
                ->count();

            $allReturned = ($pendingCount === 0);
 

            if ($allReturned) {
                DB::statement("
                    UPDATE deporepair.gate_pass
                    SET status = 'CLOSED', updated_by = ?, updated_date = GETDATE()
                    WHERE id = ?
                ", [$employeeId, $gatePassId]);

                $this->addLog($gatePassId, 'CLOSED', 'All returnable items returned — gate pass closed');

                $allItems = $this->getGatePassItems($gatePassId);
                $returnableItems = array_values(array_filter($allItems, function ($i) {
                    return strtoupper(trim($i->item_type ?? '')) === 'RETURNABLE';
                }));

                $recipients = array_filter([$gp->technician_email ?? null, 'haridwar.yadav@servoedge.com']);
                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new GatePassReturnedMail($gp, $returnableItems));
                }

                return $this->successResponse(
                    array_merge((array) $gp, ['all_returned' => true]),
                    'All items returned — gate pass closed'
                );
            }

            return $this->successResponse(
                array_merge((array) $gp, [
                    'all_returned'   => false,
                    'item_returned'  => $itemFullyReturned,
                    'returned_qty'   => $newReturnedQty,
                    'pending_qty'    => max($targetQty - $newReturnedQty, 0),
                ]),
                $itemFullyReturned ? 'Item fully returned' : 'Partial quantity returned'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to mark item as returned', 500, $e->getMessage());
        }
    }

    private function addLog($gatePassId, $action, $comment)
    {
        DB::transaction(function () use ($gatePassId, $action, $comment) {
            DB::table('deporepair.gate_pass_logs')->insert([
                'gate_pass_id' => (int) $gatePassId,
                'action'       => $action,
                'comment'      => $comment,
                'created_date' => now(),
            ]);
        });
    }


    private function getGatePassItems($gatePassId)
    {
        return DB::select("
        SELECT 
            gpi.id,
            gpi.gate_pass_id,
            gpi.item_id,
            gpi.uom,
            gpi.requested_qty,
            gpi.issued_qty,
            gpi.item_type,
            COALESCE(NULLIF(gpi.item_image, ''), mg.item_image) AS item_image,
            mg.item_code,
            gpi.is_returned,
            gpi.returned_qty
        FROM deporepair.gate_pass_items AS gpi
        LEFT JOIN deporepair.mg_item_master AS mg 
            ON gpi.item_id = mg.id
        WHERE gpi.gate_pass_id = ?
    ", [(int) $gatePassId]);
    }
}
