<?php

declare(strict_types=1);

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClosingPeriodController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Students Directory
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');

    // Invoices & Billing
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/generate', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices/generate-monthly', [InvoiceController::class, 'generateMonthly'])->name('invoices.generate-monthly');
    Route::post('/invoices/generate-incidental', [InvoiceController::class, 'generateIncidental'])->name('invoices.generate-incidental');

    // Cashier & Payments
    Route::get('/cashier', [PaymentController::class, 'cashier'])->name('payments.cashier');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/download', [PaymentController::class, 'downloadPdf'])->name('receipts.download');
    Route::post('/payments/{payment}/void', [PaymentController::class, 'voidPayment'])->name('payments.void');

    // Expenses & Approvals
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::post('/expenses/{expense}/approve-principal', [ExpenseController::class, 'approvePrincipal'])->name('expenses.approve-principal');
    Route::post('/expenses/{expense}/approve-treasurer', [ExpenseController::class, 'approveTreasurer'])->name('expenses.approve-treasurer');
    Route::post('/expenses/{expense}/disburse', [ExpenseController::class, 'disburse'])->name('expenses.disburse');
    Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');

    // Financial Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/cash-ledger', [ReportController::class, 'cashLedger'])->name('cash-ledger');
        Route::get('/cash-ledger/excel', [ReportController::class, 'exportCashLedgerExcel'])->name('cash-ledger.excel');
        Route::get('/cash-ledger/pdf', [ReportController::class, 'exportCashLedgerPdf'])->name('cash-ledger.pdf');

        Route::get('/income-expense', [ReportController::class, 'incomeExpense'])->name('income-expense');
        Route::get('/income-expense/excel', [ReportController::class, 'exportIncomeExpenseExcel'])->name('income-expense.excel');
        Route::get('/income-expense/pdf', [ReportController::class, 'exportIncomeExpensePdf'])->name('income-expense.pdf');

        Route::get('/arrears', [ReportController::class, 'arrears'])->name('arrears');
        Route::get('/arrears/excel', [ReportController::class, 'exportArrearsExcel'])->name('arrears.excel');
    });

    // Closing Periods (Tutup Buku)
    Route::get('/closing-periods', [ClosingPeriodController::class, 'index'])->name('closing-periods.index');
    Route::post('/closing-periods/close', [ClosingPeriodController::class, 'close'])->name('closing-periods.close');
    Route::post('/closing-periods/reopen', [ClosingPeriodController::class, 'reopen'])->name('closing-periods.reopen');

    // Audit Trail
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
