<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpenseType: string
{
    case CASH_ADVANCE = 'cash_advance';
    case OPERATIONAL = 'operational';
    case PETTY_CASH = 'petty_cash';

    public function label(): string
    {
        return match ($this) {
            self::CASH_ADVANCE => 'Pengajuan Dana / RAB Kegiatan',
            self::OPERATIONAL => 'Biaya Operasional Rutin',
            self::PETTY_CASH => 'Pengeluaran Kas Kecil (Petty Cash)',
        };
    }
}
