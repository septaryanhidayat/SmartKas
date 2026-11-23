<?php

declare(strict_types=1);

namespace App\Enums;

enum StudentStatus: string
{
    case ACTIVE = 'active';
    case GRADUATED = 'graduated';
    case TRANSFERRED = 'transferred';
    case ON_LEAVE = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::GRADUATED => 'Lulus',
            self::TRANSFERRED => 'Pindah',
            self::ON_LEAVE => 'Cuti',
        };
    }
}
