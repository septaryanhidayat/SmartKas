<?php

declare(strict_types=1);

namespace App\Enums;

enum BillingType: string
{
    case MONTHLY = 'monthly';
    case ANNUAL = 'annual';
    case INCIDENTAL = 'incidental';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Rutin Bulanan (SPP/Katering/dll)',
            self::ANNUAL => 'Tahunan (Daftar Ulang/dll)',
            self::INCIDENTAL => 'Insidentil (Uang Pangkal/Kegiatan/dll)',
        };
    }
}
