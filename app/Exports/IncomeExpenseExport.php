<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExpenseExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        protected Collection $incomes,
        protected Collection $expenses,
        protected int $totalIncome,
        protected int $totalExpense,
        protected int $surplusDeficit
    ) {}

    public function collection(): Collection
    {
        $rows = collect();

        $rows->push(['=== PENDAPATAN (INCOME) ===', '', '']);
        foreach ($this->incomes as $inc) {
            $rows->push([
                $inc->code,
                $inc->name,
                (int) $inc->total_amount,
            ]);
        }
        $rows->push(['TOTAL PENDAPATAN', '', $this->totalIncome]);
        $rows->push(['', '', '']);

        $rows->push(['=== PENGELUARAN (EXPENSE) ===', '', '']);
        foreach ($this->expenses as $exp) {
            $rows->push([
                $exp->code,
                $exp->name,
                (int) $exp->total_amount,
            ]);
        }
        $rows->push(['TOTAL PENGELUARAN', '', $this->totalExpense]);
        $rows->push(['', '', '']);

        $rows->push(['SURPLUS / (DEFISIT)', '', $this->surplusDeficit]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Kode Pos',
            'Nama Pos Keuangan',
            'Nominal (Rp)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
