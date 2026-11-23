<?php

declare(strict_types=1);

use App\Actions\Accounting\CloseMonthlyPeriodAction;
use App\Actions\Payment\RecordStudentPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\PeriodClosedException;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Unit;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('blocks financial transactions when period is closed and unlocks when reopened', function () {
    $unit = Unit::where('code', 'SDIT')->first();
    $closingAction = app(CloseMonthlyPeriodAction::class);
    $paymentAction = app(RecordStudentPaymentAction::class);

    // Close July 2026
    $closingAction->close(year: 2026, month: 7, unitId: $unit->id);

    $student = Student::where('unit_id', $unit->id)->first();
    $cashAccount = CashAccount::where('unit_id', $unit->id)->first();

    // Attempt payment in closed period should throw PeriodClosedException
    expect(fn () => $paymentAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => 1, 'amount' => 100000]],
        paymentDate: Carbon::create(2026, 7, 15)
    ))->toThrow(PeriodClosedException::class);

    // Reopen period
    $closingAction->reopen(year: 2026, month: 7, unitId: $unit->id, reason: 'Koreksi input audit');

    // Create an invoice for July 2026 to test payment after reopen
    $invoice = Invoice::create([
        'unit_id' => $unit->id,
        'student_id' => $student->id,
        'fee_component_id' => $unit->feeComponents()->first()->id,
        'academic_year_id' => 1,
        'invoice_number' => 'INV/TEST/202607/9999',
        'billing_month' => 7,
        'billing_year' => 2026,
        'due_date' => '2026-07-10',
        'gross_amount' => 100000,
        'discount_amount' => 0,
        'net_amount' => 100000,
        'paid_amount' => 0,
        'status' => InvoiceStatus::UNPAID,
        'created_by' => 1,
    ]);

    // Payment should now succeed
    $payment = $paymentAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => $invoice->id, 'amount' => 100000]],
        paymentDate: Carbon::create(2026, 7, 15)
    );

    expect($payment)->not->toBeNull()
        ->and($payment->total_amount)->toBe(100000);
});
