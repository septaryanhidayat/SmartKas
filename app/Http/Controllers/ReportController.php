<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ArrearsExport;
use App\Exports\CashLedgerExport;
use App\Exports\IncomeExpenseExport;
use App\Models\CashAccount;
use App\Models\Classroom;
use App\Models\Unit;
use App\Services\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function cashLedger(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $cashAccountId = $request->query('cash_account_id') ? (int) $request->query('cash_account_id') : null;

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $units = Unit::where('is_active', true)->get();
        $cashAccounts = CashAccount::where('is_active', true)->get();

        $report = $this->reportService->getCashLedgerReport(
            cashAccountId: $cashAccountId,
            unitId: $unitId,
            startDate: $startDate,
            endDate: $endDate
        );

        return view('reports.cash_ledger', compact('report', 'units', 'cashAccounts', 'unitId', 'cashAccountId', 'startDate', 'endDate'));
    }

    public function exportCashLedgerExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $cashAccountId = $request->query('cash_account_id') ? (int) $request->query('cash_account_id') : null;

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getCashLedgerReport($cashAccountId, $unitId, $startDate, $endDate);

        return Excel::download(
            new CashLedgerExport($report['mutations'], $report['opening_balance'], $report['closing_balance']),
            'Buku-Kas-Umum-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportCashLedgerPdf(Request $request): Response
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $cashAccountId = $request->query('cash_account_id') ? (int) $request->query('cash_account_id') : null;

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getCashLedgerReport($cashAccountId, $unitId, $startDate, $endDate);

        return Pdf::loadView('pdf.cash_ledger', compact('report', 'startDate', 'endDate'))
            ->setPaper('a4', 'landscape')
            ->download('Buku-Kas-Umum-'.now()->format('YmdHis').'.pdf');
    }

    public function incomeExpense(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $units = Unit::where('is_active', true)->get();

        $report = $this->reportService->getIncomeExpenseReport($unitId, $startDate, $endDate);

        return view('reports.income_expense', compact('report', 'units', 'unitId', 'startDate', 'endDate'));
    }

    public function exportIncomeExpenseExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getIncomeExpenseReport($unitId, $startDate, $endDate);

        return Excel::download(
            new IncomeExpenseExport(
                $report['incomes'],
                $report['expenses'],
                $report['total_income'],
                $report['total_expense'],
                $report['surplus_deficit']
            ),
            'Laporan-Surplus-Defisit-'.now()->format('YmdHis').'.xlsx'
        );
    }

    public function exportIncomeExpensePdf(Request $request): Response
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);

        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now()->endOfMonth();

        $report = $this->reportService->getIncomeExpenseReport($unitId, $startDate, $endDate);

        return Pdf::loadView('pdf.income_expense', compact('report'))
            ->setPaper('a4', 'portrait')
            ->download('Laporan-Surplus-Defisit-'.now()->format('YmdHis').'.pdf');
    }

    public function arrears(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $classroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;

        $units = Unit::where('is_active', true)->where('level', '!=', 'foundation')->get();
        $classrooms = Classroom::query();
        if ($unitId) {
            $classrooms->where('unit_id', $unitId);
        }
        $classrooms = $classrooms->get();

        $arrears = $this->reportService->getArrearsList($unitId, $classroomId);

        return view('reports.arrears', compact('arrears', 'units', 'classrooms', 'unitId', 'classroomId'));
    }

    public function exportArrearsExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $classroomId = $request->query('classroom_id') ? (int) $request->query('classroom_id') : null;

        $arrears = $this->reportService->getArrearsList($unitId, $classroomId);

        return Excel::download(
            new ArrearsExport($arrears),
            'Data-Tunggakan-Siswa-'.now()->format('YmdHis').'.xlsx'
        );
    }
}
