<?php

declare(strict_types=1);

namespace App\Enums;

enum PeriodStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Terbuka (Bisa Transaksi)',
            self::CLOSED => 'Tutup Buku (Terkunci)',
        };
    }
}
