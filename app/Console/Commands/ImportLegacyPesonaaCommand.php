<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BillingType;
use App\Enums\CashAccountType;
use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Enums\LedgerType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StudentStatus;
use App\Enums\UnitCode;
use App\Enums\UnitLevel;
use App\Models\AcademicYear;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\Classroom;
use App\Models\Discount;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Spatie\Activitylog\Support\ActivityLogStatus;

class ImportLegacyPesonaaCommand extends Command
{
    protected $signature = 'smartkas:import-legacy
                            {--source-db=pesonaas_db_arsi : Source MySQL database containing legacy Pesonaa tables}
                            {--fresh : Run migrate:fresh --seed before importing legacy records}';

    protected $description = 'Import legacy financial database (pesonaas_db_arsi.sql) into SmartKas schema cleanly and losslessly';

    public function handle(): int
    {
        $sourceDb = (string) $this->option('source-db');

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $user = config('database.connections.mysql.username', 'root');
        $pass = config('database.connections.mysql.password', '');

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};dbname={$sourceDb};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (\Throwable $e) {
            $this->error("Gagal terhubung ke database sumber [{$sourceDb}]: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->info('Menjalankan migrate:fresh --seed terlebih dahulu...');
            $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
        }

        $this->info("Memulai migrasi data dari [{$sourceDb}] ke SmartKas...");

        $activityStatus = app(ActivityLogStatus::class);
        $activityStatus->disable();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Clear existing seeded demo financial/student records while preserving Units, Users, Roles, Permissions, and ExpenseCategories
            foreach ([
                'activity_log',
                'cash_ledgers',
                'payment_items',
                'payments',
                'invoices',
                'student_discounts',
                'discounts',
                'fee_components',
                'students',
                'classrooms',
                'academic_years',
                'cash_accounts',
                'closing_periods',
            ] as $table) {
                DB::table($table)->truncate();
            }

            // Ensure SMAIT unit is preserved alongside Yayasan, TKIT, SDIT, SMPIT
            $unitMap = $this->resolveUnits();
            $userMap = $this->resolveUserMap();

            // 1. Academic Years
            $yearMap = $this->importAcademicYears($pdo);
            $this->info('✓ Tahun ajaran berhasil dimigrasi: '.count($yearMap['by_legacy_id']));

            // 2. Classrooms
            [$classMap, $legacyClassUnits] = $this->importClassrooms($pdo, $unitMap, $yearMap);
            $this->info('✓ Data kelas berhasil dimigrasi: '.count($classMap));

            // 3. Students & Parents
            [$studentMap, $studentUnitMap] = $this->importStudents($pdo, $classMap, $legacyClassUnits, $unitMap);
            $this->info('✓ Data siswa & orang tua berhasil dimigrasi: '.count($studentMap));

            // 4. Cash Accounts (BSI 7206858502 + Kas Tunai & Petty Cash)
            $cashAccountMap = $this->importCashAccounts($pdo, $unitMap);
            $this->info('✓ Akun kas & bank BSI (7206858502) berhasil disiapkan.');

            // 5. Fee Components (discovered across spa_categories & spa_bills)
            $feeComponentMap = $this->importFeeComponents($pdo, $unitMap, $legacyClassUnits, $studentUnitMap, $yearMap);
            $this->info('✓ Komponen biaya berhasil dimigrasi: '.count($feeComponentMap));

            // 6. Discounts & Student Discounts
            $this->importDiscounts($pdo, $studentMap, $feeComponentMap);
            $this->info('✓ Data diskon & beasiswa berhasil dimigrasi.');

            // 7. Invoices (expanding multi-item bills cleanly)
            $billToInvoiceIds = $this->importInvoices($pdo, $unitMap, $legacyClassUnits, $studentUnitMap, $studentMap, $yearMap, $feeComponentMap, $userMap);
            $this->info('✓ Data tagihan (invoices) berhasil dimigrasi: '.Invoice::count().' baris.');

            // 8. Payments, Payment Items & Cash Ledgers (chronological exact replay)
            $paymentCount = $this->importPaymentsAndLedgers($pdo, $unitMap, $studentUnitMap, $studentMap, $billToInvoiceIds, $cashAccountMap, $userMap);
            $this->info("✓ Data kwitansi pembayaran & Buku Kas Umum berhasil direplay: {$paymentCount} transaksi.");
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $activityStatus->enable();
        }

        activity()
            ->withProperties(['source_db' => $sourceDb, 'students' => count($studentMap ?? []), 'invoices' => Invoice::count(), 'payments' => Payment::count()])
            ->log("Migrasi database historis [{$sourceDb}] ke SmartKas selesai.");

        $this->verifyIntegrity();

