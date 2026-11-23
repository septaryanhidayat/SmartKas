<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpenseStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED_PRINCIPAL = 'approved_principal';
    case APPROVED_TREASURER = 'approved_treasurer';
    case DISBURSED = 'disbursed';
    case REJECTED = 'rejected';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Konsep (Draft)',
            self::SUBMITTED => 'Diajukan',
            self::APPROVED_PRINCIPAL => 'Disetujui Kepala Sekolah (Tahap 1)',
            self::APPROVED_TREASURER => 'Disetujui Bendahara Yayasan (Tahap 2)',
            self::DISBURSED => 'Cair / Dicatat di Kas',
            self::REJECTED => 'Ditolak',
            self::VOID => 'Dibatalkan (Void)',
        };
    }
}
