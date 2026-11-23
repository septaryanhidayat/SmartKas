<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArrearsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected Collection $arrears
    ) {}

    public function collection(): Collection
    {
        return $this->arrears;
    }

    public function headings(): array
    {
        return [
            'No. Tagihan',
            'Unit',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'No. WhatsApp Orang Tua',
            'Komponen Biaya',
            'Bulan/Tahun',
            'Jatuh Tempo',
            'Total Tagihan (Net)',
            'Sudah Dibayar',
            'Sisa Tunggakan',
            'Status',
        ];
    }

    /**
     * @param  Invoice  $row
     */
    public function map($row): array
    {
        $remaining = max(0, (int) $row->net_amount - (int) $row->paid_amount);

        return [
            $row->invoice_number,
            $row->unit?->code->value ?? '-',
            $row->student?->nis ?? '-',
            $row->student?->name ?? '-',
            $row->student?->classroom?->name ?? '-',
            $row->student?->parent_whatsapp ?? '-',
            $row->feeComponent?->name ?? '-',
            $row->billing_month ? "{$row->billing_month}/{$row->billing_year}" : '-',
            $row->due_date->format('d/m/Y'),
            $row->net_amount,
            $row->paid_amount,
            $remaining,
            $row->status->label(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
