<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PeriodStatus;
use App\Exceptions\PeriodClosedException;
use App\Models\ClosingPeriod;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class ClosingPeriodService
{
    /**
     * Determine if the transaction period is currently closed for the given unit and date.
     */
    public function isPeriodClosed(?int $unitId, CarbonInterface|string|null $date = null): bool
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::now();
        $year = (int) $targetDate->year;
        $month = (int) $targetDate->month;

        // Check if there is an active closed period for this specific unit OR foundation-wide (unit_id IS NULL)
        return ClosingPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', PeriodStatus::CLOSED)
            ->where(function ($query) use ($unitId) {
                if ($unitId) {
                    $query->where('unit_id', $unitId)
                        ->orWhereNull('unit_id');
                } else {
                    $query->whereNull('unit_id');
                }
            })
            ->exists();
    }

    /**
     * Throw an exception if the period is locked/closed.
     *
     * @throws PeriodClosedException
     */
    public function ensurePeriodIsOpen(?int $unitId, CarbonInterface|string|null $date = null): void
    {
        if ($this->isPeriodClosed($unitId, $date)) {
            $targetDate = $date ? Carbon::parse($date) : Carbon::now();
            $monthName = $targetDate->translatedFormat('F Y');
            throw new PeriodClosedException("Periode {$monthName} telah ditutup buku. Transaksi keuangan tidak dapat dibuat, diubah, atau dihapus.");
        }
    }
}
