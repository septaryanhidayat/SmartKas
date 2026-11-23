<?php

declare(strict_types=1);

use App\Actions\Billing\GenerateMonthlyInvoicesAction;
use App\Actions\Payment\RecordStudentPaymentAction;
use App\Actions\Payment\VoidStudentPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\LedgerType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\AcademicYear;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Unit;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('supports partial installment payment and full payment', function () {
    $unit = Unit::where('code', 'SDIT')->first();
    $academicYear = AcademicYear::getActive();

    // Generate billing
    app(GenerateMonthlyInvoicesAction::class)->execute($unit->id, $academicYear->id, 7, 2026);

    $student = Student::where('unit_id', $unit->id)->where('name', 'Fathir Zaidan Al-Ayyubi')->first();
    $invoice = Invoice::where('student_id', $student->id)->where('status', InvoiceStatus::UNPAID)->first();
    $cashAccount = CashAccount::where('unit_id', $unit->id)->first();
    $initialBalance = $cashAccount->current_balance;

    $recordAction = app(RecordStudentPaymentAction::class);

    // 1. First partial payment: 50%
    $halfAmount = (int) ($invoice->net_amount / 2);
    $payment1 = $recordAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => $invoice->id, 'amount' => $halfAmount]]
    );

    expect($payment1->status)->toBe(PaymentStatus::COMPLETED)
        ->and($invoice->fresh()->status)->toBe(InvoiceStatus::PARTIAL)
        ->and($invoice->fresh()->paid_amount)->toBe($halfAmount)
        ->and($cashAccount->fresh()->current_balance)->toBe($initialBalance + $halfAmount);

    // 2. Second payment: remaining 50% to complete/lunas
    $remaining = $invoice->fresh()->remaining_amount;
    $payment2 = $recordAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::TRANSFER,
        items: [['invoice_id' => $invoice->id, 'amount' => $remaining]]
    );

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::PAID)
        ->and($invoice->fresh()->remaining_amount)->toBe(0)
        ->and($cashAccount->fresh()->current_balance)->toBe($initialBalance + $invoice->net_amount);
});

it('prevents overpayment above invoice remaining balance', function () {
    $unit = Unit::where('code', 'SMPIT')->first();
    $academicYear = AcademicYear::getActive();

    app(GenerateMonthlyInvoicesAction::class)->execute($unit->id, $academicYear->id, 7, 2026);

    $student = Student::where('unit_id', $unit->id)->first();
    $invoice = Invoice::where('student_id', $student->id)->first();
    $cashAccount = CashAccount::where('unit_id', $unit->id)->first();

    $recordAction = app(RecordStudentPaymentAction::class);

    expect(fn () => $recordAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => $invoice->id, 'amount' => $invoice->net_amount + 50000]]
    ))->toThrow(DomainException::class);
});

it('reverses payment, restores invoice, and logs cash outflow when voided', function () {
    $unit = Unit::where('code', 'SDIT')->first();
    $academicYear = AcademicYear::getActive();

    app(GenerateMonthlyInvoicesAction::class)->execute($unit->id, $academicYear->id, 7, 2026);

    $student = Student::where('unit_id', $unit->id)->where('name', 'Fathir Zaidan Al-Ayyubi')->first();
    $invoice = Invoice::where('student_id', $student->id)->where('status', InvoiceStatus::UNPAID)->first();
    $cashAccount = CashAccount::where('unit_id', $unit->id)->first();
    $initialBalance = $cashAccount->current_balance;

    $recordAction = app(RecordStudentPaymentAction::class);
    $voidAction = app(VoidStudentPaymentAction::class);

    // Create payment
    $payment = $recordAction->execute(
        studentId: $student->id,
        cashAccountId: $cashAccount->id,
        paymentMethod: PaymentMethod::CASH,
        items: [['invoice_id' => $invoice->id, 'amount' => $invoice->net_amount]]
    );

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::PAID);

    // Void payment
    $voidedPayment = $voidAction->execute($payment->id, 'Kwitansi dibatalkan karena salah siswa');

    expect($voidedPayment->status)->toBe(PaymentStatus::VOID)
        ->and($voidedPayment->void_reason)->toBe('Kwitansi dibatalkan karena salah siswa')
        ->and($invoice->fresh()->status)->toBe(InvoiceStatus::UNPAID)
        ->and($invoice->fresh()->paid_amount)->toBe(0)
        ->and($cashAccount->fresh()->current_balance)->toBe($initialBalance);

    // Verify CashLedger reversal entry
    $reversalLedger = CashLedger::where('reference_type', Payment::class)
        ->where('reference_id', $payment->id)
        ->where('type', LedgerType::OUT)
        ->first();

    expect($reversalLedger)->not->toBeNull()
        ->and($reversalLedger->amount)->toBe($payment->total_amount);
});
