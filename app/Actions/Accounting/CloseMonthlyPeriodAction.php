<?php

declare(strict_types=1);

namespace App\Actions\Accounting;

use App\Enums\PeriodStatus;
use App\Models\ClosingPeriod;
use Carbon\Carbon;
use DomainException;
use Spatie\Activitylog\Facades\LogBatch;

class CloseMonthlyPeriodAction
{
    /**
     * Close a monthly period, making financial transactions immutable.
     */
    public function close(int $year, int $month, ?int $unitId = null, ?string $notes = null, ?int $closedByUserId = null): ClosingPeriod
    {
        $userId = $closedByUserId ?? auth()->id() ?? 1;

        $period = ClosingPeriod::firstOrNew([
            'unit_id' => $unitId,
            'year' => $year,
            'month' => $month,
        ]);

        if ($period->exists && $period->status === PeriodStatus::CLOSED) {
            throw new DomainException("Periode {$month}/{$year} sudah ditutup sebelumnya.");
        }

        $period->status = PeriodStatus::CLOSED;
        $period->closed_by = $userId;
        $period->closed_at = Carbon::now();
        $period->notes = $notes ?? 'Tutup Buku Bulanan oleh Bendahara Yayasan';
        $period->save();

        activity()
            ->performedOn($period)
            ->causedBy($userId)
            ->withProperties(['unit_id' => $unitId, 'year' => $year, 'month' => $month])
            ->log("Tutup buku periode {$month}/{$year} berhasil dilakukan.");

        return $period;
    }

    /**
     * Re-open a previously closed period with a mandatory justification notes.
     */
    public function reopen(int $year, int $month, ?int $unitId = null, string $reason = '', ?int $reopenedByUserId = null): ClosingPeriod
    {
        $userId = $reopenedByUserId ?? auth()->id() ?? 1;

        $period = ClosingPeriod::where('unit_id', $unitId)
            ->where('year', $year)
            ->where('month', $month)
            ->firstOrFail();

        $period->status = PeriodStatus::OPEN;
        $period->notes = "Dibuka kembali: {$reason} (oleh User ID: {$userId})";
        $period->closed_at = null;
        $period->save();

        activity()
            ->performedOn($period)
            ->causedBy($userId)
            ->withProperties(['unit_id' => $unitId, 'year' => $year, 'month' => $month, 'reason' => $reason])
            ->log("Buka kembali tutup buku periode {$month}/{$year}: {$reason}");

        return $period;
    }
}
