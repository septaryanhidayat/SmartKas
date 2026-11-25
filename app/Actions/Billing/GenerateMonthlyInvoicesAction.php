<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\BillingType;
use App\Enums\InvoiceStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Unit;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyInvoicesAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Generate monthly recurring billing invoices for active students.
     *
     * @return array{created_count: int, skipped_count: int}
     */
    public function execute(
        int $unitId,
        int $academicYearId,
        int $month,
        int $year,
        ?Carbon $dueDate = null,
        ?int $createdByUserId = null
    ): array {
        $unit = Unit::findOrFail($unitId);
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $due = $dueDate ?? Carbon::create($year, $month, 10);
        $creatorId = $createdByUserId ?? auth()->id() ?? 1;

        $this->closingPeriodService->ensurePeriodIsOpen($unitId, Carbon::create($year, $month, 1));

        // Fetch all active monthly components for this unit and academic year
        $monthlyComponents = FeeComponent::query()
            ->withoutGlobalScope('unit_scope')
            ->where('unit_id', $unitId)
            ->where('academic_year_id', $academicYearId)
            ->where('billing_type', BillingType::MONTHLY)
            ->where('is_active', true)
            ->get();

        if ($monthlyComponents->isEmpty()) {
            return ['created_count' => 0, 'skipped_count' => 0];
        }

        // Fetch all active students in this unit
        $students = Student::query()
            ->withoutGlobalScope('unit_scope')
            ->where('unit_id', $unitId)
            ->where('status', StudentStatus::ACTIVE)
            ->get();

        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $unit,
            $academicYear,
            $students,
            $monthlyComponents,
            $month,
            $year,
            $due,
            $creatorId,
            &$createdCount,
            &$skippedCount
        ) {
            foreach ($students as $student) {
                foreach ($monthlyComponents as $component) {
                    // Check if invoice already exists for this student, component, month, year
                    $exists = Invoice::query()
                        ->withoutGlobalScope('unit_scope')
                        ->where('student_id', $student->id)
                        ->where('fee_component_id', $component->id)
                        ->where('billing_month', $month)
                        ->where('billing_year', $year)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;

                        continue;
                    }

                    $grossAmount = (int) $component->default_amount;
                    $discountAmount = $student->calculateDiscountFor($component, Carbon::create($year, $month, 1));
                    $netAmount = max(0, $grossAmount - $discountAmount);

                    // Generate unique invoice number: INV/{UNIT}/{YYYYMM}/{SEQ}
                    $prefix = sprintf('INV/%s/%04d%02d', $unit->code->value, $year, $month);
                    $sequence = Invoice::query()
                        ->withoutGlobalScope('unit_scope')
                        ->where('invoice_number', 'like', "{$prefix}%")
                        ->count() + 1;
                    $invoiceNumber = sprintf('%s/%04d', $prefix, $sequence);

                    Invoice::create([
                        'unit_id' => $unit->id,
                        'student_id' => $student->id,
                        'fee_component_id' => $component->id,
                        'academic_year_id' => $academicYear->id,
                        'invoice_number' => $invoiceNumber,
                        'billing_month' => $month,
                        'billing_year' => $year,
                        'due_date' => $due->toDateString(),
                        'gross_amount' => $grossAmount,
                        'discount_amount' => $discountAmount,
                        'net_amount' => $netAmount,
                        'paid_amount' => 0,
                        'status' => $netAmount === 0 ? InvoiceStatus::PAID : InvoiceStatus::UNPAID,
                        'notes' => "Tagihan {$component->name} Bulan {$month}/{$year}",
                        'created_by' => $creatorId,
                    ]);

                    $createdCount++;
                }
            }
        });

        return ['created_count' => $createdCount, 'skipped_count' => $skippedCount];
    }
}
