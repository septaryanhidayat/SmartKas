<?php

declare(strict_types=1);
use App\Services\AppSettingService;

if (! function_exists('format_rupiah')) {
    /**
     * Format a numeric amount to Indonesian Rupiah representation.
     */
    function format_rupiah(int|float|string|null $amount, bool $withSymbol = true): string
    {
        $numeric = (float) ($amount ?? 0);
        $formatted = number_format($numeric, 0, ',', '.');

        return $withSymbol ? 'Rp '.$formatted : $formatted;
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
            $terbilang = ' '.$baca[$angka];
        } elseif ($angka < 20) {
            $terbilang = terbilang($angka - 10).' belas';
        } elseif ($angka < 100) {
            $terbilang = terbilang((int) ($angka / 10)).' puluh'.terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' seratus'.terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = terbilang((int) ($angka / 100)).' ratus'.terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' seribu'.terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = terbilang((int) ($angka / 1000)).' ribu'.terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = terbilang((int) ($angka / 1000000)).' juta'.terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $terbilang = terbilang((int) ($angka / 1000000000)).' milyar'.terbilang($angka % 1000000000);
        }

        return trim($terbilang);
    }
}

if (! function_exists('app_setting')) {
    /**
     * Retrieve a cached application setting with safe default fallback.
     */
    function app_setting(string $key, ?string $default = null): ?string
    {
        return app(AppSettingService::class)->get($key, $default);
    }
}

if (! function_exists('format_tx_code')) {
    /**
     * Format long legacy invoice/receipt codes compactly while preserving prefix & suffix.
     */
    function format_tx_code(?string $code, ?string $mode = null): string
    {
        $clean = trim((string) $code);
        if ($clean === '') {
            return '-';
        }

        $displayMode = $mode ?? (string) app_setting('code_display_mode', 'compact');
        if ($displayMode === 'full' || mb_strlen($clean) <= 16) {
            return $clean;
        }

        return mb_substr($clean, 0, 7).'…'.mb_substr($clean, -5);
    }
}

if (! function_exists('format_ledger_description')) {
    /**
     * Compact embedded long alphanumeric reference codes inside ledger descriptions.
     */
    function format_ledger_description(?string $description): string
    {
        $text = trim((string) $description);
        if ($text === '') {
            return '-';
        }

        if (app_setting('code_display_mode', 'compact') === 'full') {
            return $text;
        }

        return (string) preg_replace_callback(
            '/\b[A-Z0-9\-\/]{18,}\b/',
            fn (array $matches): string => format_tx_code($matches[0], 'compact'),
            $text
        );
    }
}

if (! function_exists('app_asset_url')) {
    /**
     * Resolve a public asset URL safely with default fallback if file was removed.
     */
    function app_asset_url(?string $relativePath, string $fallback = 'images/smartkas-horizontal.png'): string
    {
        $clean = ltrim((string) $relativePath, '/');
        if ($clean !== '' && is_file(public_path($clean))) {
            return asset($clean);
        }

        return asset($fallback);
    }
}

if (! function_exists('logo_data_uri')) {
    /**
     * Convert a local public image into a base64 data URI for reliable DomPDF rendering.
     */
    function logo_data_uri(?string $relativePath = null, string $fallback = 'images/yayasan-logo.png'): ?string
    {
        $clean = ltrim((string) ($relativePath ?: $fallback), '/');
        $fullPath = public_path($clean);

        if (! is_file($fullPath)) {
            $fullPath = public_path($fallback);
        }

        if (! is_file($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath) ?: 'image/png';
        $data = @file_get_contents($fullPath);

        if ($data === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
