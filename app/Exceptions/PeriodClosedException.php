<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class PeriodClosedException extends RuntimeException
{
    public function __construct(string $message = 'Periode buku untuk bulan dan unit ini sudah ditutup. Transaksi tidak dapat diubah atau ditambah.')
    {
        parent::__construct($message);
    }
}
