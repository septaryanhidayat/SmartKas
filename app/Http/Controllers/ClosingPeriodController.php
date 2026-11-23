<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Accounting\CloseMonthlyPeriodAction;
use App\Models\ClosingPeriod;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClosingPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::where('is_active', true)->get();
        $periods = ClosingPeriod::with(['unit', 'closedBy'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('closing_periods.index', compact('periods', 'units'));
    }

    public function close(Request $request, CloseMonthlyPeriodAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020'],
            'month' => ['required', 'integer', 'between:1,12'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $action->close(
                year: (int) $validated['year'],
                month: (int) $validated['month'],
                unitId: ! empty($validated['unit_id']) ? (int) $validated['unit_id'] : null,
                notes: $validated['notes'] ?? null,
                closedByUserId: auth()->id()
            );

            return back()->with('success', 'Periode buku berhasil ditutup (terkunci)!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reopen(Request $request, CloseMonthlyPeriodAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'reason' => ['required', 'string', 'min:5'],
        ]);

        try {
            $action->reopen(
                year: (int) $validated['year'],
                month: (int) $validated['month'],
                unitId: ! empty($validated['unit_id']) ? (int) $validated['unit_id'] : null,
                reason: $validated['reason'],
                reopenedByUserId: auth()->id()
            );

            return back()->with('success', 'Periode buku berhasil dibuka kembali!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
