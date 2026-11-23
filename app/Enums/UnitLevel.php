<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitLevel: string
{
    case FOUNDATION = 'foundation';
    case TK = 'tk';
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';

    public function label(): string
    {
        return match ($this) {
            self::FOUNDATION => 'Yayasan / Pusat',
            self::TK => 'KB / TKIT',
            self::SD => 'SDIT',
            self::SMP => 'SMPIT',
            self::SMA => 'SMAIT',
        };
    }
}
