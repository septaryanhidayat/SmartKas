<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Billing\GenerateMonthlyInvoicesAction;
use App\Models\AcademicYear;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyBillingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-monthly {--unit= : ID of specific unit} {--month= : Target month (1-12)} {--year= : Target year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly recurring billing invoices for active students';

    /**
     * Execute the console command.
     */
    public function handle(GenerateMonthlyInvoicesAction $action): int
    {
        $now = Carbon::now();
        $month = (int) ($this->option('month') ?? $now->month);
        $year = (int) ($this->option('year') ?? $now->year);
        $unitOption = $this->option('unit');

        $academicYear = AcademicYear::getActive();
        if (! $academicYear) {
            $this->error('Tidak ada Tahun Ajaran aktif!');

            return Command::FAILURE;
        }

        $unitsQuery = Unit::query()->where('is_active', true)->where('level', '!=', 'foundation');
        if ($unitOption) {
            $unitsQuery->where('id', $unitOption);
        }

        $units = $unitsQuery->get();
        if ($units->isEmpty()) {
            $this->warn('Tidak ada unit sekolah yang ditemukan.');

            return Command::SUCCESS;
        }

        $this->info("Memulai pembuatan tagihan bulanan: Periode {$month}/{$year}...");

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($units as $unit) {
            $result = $action->execute(
                unitId: $unit->id,
                academicYearId: $academicYear->id,
                month: $month,
                year: $year
            );

            $this->line(" - Unit [{$unit->name}]: {$result['created_count']} tagihan dibuat, {$result['skipped_count']} dilewati (duplikat).");
            $totalCreated += $result['created_count'];
            $totalSkipped += $result['skipped_count'];
        }

        $this->info("Selesai! Total tagihan dibuat: {$totalCreated}, Total dilewati: {$totalSkipped}.");

        return Command::SUCCESS;
    }
}
