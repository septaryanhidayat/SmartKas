<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerType: string
{
    case IN = 'in';
    case OUT = 'out';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Penerimaan / Debet',
            self::OUT => 'Pengeluaran / Kredit',
        };
    }
}
