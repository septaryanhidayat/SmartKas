<?php

declare(strict_types=1);

if (! function_exists('format_rupiah')) {
    /**
     * Format a numeric amount to Indonesian Rupiah representation.
     */
    function format_rupiah(int|float|string|null $amount, bool $withSymbol = true): string
    {
        $numeric = (float) ($amount ?? 0);
        $formatted = number_format($numeric, 0, ',', '.');

        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (! function_exists('terbilang')) {
    /**
     * Convert number to Indonesian words (useful for receipts).
     */
    function terbilang(int|float $angka): string
    {
        $angka = abs((int) $angka);
        $baca = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        $terbilang = '';

        if ($angka < 12) {
            $terbilang = ' ' . $baca[$angka];
        } elseif ($angka < 20) {
            $terbilang = terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            $terbilang = terbilang((int) ($angka / 10)) . ' puluh' . terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' seratus' . terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = terbilang((int) ($angka / 100)) . ' ratus' . terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' seribu' . terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = terbilang((int) ($angka / 1000)) . ' ribu' . terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = terbilang((int) ($angka / 1000000)) . ' juta' . terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $terbilang = terbilang((int) ($angka / 1000000000)) . ' milyar' . terbilang($angka % 1000000000);
        }

        return trim($terbilang);
    }
}
