<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppSettingService
{
    public const CACHE_KEY = 'smartkas.app_settings.v1';

    public const OFFICIAL_ADDRESS = 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir';

    /**
     * @var array<string, array{value: string, group: string, type: string}>
     */
    public const DEFAULTS = [
        'app_name' => ['value' => 'SmartKas', 'group' => 'general', 'type' => 'text'],
        'app_tagline' => ['value' => 'Sistem Keuangan Terpusat', 'group' => 'general', 'type' => 'text'],
        'foundation_name' => ['value' => 'Yayasan Generasi Robbani Sumatera Selatan', 'group' => 'general', 'type' => 'text'],
        'foundation_short_name' => ['value' => 'Yayasan Generasi Robbani Sumsel', 'group' => 'general', 'type' => 'text'],
        'foundation_address' => ['value' => self::OFFICIAL_ADDRESS, 'group' => 'general', 'type' => 'textarea'],
        'foundation_phone' => ['value' => '0711-820123', 'group' => 'general', 'type' => 'text'],
        'foundation_email' => ['value' => 'keuangan@sitrobbani.sch.id', 'group' => 'general', 'type' => 'text'],
        'academic_year_label' => ['value' => '2026/2027', 'group' => 'general', 'type' => 'text'],
        'app_logo_path' => ['value' => 'images/smartkas-horizontal.png', 'group' => 'general', 'type' => 'image'],
        'app_icon_path' => ['value' => 'images/smartkas-icon.png', 'group' => 'general', 'type' => 'image'],
        'foundation_logo_path' => ['value' => 'images/yayasan-logo.png', 'group' => 'general', 'type' => 'image'],

        'invoice_prefix' => ['value' => 'INV', 'group' => 'invoice', 'type' => 'text'],
        'receipt_prefix' => ['value' => 'KW', 'group' => 'invoice', 'type' => 'text'],
        'default_due_day' => ['value' => '10', 'group' => 'invoice', 'type' => 'number'],
        'code_display_mode' => ['value' => 'compact', 'group' => 'invoice', 'type' => 'select'],
        'show_foundation_logo_on_pdf' => ['value' => '1', 'group' => 'invoice', 'type' => 'boolean'],
        'invoice_footer_note' => ['value' => 'Dokumen ini merupakan bukti resmi yang dikeluarkan oleh sistem keuangan Yayasan Generasi Robbani Sumatera Selatan.', 'group' => 'invoice', 'type' => 'textarea'],
        'receipt_signer_title' => ['value' => 'Staf Keuangan Yayasan', 'group' => 'invoice', 'type' => 'text'],

        'font_family' => ['value' => 'plus_jakarta', 'group' => 'appearance', 'type' => 'select'],
        'font_size' => ['value' => '14', 'group' => 'appearance', 'type' => 'select'],
        'table_density' => ['value' => 'comfortable', 'group' => 'appearance', 'type' => 'select'],
        'theme_color' => ['value' => 'navy_teal', 'group' => 'appearance', 'type' => 'select'],
    ];

    /**
     * @var array<string, array{label: string, stack: string}>
     */
    public const FONT_OPTIONS = [
        'plus_jakarta' => [
            'label' => 'Plus Jakarta Sans (Default SmartKas)',
            'stack' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
        ],
        'inter' => [
            'label' => 'Inter (Modern & Tajam untuk Angka)',
            'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif",
        ],
        'poppins' => [
            'label' => 'Poppins (Geometris & Bersih)',
            'stack' => "'Poppins', ui-sans-serif, system-ui, sans-serif",
        ],
        'nunito' => [
            'label' => 'Nunito (Ramah & Mudah Dibaca)',
            'stack' => "'Nunito', ui-sans-serif, system-ui, sans-serif",
        ],
        'roboto' => [
            'label' => 'Roboto (Klasik Korporat)',
            'stack' => "'Roboto', ui-sans-serif, system-ui, sans-serif",
        ],
        'system' => [
            'label' => 'Font Bawaan Perangkat (System UI)',
            'stack' => "ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        ],
    ];

    /**
     * @var array<string, string>
     */
    public const FONT_SIZES = [
        '13' => 'Kecil (13px — Ringkas & Muat Banyak)',
        '14' => 'Standar (14px — Seimbang)',
        '15' => 'Nyaman (15px — Lebih Jelas)',
        '16' => 'Besar (16px — Mudah Terbaca)',
    ];

    /**
     * @var array<string, array{label: string, sidebar: string, footer: string, gradient: string}>
     */
    public const THEME_COLORS = [
        'navy_teal' => [
            'label' => 'Navy & Teal SmartKas (Resmi)',
            'sidebar' => '#062547',
            'footer' => '#041B35',
            'gradient' => 'linear-gradient(135deg, #062547 0%, #0B3B6F 55%, #0D6E7A 100%)',
        ],
        'emerald' => [
            'label' => 'Hijau Zamrud Islami',
            'sidebar' => '#064E3B',
            'footer' => '#04382A',
            'gradient' => 'linear-gradient(135deg, #04382A 0%, #065F46 55%, #0F766E 100%)',
        ],
        'indigo' => [
            'label' => 'Biru Safir Korporat',
            'sidebar' => '#1E1B4B',
            'footer' => '#151238',
            'gradient' => 'linear-gradient(135deg, #1E1B4B 0%, #312E81 55%, #0F766E 100%)',
        ],
        'slate' => [
            'label' => 'Charcoal Minimalis Modern',
            'sidebar' => '#0F172A',
            'footer' => '#090E1A',
            'gradient' => 'linear-gradient(135deg, #0F172A 0%, #1E293B 55%, #0D9488 100%)',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $defaults = array_map(fn (array $meta): string => $meta['value'], self::DEFAULTS);

        try {
            if (! Schema::hasTable('app_settings')) {
                return $defaults;
            }

            $stored = Cache::remember(self::CACHE_KEY, now()->addHours(12), function (): array {
                return AppSetting::query()
                    ->pluck('value', 'key')
                    ->map(fn ($v): string => (string) ($v ?? ''))
                    ->all();
            });

            return array_merge($defaults, $stored);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->all();

        return $all[$key] ?? (self::DEFAULTS[$key]['value'] ?? $default);
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        DB::transaction(function () use ($values, $group): void {
            foreach ($values as $key => $value) {
                $defaultMeta = self::DEFAULTS[$key] ?? ['group' => $group, 'type' => 'text'];

                AppSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value ?? '',
                        'group' => $defaultMeta['group'],
                        'type' => $defaultMeta['type'],
                    ]
                );
            }
        });

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function activeFontStack(): string
    {
        $fontKey = $this->get('font_family', 'plus_jakarta');

        return self::FONT_OPTIONS[$fontKey]['stack'] ?? self::FONT_OPTIONS['plus_jakarta']['stack'];
    }

    public function activeFontSizePx(): int
    {
        $size = (int) $this->get('font_size', '14');

        return in_array($size, [13, 14, 15, 16], true) ? $size : 14;
    }

    /**
     * @return array{label: string, sidebar: string, footer: string, gradient: string}
     */
    public function activeTheme(): array
    {
        $key = $this->get('theme_color', 'navy_teal');

        return self::THEME_COLORS[$key] ?? self::THEME_COLORS['navy_teal'];
    }
}
