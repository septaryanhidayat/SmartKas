<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case COMPLETED = 'completed';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Berhasil (Valid)',
            self::VOID => 'Dibatalkan (Void)',
        };
    }
}
