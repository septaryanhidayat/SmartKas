<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case TREASURER = 'treasurer';
    case PRINCIPAL = 'principal';
    case FINANCE_ADMIN = 'finance_admin';

    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Superadmin / Ketua Yayasan',
            self::TREASURER => 'Bendahara Yayasan',
            self::PRINCIPAL => 'Kepala Sekolah Unit',
            self::FINANCE_ADMIN => 'Admin Keuangan Unit',
        };
    }
}
