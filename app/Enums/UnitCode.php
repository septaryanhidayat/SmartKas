<?php

declare(strict_types=1);

namespace App\Enums;

enum UnitCode: string
{
    case YAYASAN = 'YAYASAN';
    case TKIT = 'TKIT';
    case SDIT = 'SDIT';
    case SMPIT = 'SMPIT';
    case SMAIT = 'SMAIT';

    public function label(): string
    {
        return match ($this) {
            self::YAYASAN => 'Kantor Pusat Yayasan',
            self::TKIT => 'KB/TKIT Islam Terpadu',
            self::SDIT => 'SDIT Islam Terpadu',
            self::SMPIT => 'SMPIT Islam Terpadu',
            self::SMAIT => 'SMAIT Islam Terpadu',
        };
    }
}
