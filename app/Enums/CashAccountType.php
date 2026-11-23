<?php

declare(strict_types=1);

namespace App\Enums;

enum CashAccountType: string
{
    case BANK = 'bank';
    case CASH = 'cash';
    case PETTY_CASH = 'petty_cash';

    public function label(): string
    {
        return match ($this) {
            self::BANK => 'Rekening Bank',
            self::CASH => 'Kas Utama / Brankas',
            self::PETTY_CASH => 'Kas Kecil (Petty Cash)',
        };
    }
}
