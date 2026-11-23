<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case QRIS = 'qris';
    case VIRTUAL_ACCOUNT = 'virtual_account';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai (Kasir)',
            self::TRANSFER => 'Transfer Bank Manual',
            self::QRIS => 'QRIS',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
        };
    }
}
