<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CashLedger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashLedgerExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected Collection $mutations,
        protected int $openingBalance,
        protected int $closingBalance
    ) {}

    public function collection(): Collection
    {
        return $this->mutations;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Unit',
            'Akun Kas / Bank',
            'Jenis',
            'Deskripsi',
            'Penerimaan (Debet)',
            'Pengeluaran (Kredit)',
            'Saldo Sebelum',
            'Saldo Sesudah',
            'Petugas',
        ];
    }

    /**
     * @param  CashLedger  $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->transaction_date->translatedFormat('d/m/Y H:i'),
            $row->unit?->code->value ?? 'YAYASAN',
            $row->cashAccount?->name,
            $row->type->label(),
            $row->description,
            $row->type->value === 'in' ? $row->amount : 0,
            $row->type->value === 'out' ? $row->amount : 0,
            $row->balance_before,
            $row->balance_after,
            $row->creator?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