        return self::SUCCESS;
    }

    /**
     * @return array<int, int> legacy_unit_id => smartkas_unit_id
     */
    protected function resolveUnits(): array
    {
        $yayasan = Unit::where('code', UnitCode::YAYASAN)->firstOrFail();
        $tkit = Unit::where('code', UnitCode::TKIT)->firstOrFail();
        $sdit = Unit::where('code', UnitCode::SDIT)->firstOrFail();
        $smpit = Unit::where('code', UnitCode::SMPIT)->firstOrFail();

        Unit::firstOrCreate(
            ['code' => UnitCode::SMAIT],
            [
                'name' => 'SMAIT Robbani',
                'level' => UnitLevel::SMA,
                'address' => 'Jl. Pangeran Ayin No. 88D, Sako, Palembang, Sumatera Selatan',
                'phone' => '0711-820127',
                'principal_name' => 'Kepala Sekolah SMAIT Robbani',
                'is_active' => true,
            ]
        );

        return [
            1 => $yayasan->id,
            2 => $tkit->id,
            3 => $sdit->id,
            4 => $smpit->id,
            5 => $sdit->id, // Legacy unit 5 ('XXX' Siswa Keluar) safely archived under SDIT as transferred+soft-deleted
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function resolveUserMap(): array
    {
        $anis = User::where('email', 'anis@sitrobbani.sch.id')->first();
        $dessy = User::where('email', 'dessy@sitrobbani.sch.id')->first();
        $sughesti = User::where('email', 'sughesti@sitrobbani.sch.id')->first();
        $rojanah = User::where('email', 'tk@sitrobbani.sch.id')->first();
        $risma = User::where('email', 'sd@sitrobbani.sch.id')->first();
        $adelia = User::where('email', 'smp@sitrobbani.sch.id')->first();

        $defaultId = $anis?->id ?? $sughesti?->id ?? 1;

        return [
            'default' => $defaultId,
            'anis monika' => $anis?->id ?? $defaultId,
            'dessy aprianti' => $dessy?->id ?? $defaultId,
            'septa ryan hidayat' => $sughesti?->id ?? $defaultId,
            'admin tk' => $rojanah?->id ?? $defaultId,
            'admin sd' => $risma?->id ?? $defaultId,
            'admin smp' => $adelia?->id ?? $defaultId,
        ];
    }

    protected function resolveUserId(?string $adminName, array $userMap): int
    {
        $key = strtolower(trim((string) $adminName));

        return $userMap[$key] ?? $userMap['default'];
    }

    /**
     * @return array{by_legacy_id: array<int, int>, by_name: array<string, int>, active_id: int}
     */
    protected function importAcademicYears(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT year_id, year_code, year_name, is_active, created_at FROM spa_classyears ORDER BY year_name ASC')->fetchAll();

        $byLegacyId = [];
        $byName = [];
        $activeId = null;

        foreach ($rows as $row) {
            $name = trim((string) ($row['year_name'] ?: $row['year_code']));
            $parts = explode('/', $name);
            $startYear = (int) ($parts[0] ?? date('Y'));
            $endYear = (int) ($parts[1] ?? ($startYear + 1));

            $year = AcademicYear::create([
                'name' => $name,
                'start_date' => "{$startYear}-07-01",
                'end_date' => "{$endYear}-06-30",
                'is_active' => (bool) $row['is_active'],
            ]);

            $byLegacyId[(int) $row['year_id']] = $year->id;
            $byName[$name] = $year->id;

            if ($year->is_active) {
                $activeId = $year->id;
            }
        }

        if (! $activeId) {
            $activeId = (int) AcademicYear::orderByDesc('name')->value('id');
            AcademicYear::where('id', $activeId)->update(['is_active' => true]);
        }

        return [
            'by_legacy_id' => $byLegacyId,
            'by_name' => $byName,
            'active_id' => $activeId,
        ];
    }

    /**
     * @return array{0: array<int, int>, 1: array<int, int>} [legacy_class_id => smartkas_class_id, legacy_class_id => legacy_unit_id]
     */
    protected function importClassrooms(PDO $pdo, array $unitMap, array $yearMap): array
    {
        $rows = $pdo->query('SELECT * FROM spa_classrooms ORDER BY class_id ASC')->fetchAll();

        $classMap = [];
        $legacyClassUnits = [];

        foreach ($rows as $row) {
            $legacyClassId = (int) $row['class_id'];
            $legacyUnitId = (int) ($row['unit_id'] ?: 3);
            $legacyClassUnits[$legacyClassId] = $legacyUnitId;

            // Skip synthetic 'X Sudah Keluar' unit-5 class from SmartKas active classrooms; students will have classroom_id = null
            if ($legacyUnitId === 5) {
                continue;
            }

            $smartUnitId = $unitMap[$legacyUnitId] ?? $unitMap[3];
            $smartYearId = $yearMap['by_legacy_id'][(int) $row['year_id']]
                ?? ($yearMap['by_name'][trim((string) $row['class_year'])] ?? $yearMap['active_id']);

            $gradeLevel = $this->inferGradeLevel((string) $row['class_name'], (string) $row['class_code'], $legacyUnitId);

            $classroom = Classroom::create([
                'unit_id' => $smartUnitId,
                'academic_year_id' => $smartYearId,
                'name' => trim((string) $row['class_name']),
                'grade_level' => $gradeLevel,
            ]);

            if ((int) $row['is_deleted'] === 1) {
                $classroom->delete();
            }

            $classMap[$legacyClassId] = $classroom->id;
        }

        return [$classMap, $legacyClassUnits];
    }

    protected function inferGradeLevel(string $className, string $classCode, int $legacyUnitId): int
    {
        if (preg_match('/(\d+)/', $className, $m) || preg_match('/(\d+)/', $classCode, $m)) {
            $num = (int) $m[1];
            if ($legacyUnitId === 4 && $num >= 1 && $num <= 3) {
                return $num + 6;
            }

            return max(1, min(12, $num));
        }

        if (stripos($className, 'B') !== false || stripos($classCode, 'TKB') !== false) {
            return 2;
        }

        return 1;
    }

    /**
     * @return array{0: array<int, int>, 1: array<int, int>} [legacy_student_id => smartkas_student_id, legacy_student_id => legacy_unit_id]
     */
    protected function importStudents(PDO $pdo, array $classMap, array $legacyClassUnits, array $unitMap): array
    {
        $parentsRaw = $pdo->query('SELECT * FROM spa_students_parents')->fetchAll();
        $parentsByStudent = [];
        foreach ($parentsRaw as $p) {
            $parentsByStudent[(int) $p['student_id']] = $p;
        }

        // Order active non-deleted students first so siblings sharing a parent phone leave the primary NIS with the active child
        $rows = $pdo->query('SELECT * FROM spa_students ORDER BY is_deleted ASC, (student_status = "A") DESC, student_id ASC')->fetchAll();

        $studentMap = [];
        $studentUnitMap = [];
        $usedNis = [];

        foreach ($rows as $row) {
            $legacyId = (int) $row['student_id'];
            $legacyClassId = (int) ($row['class_id'] ?: 0);
            $legacyUnitId = $legacyClassUnits[$legacyClassId] ?? 3;
            $studentUnitMap[$legacyId] = $legacyUnitId;

            $isExitedUnit = $legacyUnitId === 5;
            $smartUnitId = $unitMap[$legacyUnitId] ?? $unitMap[3];
            $smartClassId = $classMap[$legacyClassId] ?? null;

            $baseNis = trim((string) ($row['student_number'] ?: 'SISWA-'.$legacyId));
            $nis = substr($baseNis, 0, 26);
            $suffix = 2;
            while (isset($usedNis[$nis])) {
                $nis = substr($baseNis, 0, 25).'-'.$suffix;
                $suffix++;
            }
            $usedNis[$nis] = true;

            $p = $parentsByStudent[$legacyId] ?? [];
            $parentName = trim((string) (($p['father_name'] ?? '') ?: (($p['mother_name'] ?? '') ?: ($p['guardian_name'] ?? ''))));
            if ($parentName === '') {
                $parentName = 'Wali '.$row['student_name'];
            }

            $rawPhone = trim((string) (($p['father_phone'] ?? '') ?: (($p['mother_phone'] ?? '') ?: (($p['guardian_phone'] ?? '') ?: ($row['student_phone'] ?? '')))));
            $parentWhatsapp = $this->normalizePhone($rawPhone);

            $status = match ($row['student_status']) {
                'L' => StudentStatus::GRADUATED,
                'K' => StudentStatus::TRANSFERRED,
                'A' => StudentStatus::ACTIVE,
                default => ((int) $row['is_deleted'] === 1 || $isExitedUnit) ? StudentStatus::TRANSFERRED : StudentStatus::ACTIVE,
            };

            $enrolledYear = trim((string) ($row['student_year_in'] ?: ''));
            $enrolledAt = preg_match('/^\d{4}$/', $enrolledYear)
                ? "{$enrolledYear}-07-01"
                : ($this->parseSafeDate($row['created_at']) ?? '2024-07-01');

            $student = Student::create([
                'unit_id' => $smartUnitId,
                'classroom_id' => $isExitedUnit ? null : $smartClassId,
                'nis' => $nis,
                'nisn' => trim((string) ($row['student_nisn'] ?? '')) ?: null,
                'name' => trim((string) $row['student_name']),
                'parent_name' => mb_substr($parentName, 0, 150),
                'parent_whatsapp' => $parentWhatsapp ?: '-',
                'status' => $status,
                'enrolled_at' => $enrolledAt,
            ]);

            if ((int) $row['is_deleted'] === 1 || $isExitedUnit) {
                $student->deleted_at = $row['deleted_at'] ?: now();
                $student->save();
            }

            $studentMap[$legacyId] = $student->id;
        }

        return [$studentMap, $studentUnitMap];
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * @return array{bank_bsi: CashAccount, cash_yayasan: CashAccount, unit_cash: array<int, CashAccount>}
     */
    protected function importCashAccounts(PDO $pdo, array $unitMap): array
    {
        $bankRow = $pdo->query('SELECT * FROM spa_banks WHERE is_active = 1 ORDER BY bank_id ASC LIMIT 1')->fetch();

        $bankBsi = CashAccount::create([
            'unit_id' => null,
            'name' => ($bankRow['bank_name'] ?? 'Bank Syariah Indonesia').' Yayasan',
            'type' => CashAccountType::BANK,
            'account_number' => $bankRow['bank_number'] ?? '7206858502',
            'bank_name' => $bankRow['bank_name'] ?? 'Bank Syariah Indonesia',
            'current_balance' => 0,
            'is_active' => true,
        ]);

        $cashYayasan = CashAccount::create([
            'unit_id' => null,
            'name' => 'Brankas Kas Tunai Yayasan',
            'type' => CashAccountType::CASH,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        $unitCash = [];
        $schoolUnits = Unit::where('level', '!=', UnitLevel::FOUNDATION)->orderBy('id')->get();
        foreach ($schoolUnits as $u) {
            $cashier = CashAccount::create([
                'unit_id' => $u->id,
                'name' => "Kasir Tunai {$u->code->value}",
                'type' => CashAccountType::CASH,
                'current_balance' => 0,
                'is_active' => true,
            ]);

            CashAccount::create([
                'unit_id' => $u->id,
                'name' => "Petty Cash {$u->code->value}",
                'type' => CashAccountType::PETTY_CASH,
                'current_balance' => 0,
                'is_active' => true,
            ]);

            $unitCash[$u->id] = $cashier;
        }

        return [
            'bank_bsi' => $bankBsi,
            'cash_yayasan' => $cashYayasan,
            'unit_cash' => $unitCash,
        ];
    }

    /**
     * @return array<string, int> key "{smart_unit_id}|{upper_code}" => fee_component_id
     */
    protected function importFeeComponents(PDO $pdo, array $unitMap, array $legacyClassUnits, array $studentUnitMap, array $yearMap): array
    {
        $categoriesByCode = [];
        foreach ($pdo->query('SELECT * FROM spa_categories')->fetchAll() as $cat) {
            $code = strtoupper(trim((string) $cat['category_code']));
            $categoriesByCode[$code] = $cat;
        }

        // Gather all (unit, code, name, amount) combinations actually used across slots 1..6 of spa_bills
        $discovered = [];
        $bills = $pdo->query('SELECT unit_id, class_id, student_id, bill_year, bill_category_1, bill_name_1, bill_amount_1, bill_category_2, bill_name_2, bill_amount_2, bill_category_3, bill_name_3, bill_amount_3, bill_category_4, bill_name_4, bill_amount_4, bill_category_5, bill_name_5, bill_amount_5, bill_category_6, bill_name_6, bill_amount_6 FROM spa_bills')->fetchAll();

        foreach ($bills as $b) {
            $legacyUnit = (int) ($b['unit_id'] ?: ($legacyClassUnits[(int) $b['class_id']] ?? ($studentUnitMap[(int) $b['student_id']] ?? 3)));
            $smartUnitId = $unitMap[$legacyUnit] ?? $unitMap[3];

            for ($k = 1; $k <= 6; $k++) {
                $rawCode = trim((string) ($b["bill_category_{$k}"] ?? ''));
                $rawName = trim((string) ($b["bill_name_{$k}"] ?? ''));
                $amt = (int) round((float) ($b["bill_amount_{$k}"] ?? 0));

                if ($rawCode === '' && $rawName === '') {
                    continue;
                }

                $code = strtoupper($rawCode !== '' ? $rawCode : ('ITEM-'.$k));
                $key = "{$smartUnitId}|{$code}";
                $year = (int) ($b['bill_year'] ?: 0);

                if (! isset($discovered[$key])) {
                    $catMeta = $categoriesByCode[$code] ?? null;
                    $discovered[$key] = [
                        'unit_id' => $smartUnitId,
                        'code' => substr($code, 0, 30),
                        'name' => mb_substr($rawName !== '' ? $rawName : ($catMeta['category_name'] ?? $code), 0, 100),
                        'amount' => $amt > 0 ? $amt : (int) round((float) ($catMeta['category_amount'] ?? 0)),
                        'latest_year' => $year,
                        'usage_count' => 1,
                    ];
                } else {
                    $discovered[$key]['usage_count']++;
                    if ($year > $discovered[$key]['latest_year']) {
                        $discovered[$key]['latest_year'] = $year;
                        if ($amt > 0) {
                            $discovered[$key]['amount'] = $amt;
                        }
                    }
                }
            }
        }

        // Canonical primary active monthly tariff per unit (prevents duplicate invoice runs when admin triggers auto-billing)
        $primaryActiveMonthlyCodes = ['SPPTK25-26', 'SPPSD-01', 'SPPSISWASMP'];

        $map = [];
        foreach ($discovered as $key => $info) {
            $billingType = $this->inferBillingType($info['code'], $info['name']);
            $isActiveMonthly = $billingType === BillingType::MONTHLY
                ? in_array($info['code'], $primaryActiveMonthlyCodes, true)
                : $info['latest_year'] >= 2025;

            $fc = FeeComponent::create([
                'unit_id' => $info['unit_id'],
                'academic_year_id' => $yearMap['active_id'],
                'code' => $info['code'],
                'name' => $info['name'],
                'billing_type' => $billingType,
                'default_amount' => max(0, $info['amount']),
                'is_active' => $isActiveMonthly,
            ]);

            $map[$key] = $fc->id;
        }

        // Add default SMAIT SPP tariff so SMAIT unit is ready immediately
        $smait = Unit::where('code', UnitCode::SMAIT)->first();
        if ($smait) {
            $smaitFc = FeeComponent::create([
                'unit_id' => $smait->id,
                'academic_year_id' => $yearMap['active_id'],
                'code' => 'SPP-SMAIT',
                'name' => 'SPP Bulanan SMAIT',
                'billing_type' => BillingType::MONTHLY,
                'default_amount' => 500000,
                'is_active' => true,
            ]);
            $map["{$smait->id}|SPP-SMAIT"] = $smaitFc->id;
        }

        return $map;
    }

    protected function inferBillingType(string $code, string $name): BillingType
    {
        $haystack = strtoupper($code.' '.$name);

        if (preg_match('/SERAGAM|SRGM|DAFTAR|PANGKAL|GEDUNG|UP\b|UP2|UBT|UDU|DU\d|USG|UJIAN|FORMULIR|CELANA/', $haystack)) {
            if (preg_match('/SERAGAM|SRGM|DAFTAR|UDU|DU\d/', $haystack)) {
                return BillingType::ANNUAL;
            }

            return BillingType::INCIDENTAL;
        }

        if (preg_match('/SPP|CATERING|CS\w+|CTG|TPA|EKSKUL|FUTSAL|MEMANAH|CODING|RENANG|PENDAMPING|UGP/', $haystack)) {
            return BillingType::MONTHLY;
        }

        return BillingType::INCIDENTAL;
    }

    protected function importDiscounts(PDO $pdo, array $studentMap, array $feeComponentMap): void
    {
        // Standard foundation discounts
        Discount::create([
            'unit_id' => null,
            'name' => 'Beasiswa Yatim & Dhuafa 100%',
            'type' => DiscountType::PERCENTAGE,
            'value' => 100,
            'is_active' => true,
        ]);

        Discount::create([
            'unit_id' => null,
            'name' => 'Diskon Anak Guru 50%',
            'type' => DiscountType::PERCENTAGE,
            'value' => 50,
            'is_active' => true,
        ]);

        $rows = $pdo->query('SELECT * FROM spa_bills_discounts')->fetchAll();
        foreach ($rows as $row) {
            $type = $row['discount_type'] === 'P' ? DiscountType::PERCENTAGE : DiscountType::FIXED;
            $val = (int) round((float) $row['discount_amount']);
            $note = trim((string) ($row['discount_note'] ?: ($type === DiscountType::PERCENTAGE ? "Potongan {$val}%" : 'Potongan Khusus Rp '.number_format($val, 0, ',', '.'))));

            $discount = Discount::create([
                'unit_id' => null,
                'name' => mb_substr($note, 0, 100),
                'type' => $type,
                'value' => $val,
                'is_active' => true,
            ]);

            $legacyStudentId = (int) ($row['student_id'] ?? 0);
            if ($legacyStudentId && isset($studentMap[$legacyStudentId])) {
                $catCode = strtoupper(trim((string) ($row['category_code'] ?? '')));
                $student = Student::withTrashed()->find($studentMap[$legacyStudentId]);
                $fcId = $student ? ($feeComponentMap["{$student->unit_id}|{$catCode}"] ?? null) : null;

                StudentDiscount::create([
                    'student_id' => $studentMap[$legacyStudentId],
                    'discount_id' => $discount->id,
                    'fee_component_id' => $fcId,
                    'valid_from' => '2024-07-01',
                    'valid_until' => null,
                    'notes' => $note,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<int, array{id: int, unit_id: int, net_amount: int, paid_amount: int}>>
     */
    protected function importInvoices(
        PDO $pdo,
        array $unitMap,
        array $legacyClassUnits,
        array $studentUnitMap,
        array $studentMap,
        array $yearMap,
        array $feeComponentMap,
        array $userMap
    ): array {
        ini_set('memory_limit', '512M');
        DB::connection()->disableQueryLog();

        $stmt = $pdo->query('SELECT * FROM spa_bills ORDER BY bill_id ASC');
        $defaultCreatorId = $userMap['default'];
        $nowStr = Carbon::now()->toDateTimeString();
        $fallbackFcId = (int) reset($feeComponentMap);

        $billToInvoiceIds = [];

        DB::transaction(function () use (
            $stmt,
            $unitMap,
            $legacyClassUnits,
            $studentUnitMap,
            $studentMap,
            $yearMap,
            $feeComponentMap,
            $defaultCreatorId,
            $nowStr,
            $fallbackFcId,
            &$billToInvoiceIds
        ): void {
            while ($b = $stmt->fetch()) {
                $legacyBillId = (int) $b['bill_id'];
                $legacyStudentId = (int) $b['student_id'];
                $smartStudentId = $studentMap[$legacyStudentId] ?? null;

                if (! $smartStudentId) {
                    continue;
                }

                $legacyUnit = (int) ($b['unit_id'] ?: ($legacyClassUnits[(int) $b['class_id']] ?? ($studentUnitMap[$legacyStudentId] ?? 3)));
                $smartUnitId = $unitMap[$legacyUnit] ?? $unitMap[3];

                $billMonth = (int) ($b['bill_month'] ?: 0);
                $billYear = (int) ($b['bill_year'] ?: 0);
                $academicYearId = $this->resolveAcademicYearIdForDate($billYear, $billMonth, $yearMap);
                $dueDate = $this->buildDueDate($billYear, $billMonth, (int) ($b['bill_due'] ?: 10), $b['created_at']);

                // Extract active slots 1..6
                $slots = [];
                for ($k = 1; $k <= 6; $k++) {
                    $catCode = strtoupper(trim((string) ($b["bill_category_{$k}"] ?? '')));
                    $catName = trim((string) ($b["bill_name_{$k}"] ?? ''));
                    $gross = (int) round((float) ($b["bill_amount_{$k}"] ?? 0));
                    $disc = (int) round((float) ($b["bill_discount_{$k}"] ?? 0));

                    if ($catCode === '' && $catName === '' && $gross === 0) {
                        continue;
                    }

                    $slots[] = [
                        'slot' => $k,
                        'code' => $catCode !== '' ? $catCode : "ITEM-{$k}",
                        'name' => $catName,
                        'gross' => $gross,
                        'disc' => $disc,
                    ];
                }

                if (empty($slots)) {
                    $slots[] = [
                        'slot' => 1,
                        'code' => 'TAGIHAN',
                        'name' => 'Tagihan Siswa',
                        'gross' => (int) round((float) $b['amount_total'] + (float) $b['amount_discount']),
                        'disc' => (int) round((float) $b['amount_discount']),
                    ];
                }

                // Verify discount sum matches amount_discount
                $totalBillDiscount = (int) round((float) $b['amount_discount']);
                $slotDiscSum = array_sum(array_column($slots, 'disc'));
                if ($totalBillDiscount !== $slotDiscSum && $totalBillDiscount > 0) {
                    $slots[0]['disc'] = min($slots[0]['gross'], $totalBillDiscount);
                }

                $createdAt = $this->parseSafeDateTime($b['created_at']) ?? $nowStr;
                $updatedAt = $this->parseSafeDateTime($b['updated_at']) ?? $nowStr;

                $createdForBill = [];
                foreach ($slots as $idx => $slot) {
                    $fcId = $feeComponentMap["{$smartUnitId}|{$slot['code']}"] ?? $fallbackFcId;
                    $invNumber = $idx === 0
                        ? substr((string) $b['bill_code'], 0, 50)
                        : substr((string) $b['bill_code'], 0, 44).'-'.$slot['slot'];

                    $net = max(0, $slot['gross'] - $slot['disc']);

                    $invId = DB::table('invoices')->insertGetId([
                        'unit_id' => $smartUnitId,
                        'student_id' => $smartStudentId,
                        'fee_component_id' => $fcId,
                        'academic_year_id' => $academicYearId,
                        'invoice_number' => $invNumber,
                        'billing_month' => ($billMonth >= 1 && $billMonth <= 12) ? $billMonth : null,
                        'billing_year' => $billYear >= 2020 ? $billYear : null,
                        'due_date' => $dueDate,
                        'gross_amount' => $slot['gross'],
                        'discount_amount' => $slot['disc'],
                        'net_amount' => $net,
                        'paid_amount' => 0,
                        'status' => $net === 0 ? InvoiceStatus::PAID->value : InvoiceStatus::UNPAID->value,
                        'notes' => trim((string) ($b['bill_note'] ?: ($slot['name'] ?: null))) ?: null,
                        'created_by' => $defaultCreatorId,
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                    $createdForBill[] = [
                        'id' => $invId,
                        'unit_id' => $smartUnitId,
                        'net_amount' => $net,
                        'paid_amount' => 0,
                    ];
                }

                $billToInvoiceIds[$legacyBillId] = $createdForBill;
            }
        });

        return $billToInvoiceIds;
    }

    protected function resolveAcademicYearIdForDate(int $year, int $month, array $yearMap): int
    {
        if ($year >= 2020) {
            $startYear = ($month >= 7 || $month === 0) ? $year : ($year - 1);
            $key = "{$startYear}/".($startYear + 1);
            if (isset($yearMap['by_name'][$key])) {
                return $yearMap['by_name'][$key];
            }
        }

        return $yearMap['active_id'];
    }

    protected function buildDueDate(int $year, int $month, int $day, ?string $fallbackCreatedAt): string
    {
        if ($year >= 2020 && $month >= 1 && $month <= 12) {
            $safeDay = max(1, min(28, $day));

            return sprintf('%04d-%02d-%02d', $year, $month, $safeDay);
        }

        return $this->parseSafeDate($fallbackCreatedAt) ?? '2024-07-10';
    }

    protected function importPaymentsAndLedgers(
        PDO $pdo,
        array $unitMap,
        array $studentUnitMap,
        array $studentMap,
        array $billToInvoiceIds,
        array $cashAccountMap,
        array $userMap
    ): int {
        // Build fast lookup maps from spa_bills for exact payment -> bill amount allocations
        $rawBillsStmt = $pdo->query('SELECT bill_id, amount_paid, payment_code, payment_code_x1, payment_code_x2, payment_code_x3, payment_code_x4, payment_code_x5, payment_code_x6, payment_amount_x1, payment_amount_x2, payment_amount_x3, payment_amount_x4, payment_amount_x5, payment_amount_x6 FROM spa_bills');

        $byBillId = [];
        $byPayCode = [];

        while ($b = $rawBillsStmt->fetch()) {
            $bid = (int) $b['bill_id'];
            $byBillId[$bid] = (int) round((float) $b['amount_paid']);

            $codes = [];
            for ($k = 1; $k <= 6; $k++) {
                $c = $b["payment_code_x{$k}"];
                $a = (int) round((float) $b["payment_amount_x{$k}"]);
                if (! empty($c) && $a > 0) {
                    $codes[$c] = ($codes[$c] ?? 0) + $a;
                }
            }

            if (! empty($b['payment_code']) && empty($codes)) {
                $codes[$b['payment_code']] = (int) round((float) $b['amount_paid']);
            }

            foreach ($codes as $c => $amt) {
                $byPayCode[$c][$bid] = $amt;
            }
        }

        // Load only verified completed (LN) bill payments in chronological order
        $paymentStmt = $pdo->query("
            SELECT *
            FROM spa_payments
            WHERE payment_status = 'LN'
              AND (payment_description IS NULL OR payment_description NOT LIKE '%Tabungan%')
            ORDER BY COALESCE(payment_paid_at, created_at, payment_at) ASC, payment_id ASC
        ");

        $accountBalances = [];
        foreach (CashAccount::all() as $ca) {
            $accountBalances[$ca->id] = (int) $ca->current_balance;
        }

        $nowStr = Carbon::now()->toDateTimeString();
        $importedCount = 0;

        DB::transaction(function () use (
            $paymentStmt,
            $unitMap,
            $studentUnitMap,
            $studentMap,
            $byBillId,
            $byPayCode,
            $cashAccountMap,
            $userMap,
            $nowStr,
            &$billToInvoiceIds,
            &$accountBalances,
            &$importedCount
        ): void {
            $paymentItemBatch = [];
            $cashLedgerBatch = [];
            $dirtyInvoices = [];

            while ($p = $paymentStmt->fetch()) {
                $legacyPaymentId = (int) $p['payment_id'];
                $legacyStudentId = (int) $p['student_id'];
                $smartStudentId = $studentMap[$legacyStudentId] ?? null;
                if (! $smartStudentId) {
                    continue;
                }

                $pCode = trim((string) ($p['payment_code'] ?? ''));
                $pAmt = (int) round((float) $p['payment_amount']);

                // Resolve linked legacy bills and amounts
                $allocs = [];
                if ($pCode !== '' && isset($byPayCode[$pCode])) {
                    $allocs = $byPayCode[$pCode];
                } elseif (! empty($p['bill_id']) && isset($byBillId[(int) $p['bill_id']])) {
                    $allocs[(int) $p['bill_id']] = $pAmt;
                } elseif (! empty($p['bill_ids'])) {
                    $decoded = json_decode((string) $p['bill_ids'], true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $bid) {
                            $bid = (int) $bid;
                            if (isset($byBillId[$bid])) {
                                $allocs[$bid] = $byBillId[$bid];
                            }
                        }
                    }
                }

                if (empty($allocs)) {
                    continue;
                }

                // Resolve SmartKas unit_id from first matched invoice -> student -> payment unit
                $smartUnitId = null;
                foreach (array_keys($allocs) as $bid) {
                    if (! empty($billToInvoiceIds[$bid])) {
                        $smartUnitId = $billToInvoiceIds[$bid][0]['unit_id'];
                        break;
                    }
                }
                if (! $smartUnitId) {
                    $legacyUnit = (int) ($p['unit_id'] ?: ($studentUnitMap[$legacyStudentId] ?? 3));
                    $smartUnitId = $unitMap[$legacyUnit] ?? $unitMap[3];
                }

                // Resolve PaymentMethod and target CashAccount
                [$method, $cashAccount] = $this->resolveMethodAndAccount(
                    (string) ($p['payment_method'] ?? 'TN'),
                    (string) ($p['payment_channel'] ?? ''),
                    $smartUnitId,
                    $cashAccountMap
                );

                $paymentDate = $this->parseSafeDateTime($p['payment_paid_at'] ?: ($p['created_at'] ?: $p['payment_at'])) ?? $nowStr;
                $receiptNo = $pCode !== '' ? substr($pCode, 0, 50) : sprintf('KW/LEG/%06d', $legacyPaymentId);
                $creatorId = $this->resolveUserId($p['admin_name'] ?? null, $userMap);

                $refNumber = trim((string) ($p['payment_invoice'] ?: ($p['payment_channel'] ?: '')));

                $paymentId = DB::table('payments')->insertGetId([
                    'unit_id' => $smartUnitId,
                    'student_id' => $smartStudentId,
                    'cash_account_id' => $cashAccount->id,
                    'receipt_number' => $receiptNo,
                    'payment_date' => $paymentDate,
                    'payment_method' => $method->value,
                    'total_amount' => $pAmt,
                    'reference_number' => $refNumber !== '' ? mb_substr($refNumber, 0, 100, 'UTF-8') : null,
                    'status' => PaymentStatus::COMPLETED->value,
                    'created_by' => $creatorId,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);

                // Allocate across SmartKas Invoice(s) waterfall-style
                foreach ($allocs as $legacyBillId => $allocAmount) {
                    if (! isset($billToInvoiceIds[$legacyBillId])) {
                        continue;
                    }

                    $remainingAlloc = $allocAmount;

                    foreach ($billToInvoiceIds[$legacyBillId] as $idx => $inv) {
                        if ($remainingAlloc <= 0) {
                            break;
                        }

                        $invRemaining = max(0, $inv['net_amount'] - $inv['paid_amount']);
                        $apply = min($remainingAlloc, $invRemaining);

                        if ($apply <= 0) {
                            continue;
                        }

                        $paymentItemBatch[] = [
                            'payment_id' => $paymentId,
                            'invoice_id' => $inv['id'],
                            'amount' => $apply,
                            'created_at' => $paymentDate,
                            'updated_at' => $paymentDate,
                        ];

                        $newPaid = $inv['paid_amount'] + $apply;
                        $billToInvoiceIds[$legacyBillId][$idx]['paid_amount'] = $newPaid;
                        $dirtyInvoices[$inv['id']] = [
                            'paid_amount' => $newPaid,
                            'status' => $newPaid >= $inv['net_amount']
                                ? InvoiceStatus::PAID->value
                                : InvoiceStatus::PARTIAL->value,
                        ];

                        $remainingAlloc -= $apply;
                    }
                }

                // Update account running balance and buffer CashLedger entry
                $balanceBefore = $accountBalances[$cashAccount->id];
                $balanceAfter = $balanceBefore + $pAmt;
                $accountBalances[$cashAccount->id] = $balanceAfter;

                $desc = trim((string) ($p['payment_description'] ?? ''));
                $fullDesc = "Pembayaran {$receiptNo}".($desc !== '' ? " - {$desc}" : '');
                $cashLedgerBatch[] = [
                    'unit_id' => $smartUnitId,
                    'cash_account_id' => $cashAccount->id,
                    'transaction_date' => $paymentDate,
                    'type' => LedgerType::IN->value,
                    'amount' => $pAmt,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'reference_type' => Payment::class,
                    'reference_id' => $paymentId,
                    'description' => mb_strimwidth($fullDesc, 0, 255, '...', 'UTF-8'),
                    'created_by' => $creatorId,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ];

                if (count($paymentItemBatch) >= 500) {
                    DB::table('payment_items')->insert($paymentItemBatch);
                    $paymentItemBatch = [];
                }

                if (count($cashLedgerBatch) >= 500) {
                    DB::table('cash_ledgers')->insert($cashLedgerBatch);
                    $cashLedgerBatch = [];
                }

                $importedCount++;
            }

            if ($paymentItemBatch !== []) {
                DB::table('payment_items')->insert($paymentItemBatch);
            }

            if ($cashLedgerBatch !== []) {
                DB::table('cash_ledgers')->insert($cashLedgerBatch);
            }

            foreach ($dirtyInvoices as $invId => $updateVals) {
                DB::table('invoices')->where('id', $invId)->update($updateVals);
            }

            // Persist final balances to CashAccounts
            foreach ($accountBalances as $accountId => $balance) {
                DB::table('cash_accounts')
                    ->where('id', $accountId)
                    ->update(['current_balance' => $balance]);
            }
        });

        return $importedCount;
    }

    /**
     * @return array{0: PaymentMethod, 1: CashAccount}
     */
    protected function resolveMethodAndAccount(string $methodRaw, string $channelRaw, int $smartUnitId, array $cashAccountMap): array
    {
        $channelUpper = strtoupper($channelRaw);

        if ($methodRaw === 'TN') {
            $account = $cashAccountMap['unit_cash'][$smartUnitId] ?? $cashAccountMap['cash_yayasan'];

            return [PaymentMethod::CASH, $account];
        }

        if (str_contains($channelUpper, 'QRIS')) {
            return [PaymentMethod::QRIS, $cashAccountMap['bank_bsi']];
        }

        if (str_contains($channelUpper, 'VIRTUAL')) {
            return [PaymentMethod::VIRTUAL_ACCOUNT, $cashAccountMap['bank_bsi']];
        }

        return [PaymentMethod::TRANSFER, $cashAccountMap['bank_bsi']];
    }

    protected function verifyIntegrity(): void
    {
        $invoicePaid = (int) Invoice::withoutGlobalScope('unit_scope')->sum('paid_amount');
        $invoiceNet = (int) Invoice::withoutGlobalScope('unit_scope')->sum('net_amount');
        $paymentTotal = (int) Payment::withoutGlobalScope('unit_scope')->sum('total_amount');
        $paymentItemsTotal = (int) PaymentItem::sum('amount');
        $cashBalanceTotal = (int) CashAccount::withoutGlobalScope('unit_scope')->sum('current_balance');
        $ledgerTotal = (int) CashLedger::withoutGlobalScope('unit_scope')->sum('amount');

        $this->newLine();
        $this->table(
            ['Metrik Integritas Finansial', 'Nilai (Rp)'],
            [
                ['Total Net Tagihan (Invoices)', 'Rp '.number_format($invoiceNet, 0, ',', '.')],
                ['Total Terbayar (Invoices.paid_amount)', 'Rp '.number_format($invoicePaid, 0, ',', '.')],
                ['Total Sisa Tunggakan', 'Rp '.number_format($invoiceNet - $invoicePaid, 0, ',', '.')],
                ['Total Kwitansi (Payments.total_amount)', 'Rp '.number_format($paymentTotal, 0, ',', '.')],
                ['Total Alokasi (PaymentItems.amount)', 'Rp '.number_format($paymentItemsTotal, 0, ',', '.')],
                ['Total Saldo Rekening Kas & Bank', 'Rp '.number_format($cashBalanceTotal, 0, ',', '.')],
                ['Total Mutasi Buku Kas (CashLedgers)', 'Rp '.number_format($ledgerTotal, 0, ',', '.')],
            ]
        );
    }

    protected function parseSafeDate(?string $date): ?string
    {
        if (empty($date) || str_starts_with($date, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseSafeDateTime(?string $datetime): ?string
    {
        if (empty($datetime) || str_starts_with($datetime, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($datetime)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
