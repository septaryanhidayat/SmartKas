<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Models\ExpenseRequest;
use App\Models\Unit;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubmitExpenseRequestAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Create and submit a new expense request.
     */
    public function execute(
        int $unitId,
        int $expenseCategoryId,
        string $title,
        int $requestedAmount,
        ExpenseType $expenseType = ExpenseType::OPERATIONAL,
        ?string $description = null,
        ?Carbon $transactionDate = null,
        ?string $attachmentPath = null,
        ?int $cashAccountId = null,
        bool $submitImmediately = true,
        ?int $requestedByUserId = null
    ): ExpenseRequest {
        $date = $transactionDate ?? Carbon::now();
        $unit = Unit::findOrFail($unitId);
        $creatorId = $requestedByUserId ?? auth()->id() ?? 1;

        $this->closingPeriodService->ensurePeriodIsOpen($unitId, $date);

        return DB::transaction(function () use (
            $unit,
            $expenseCategoryId,
            $cashAccountId,
            $title,
            $description,
            $expenseType,
            $requestedAmount,
            $date,
            $attachmentPath,
            $submitImmediately,
            $creatorId
        ) {
            $prefix = sprintf('EXP/%s/%s', $unit->code->value, $date->format('Ym'));
            $sequence = ExpenseRequest::query()
                ->withoutGlobalScope('unit_scope')
                ->where('request_number', 'like', "{$prefix}%")
                ->count() + 1;
            $requestNumber = sprintf('%s/%04d', $prefix, $sequence);

            return ExpenseRequest::create([
                'unit_id' => $unit->id,
                'expense_category_id' => $expenseCategoryId,
                'cash_account_id' => $cashAccountId,
                'request_number' => $requestNumber,
                'title' => $title,
                'description' => $description,
                'expense_type' => $expenseType,
                'requested_amount' => $requestedAmount,
                'approved_amount' => $submitImmediately ? $requestedAmount : null,
                'transaction_date' => $date->toDateString(),
                'status' => $submitImmediately ? ExpenseStatus::SUBMITTED : ExpenseStatus::DRAFT,
                'attachment_path' => $attachmentPath,
                'requested_by' => $creatorId,
            ]);
        });
    }
}
