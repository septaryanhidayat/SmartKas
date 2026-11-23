<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Enums\ExpenseStatus;
use App\Models\ExpenseRequest;
use Carbon\Carbon;
use DomainException;
use InvalidArgumentException;

class ApproveExpenseRequestAction
{
    /**
     * Approve or reject an expense request at Level 1 (Principal) or Level 2 (Treasurer).
     */
    public function approveByPrincipal(ExpenseRequest $request, ?int $approverUserId = null): ExpenseRequest
    {
        if ($request->status !== ExpenseStatus::SUBMITTED) {
            throw new DomainException("Hanya pengajuan berstatus 'Diajukan' yang dapat disetujui Kepala Sekolah.");
        }

        $userId = $approverUserId ?? auth()->id() ?? 1;

        $request->update([
            'status' => ExpenseStatus::APPROVED_PRINCIPAL,
            'principal_approved_by' => $userId,
            'principal_approved_at' => Carbon::now(),
        ]);

        return $request;
    }

    /**
     * Final approval by Treasurer.
     */
    public function approveByTreasurer(
        ExpenseRequest $request,
        ?int $approvedAmount = null,
        ?int $cashAccountId = null,
        ?int $approverUserId = null
    ): ExpenseRequest {
        if ($request->status !== ExpenseStatus::APPROVED_PRINCIPAL && $request->status !== ExpenseStatus::SUBMITTED) {
            throw new DomainException("Pengajuan belum disetujui Kepala Sekolah atau status tidak valid.");
        }

        $userId = $approverUserId ?? auth()->id() ?? 1;
        $finalAmount = $approvedAmount ?? $request->requested_amount;

        $updateData = [
            'status' => ExpenseStatus::APPROVED_TREASURER,
            'approved_amount' => $finalAmount,
            'treasurer_approved_by' => $userId,
            'treasurer_approved_at' => Carbon::now(),
        ];

        if ($cashAccountId) {
            $updateData['cash_account_id'] = $cashAccountId;
        }

        $request->update($updateData);

        return $request;
    }

    /**
     * Reject an expense request with a mandatory reason.
     */
    public function reject(ExpenseRequest $request, string $reason): ExpenseRequest
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan penolakan pengajuan dana wajib diisi.');
        }

        if (in_array($request->status, [ExpenseStatus::DISBURSED, ExpenseStatus::VOID])) {
            throw new DomainException('Pengajuan yang sudah dicairkan atau dibatalkan tidak dapat ditolak.');
        }

        $request->update([
            'status' => ExpenseStatus::REJECTED,
            'rejection_reason' => $reason,
        ]);

        return $request;
    }
}
