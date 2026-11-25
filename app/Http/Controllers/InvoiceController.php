<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Billing\GenerateIncidentalInvoicesAction;
use App\Actions\Billing\GenerateMonthlyInvoicesAction;
use App\Models\AcademicYear;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Unit;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $status = $request->query('status');
        $search = $request->query('search');

        $units = Unit::where('is_active', true)->where('level', '!=', 'foundation')->get();

        $query = Invoice::with(['unit', 'student.classroom', 'feeComponent'])
            ->orderBy('due_date', 'desc')
            ->orderBy('id', 'desc');

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices', 'units', 'unitId', 'status', 'search'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        $units = Unit::where('is_active', true)
            ->where('level', '!=', 'foundation')
            ->when($unitId, fn ($q) => $q->where('id', $unitId))
            ->orderBy('id')
            ->get();
        $academicYears = AcademicYear::where('is_active', true)->get();

        $feeComponentsQuery = FeeComponent::where('is_active', true);
        if ($unitId) {
            $feeComponentsQuery->where('unit_id', $unitId);
        }
        $feeComponents = $feeComponentsQuery->get();

        $studentsQuery = Student::where('status', 'active');
        if ($unitId) {
            $studentsQuery->where('unit_id', $unitId);
        }
        $students = $studentsQuery->get();

        return view('invoices.create', compact('units', 'academicYears', 'feeComponents', 'students', 'unitId'));
    }

    public function generateMonthly(Request $request, GenerateMonthlyInvoicesAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'due_date' => ['nullable', 'date'],
        ]);

        abort_unless(auth()->user()->canAccessUnit((int) $validated['unit_id']), 403, 'Anda tidak memiliki akses untuk unit sekolah tersebut.');

        $dueDate = ! empty($validated['due_date']) ? Carbon::parse($validated['due_date']) : null;

        try {
            $result = $action->execute(
                unitId: (int) $validated['unit_id'],
                academicYearId: (int) $validated['academic_year_id'],
                month: (int) $validated['month'],
                year: (int) $validated['year'],
                dueDate: $dueDate,
                createdByUserId: auth()->id()
            );

            return redirect()->route('invoices.index')->with(
                'success',
                "Tagihan bulanan berhasil digenerate! {$result['created_count']} tagihan baru dibuat, {$result['skipped_count']} dilewati (duplikat)."
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function generateIncidental(Request $request, GenerateIncidentalInvoicesAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'fee_component_id' => ['required', 'exists:fee_components,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'custom_amount' => ['nullable', 'integer', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $component = FeeComponent::findOrFail((int) $validated['fee_component_id']);
        abort_unless(auth()->user()->canAccessUnit((int) $component->unit_id), 403, 'Anda tidak memiliki akses untuk komponen biaya unit tersebut.');

        $dueDate = ! empty($validated['due_date']) ? Carbon::parse($validated['due_date']) : null;

        try {
            $created = $action->execute(
                feeComponentId: (int) $validated['fee_component_id'],
                studentIds: array_map('intval', $validated['student_ids']),
                customAmount: ! empty($validated['custom_amount']) ? (int) $validated['custom_amount'] : null,
                dueDate: $dueDate,
                notes: $validated['notes'] ?? null,
                createdByUserId: auth()->id()
            );

            return redirect()->route('invoices.index')->with(
                'success',
                "Berhasil membuat {$created->count()} tagihan insidentil untuk siswa terpilih."
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function downloadPdf(Invoice $invoice, InvoicePdfService $pdfService): Response
    {
        abort_unless(auth()->user()->canAccessUnit((int) $invoice->unit_id), 403);

        return $pdfService->download($invoice);
    }
}
