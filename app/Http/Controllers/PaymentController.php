<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Payment\RecordStudentPaymentAction;
use App\Actions\Payment\VoidStudentPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Unit;
use App\Services\ReceiptPdfService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $search = $request->query('search');

        $units = Unit::where('is_active', true)->where('level', '!=', 'foundation')->get();

        $query = Payment::with(['unit', 'student.classroom', 'cashAccount', 'creator'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc');

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('payments.index', compact('payments', 'units', 'unitId', 'search'));
    }

    /**
     * Cashier / Point-of-Sale UI for Student Billing Payments.
     */
    public function cashier(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $studentId = $request->query('student_id') ? (int) $request->query('student_id') : null;

        $units = Unit::where('is_active', true)->where('level', '!=', 'foundation')->get();

        $studentsQuery = Student::where('status', 'active');
        if ($unitId) {
            $studentsQuery->where('unit_id', $unitId);
        }
        $students = $studentsQuery->orderBy('name', 'asc')->get();

        $selectedStudent = null;
        $unpaidInvoices = collect();

        if ($studentId) {
            $selectedStudent = Student::with(['unit', 'classroom', 'studentDiscounts.discount'])->find($studentId);
            if ($selectedStudent) {
                $unpaidInvoices = Invoice::with(['feeComponent'])
                    ->where('student_id', $selectedStudent->id)
                    ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])
                    ->orderBy('due_date', 'asc')
                    ->get();
            }
        }

        $cashAccountsQuery = CashAccount::where('is_active', true);
        if ($unitId) {
            $cashAccountsQuery->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }
        $cashAccounts = $cashAccountsQuery->get();

        return view('payments.cashier', compact('units', 'students', 'unitId', 'selectedStudent', 'unpaidInvoices', 'cashAccounts'));
    }

    /**
     * Store payment transaction from cashier.
     */
    public function store(Request $request, RecordStudentPaymentAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'payment_method' => ['required', 'string'],
            'payment_date' => ['nullable', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_id' => ['required', 'exists:invoices,id'],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ]);

        $cashAccount = CashAccount::findOrFail((int) $validated['cash_account_id']);
        $student = Student::findOrFail((int) $validated['student_id']);
        abort_unless(auth()->user()->canAccessUnit((int) $student->unit_id), 403);
        if ($cashAccount->unit_id && ! auth()->user()->canAccessUnit((int) $cashAccount->unit_id)) {
            abort(403, 'Akun kas bukan milik unit Anda.');
        }

        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $paymentDate = ! empty($validated['payment_date']) ? Carbon::parse($validated['payment_date']) : Carbon::now();

        try {
            $payment = $action->execute(
                studentId: (int) $validated['student_id'],
                cashAccountId: (int) $validated['cash_account_id'],
                paymentMethod: $paymentMethod,
                items: $validated['items'],
                paymentDate: $paymentDate,
                referenceNumber: $validated['reference_number'] ?? null,
                createdByUserId: auth()->id()
            );

            return redirect()->route('payments.show', $payment)->with(
                'success',
                "Pembayaran berhasil disimpan dengan No. Kwitansi: {$payment->receipt_number}!"
            );
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Payment $payment): View
    {
        $payment->load([
            'unit',
            'student.classroom',
            'cashAccount',
            'creator',
            'voidedBy',
            'items.invoice.feeComponent',
        ]);

        return view('payments.show', compact('payment'));
    }

    public function downloadPdf(Payment $payment, ReceiptPdfService $pdfService): Response
    {
        return $pdfService->download($payment);
    }

    public function voidPayment(Request $request, Payment $payment, VoidStudentPaymentAction $action): RedirectResponse
    {
        abort_unless(auth()->user()->can('void-payments'), 403, 'Anda tidak memiliki hak akses untuk membatalkan kwitansi.');

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            $action->execute(
                paymentId: $payment->id,
                voidReason: $validated['void_reason'],
                voidedByUserId: auth()->id()
            );

            return redirect()->route('payments.show', $payment)->with(
                'success',
                "Kwitansi {$payment->receipt_number} berhasil dibatalkan (void). Saldo kas dan tagihan telah dipulihkan."
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
