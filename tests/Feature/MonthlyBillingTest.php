<?php

declare(strict_types=1);

use App\Actions\Billing\GenerateMonthlyInvoicesAction;
use App\Enums\InvoiceStatus;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Unit;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('generates monthly billing invoices for active students with discount calculation', function () {
    $action = app(GenerateMonthlyInvoicesAction::class);
    $unit = Unit::where('code', 'SDIT')->first();
    $academicYear = AcademicYear::getActive();

    $result = $action->execute(
        unitId: $unit->id,
        academicYearId: $academicYear->id,
        month: 7,
        year: 2026
    );

    expect($result['created_count'])->toBeGreaterThan(0);

    // Verify discount calculation for student with full scholarship (Bilqis Adzra Nafisah)
    $scholarshipStudent = $unit->students()->where('name', 'Bilqis Adzra Nafisah')->first();
    $invoice = Invoice::where('student_id', $scholarshipStudent->id)
        ->where('billing_month', 7)
        ->where('billing_year', 2026)
        ->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->discount_amount)->toBe($invoice->gross_amount)
        ->and($invoice->net_amount)->toBe(0)
        ->and($invoice->status)->toBe(InvoiceStatus::PAID);
});

it('prevents duplicate monthly invoices on repeated runs', function () {
    $action = app(GenerateMonthlyInvoicesAction::class);
    $unit = Unit::where('code', 'TKIT')->first();
    $academicYear = AcademicYear::getActive();

    $firstRun = $action->execute($unit->id, $academicYear->id, 8, 2026);
    expect($firstRun['created_count'])->toBeGreaterThan(0);

    // Second run should be skipped
    $secondRun = $action->execute($unit->id, $academicYear->id, 8, 2026);
    expect($secondRun['created_count'])->toBe(0)
        ->and($secondRun['skipped_count'])->toBe($firstRun['created_count']);
});
