<?php

declare(strict_types=1);

use App\Actions\Expense\ApproveExpenseRequestAction;
use App\Actions\Expense\DisburseExpenseAction;
use App\Actions\Expense\SubmitExpenseRequestAction;
use App\Enums\ExpenseStatus;
use App\Enums\LedgerType;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('follows multi-stage approval flow from submit to disbursement', function () {
    $unit = Unit::where('code', 'SDIT')->first();
    $category = ExpenseCategory::first();
    $cashAccount = CashAccount::where('unit_id', $unit->id)->first();
    $initialBalance = $cashAccount->current_balance;

    $admin = User::where('email', 'admin.sdit@sit.sch.id')->first();
    $kepsek = User::where('email', 'kepsek.sdit@sit.sch.id')->first();
    $bendahara = User::where('email', 'bendahara@sit.sch.id')->first();

    $submitAction = app(SubmitExpenseRequestAction::class);
    $approveAction = app(ApproveExpenseRequestAction::class);
    $disburseAction = app(DisburseExpenseAction::class);

    // 1. Admin submits expense
    $expense = $submitAction->execute(
        unitId: $unit->id,
        expenseCategoryId: $category->id,
        title: 'Pengadaan ATK Ujian Semester',
        requestedAmount: 1500000,
        requestedByUserId: $admin->id
    );

    expect($expense->status)->toBe(ExpenseStatus::SUBMITTED);

    // 2. Principal approves Level 1
    $approveAction->approveByPrincipal($expense, $kepsek->id);
    expect($expense->fresh()->status)->toBe(ExpenseStatus::APPROVED_PRINCIPAL);

    // 3. Treasurer approves Level 2 with adjustment
    $approveAction->approveByTreasurer($expense, approvedAmount: 1400000, cashAccountId: $cashAccount->id, approverUserId: $bendahara->id);
    expect($expense->fresh()->status)->toBe(ExpenseStatus::APPROVED_TREASURER)
        ->and($expense->fresh()->approved_amount)->toBe(1400000);

    // 4. Disburse funds
    $disbursed = $disburseAction->execute($expense, cashAccountId: $cashAccount->id, disbursedByUserId: $bendahara->id);
    expect($disbursed->status)->toBe(ExpenseStatus::DISBURSED)
        ->and($cashAccount->fresh()->current_balance)->toBe($initialBalance - 1400000);

    // Verify CashLedger
    $ledger = CashLedger::where('reference_type', ExpenseRequest::class)
        ->where('reference_id', $expense->id)
        ->first();

    expect($ledger)->not->toBeNull()
        ->and($ledger->type)->toBe(LedgerType::OUT)
        ->and($ledger->amount)->toBe(1400000);
});
