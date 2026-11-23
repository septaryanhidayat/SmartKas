<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateIncidentalInvoicesAction
{
    /**
     * Generate incidental or annual invoices for specified students.
     *
     * @param  array<int>  $studentIds
     * @return Collection<int, Invoice>
     */
    public function execute(
        int $feeComponentId,
        array $studentIds,
        ?int $customAmount = null,
        ?Carbon $dueDate = null,
        ?string $notes = null,
        ?int $createdByUserId = null
    ): Collection {
        $component = FeeComponent::with('unit')->findOrFail($feeComponentId);
        $unit = $component->unit;
        $due = $dueDate ?? Carbon::now()->addDays(14);
        $creatorId = $createdByUserId ?? auth()->id() ?? 1;

        $students = Student::query()
            ->withoutGlobalScope('unit_scope')
            ->whereIn('id', $studentIds)
            ->where('unit_id', $unit->id)
            ->get();

        $createdInvoices = collect();

        DB::transaction(function () use (
            $component,
            $unit,
            $students,
            $customAmount,
            $due,
            $notes,
            $creatorId,
            &$createdInvoices
        ) {
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            foreach ($students as $student) {
                $grossAmount = $customAmount ?? (int) $component->default_amount;
                $discountAmount = $student->calculateDiscountFor($component);
                $netAmount = max(0, $grossAmount - $discountAmount);

                $prefix = sprintf('INV/%s/%04d%02d', $unit->code->value, $year, $month);
                $sequence = Invoice::query()
                    ->withoutGlobalScope('unit_scope')
                    ->where('invoice_number', 'like', "{$prefix}%")
                    ->count() + 1;
                $invoiceNumber = sprintf('%s/%04d', $prefix, $sequence);

                $invoice = Invoice::create([
                    'unit_id' => $unit->id,
                    'student_id' => $student->id,
                    'fee_component_id' => $component->id,
                    'academic_year_id' => $component->academic_year_id,
                    'invoice_number' => $invoiceNumber,
                    'billing_month' => null,
                    'billing_year' => $year,
                    'due_date' => $due->toDateString(),
                    'gross_amount' => $grossAmount,
                    'discount_amount' => $discountAmount,
                    'net_amount' => $netAmount,
                    'paid_amount' => 0,
                    'status' => $netAmount === 0 ? InvoiceStatus::PAID : InvoiceStatus::UNPAID,
                    'notes' => $notes ?? "Tagihan {$component->name}",
                    'created_by' => $creatorId,
                ]);

                $createdInvoices->push($invoice);
            }
        });

        return $createdInvoices;
    }
}
