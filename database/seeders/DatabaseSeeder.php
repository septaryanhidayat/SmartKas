<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillingType;
use App\Enums\CashAccountType;
use App\Enums\DiscountType;
use App\Enums\StudentStatus;
use App\Enums\UnitCode;
use App\Enums\UnitLevel;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\CashAccount;
use App\Models\Classroom;
use App\Models\Discount;
use App\Models\ExpenseCategory;
use App\Models\FeeComponent;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'view-dashboard',
            'view-all-units',
            'manage-units',
            'manage-academic-years',
            'manage-classrooms',
            'manage-students',
            'manage-fee-components',
            'manage-discounts',
            'manage-cash-accounts',
            'generate-invoices',
            'view-invoices',
            'receive-payments',
            'void-payments',
            'submit-expense',
            'approve-expense-principal',
            'approve-expense-treasurer',
            'disburse-expense',
            'view-reports',
            'export-reports',
            'manage-closing-periods',
            'view-audit-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions
        $superadminRole = Role::firstOrCreate(['name' => UserRole::SUPERADMIN->value]);
        $superadminRole->syncPermissions(Permission::all());

        $treasurerRole = Role::firstOrCreate(['name' => UserRole::TREASURER->value]);
        $treasurerRole->syncPermissions([
            'view-dashboard',
            'view-all-units',
            'manage-fee-components',
            'manage-discounts',
            'manage-cash-accounts',
            'view-invoices',
            'receive-payments',
            'void-payments',
            'approve-expense-treasurer',
            'disburse-expense',
            'view-reports',
            'export-reports',
            'manage-closing-periods',
            'view-audit-logs',
        ]);

        $principalRole = Role::firstOrCreate(['name' => UserRole::PRINCIPAL->value]);
        $principalRole->syncPermissions([
            'view-dashboard',
            'view-invoices',
            'approve-expense-principal',
            'view-reports',
        ]);

        $financeAdminRole = Role::firstOrCreate(['name' => UserRole::FINANCE_ADMIN->value]);
        $financeAdminRole->syncPermissions([
            'view-dashboard',
            'manage-classrooms',
            'manage-students',
            'generate-invoices',
            'view-invoices',
            'receive-payments',
            'submit-expense',
            'view-reports',
            'export-reports',
        ]);

        // 3. Create Units
        $yayasan = Unit::create([
            'code' => UnitCode::YAYASAN,
            'name' => 'Yayasan Pendidikan Islam Terpadu',
            'level' => UnitLevel::FOUNDATION,
            'address' => 'Jl. Pendidikan Islam No. 1, Jakarta Selatan',
            'phone' => '021-7890123',
            'principal_name' => 'Dr. H. Ahmad Dahlan, M.Pd.',
            'is_active' => true,
        ]);

        $tkit = Unit::create([
            'code' => UnitCode::TKIT,
            'name' => 'KB/TKIT Nurul Fikri',
            'level' => UnitLevel::TK,
            'address' => 'Jl. Pendidikan Islam No. 1A, Jakarta Selatan',
            'phone' => '021-7890124',
            'principal_name' => 'Ustadzah Siti Aminah, S.Pd.I.',
            'is_active' => true,
        ]);

        $sdit = Unit::create([
            'code' => UnitCode::SDIT,
            'name' => 'SDIT Nurul Fikri',
            'level' => UnitLevel::SD,
            'address' => 'Jl. Pendidikan Islam No. 1B, Jakarta Selatan',
            'phone' => '021-7890125',
            'principal_name' => 'Ustadz Muhammad Ridwan, S.Pd.',
            'is_active' => true,
        ]);

        $smpit = Unit::create([
            'code' => UnitCode::SMPIT,
            'name' => 'SMPIT Nurul Fikri',
            'level' => UnitLevel::SMP,
            'address' => 'Jl. Pendidikan Islam No. 1C, Jakarta Selatan',
            'phone' => '021-7890126',
            'principal_name' => 'Ustadz Abdullah Faqih, M.Pd.',
            'is_active' => true,
        ]);

        $smait = Unit::create([
            'code' => UnitCode::SMAIT,
            'name' => 'SMAIT Nurul Fikri',
            'level' => UnitLevel::SMA,
            'address' => 'Jl. Pendidikan Islam No. 1D, Jakarta Selatan',
            'phone' => '021-7890127',
            'principal_name' => 'Ustadz Hasan Basri, Lc., M.A.',
            'is_active' => true,
        ]);

        // 4. Create Demo Users
        $defaultPassword = Hash::make('password123');

        $superadmin = User::create([
            'unit_id' => null,
            'name' => 'Ketua Yayasan (Superadmin)',
            'email' => 'superadmin@sit.sch.id',
            'password' => $defaultPassword,
            'phone' => '628111000001',
            'is_active' => true,
        ]);
        $superadmin->assignRole($superadminRole);

        $treasurer = User::create([
            'unit_id' => null,
            'name' => 'Hj. Maryam (Bendahara Yayasan)',
            'email' => 'bendahara@sit.sch.id',
            'password' => $defaultPassword,
            'phone' => '628111000002',
            'is_active' => true,
        ]);
        $treasurer->assignRole($treasurerRole);

        // Unit Users (Principal and Finance Admin)
        $unitsConfig = [
            ['unit' => $tkit, 'kepsek_email' => 'kepsek.tkit@sit.sch.id', 'admin_email' => 'admin.tkit@sit.sch.id', 'code' => 'TKIT'],
            ['unit' => $sdit, 'kepsek_email' => 'kepsek.sdit@sit.sch.id', 'admin_email' => 'admin.sdit@sit.sch.id', 'code' => 'SDIT'],
            ['unit' => $smpit, 'kepsek_email' => 'kepsek.smpit@sit.sch.id', 'admin_email' => 'admin.smpit@sit.sch.id', 'code' => 'SMPIT'],
            ['unit' => $smait, 'kepsek_email' => 'kepsek.smait@sit.sch.id', 'admin_email' => 'admin.smait@sit.sch.id', 'code' => 'SMAIT'],
        ];

        foreach ($unitsConfig as $cfg) {
            $u = $cfg['unit'];

            $kepsek = User::create([
                'unit_id' => $u->id,
                'name' => "Kepala Sekolah {$cfg['code']}",
                'email' => $cfg['kepsek_email'],
                'password' => $defaultPassword,
                'phone' => "6281200000{$u->id}1",
                'is_active' => true,
            ]);
            $kepsek->assignRole($principalRole);

            $admin = User::create([
                'unit_id' => $u->id,
                'name' => "Admin Keuangan {$cfg['code']}",
                'email' => $cfg['admin_email'],
                'password' => $defaultPassword,
                'phone' => "6281200000{$u->id}2",
                'is_active' => true,
            ]);
            $admin->assignRole($financeAdminRole);
        }

        // 5. Create Academic Year
        $currentYear = (int) date('Y');
        $academicYear = AcademicYear::create([
            'name' => "{$currentYear}/".($currentYear + 1),
            'start_date' => "{$currentYear}-07-01",
            'end_date' => ($currentYear + 1).'-06-30',
            'is_active' => true,
        ]);

        // 6. Create Classrooms
        $classrooms = [
            ['unit' => $tkit, 'name' => 'TK A (Al-Fatih)', 'grade' => 1],
            ['unit' => $tkit, 'name' => 'TK B (Thariq bin Ziyad)', 'grade' => 2],
            ['unit' => $sdit, 'name' => '1A Abu Bakar', 'grade' => 1],
            ['unit' => $sdit, 'name' => '1B Umar bin Khattab', 'grade' => 1],
            ['unit' => $sdit, 'name' => '2A Utsman bin Affan', 'grade' => 2],
            ['unit' => $smpit, 'name' => '7A Salahuddin', 'grade' => 7],
            ['unit' => $smpit, 'name' => '8A Khalid bin Walid', 'grade' => 8],
            ['unit' => $smait, 'name' => '10 MIPA 1', 'grade' => 10],
            ['unit' => $smait, 'name' => '11 MIPA 1', 'grade' => 11],
        ];

        $createdClassrooms = [];
        foreach ($classrooms as $cls) {
            $created = Classroom::create([
                'unit_id' => $cls['unit']->id,
                'academic_year_id' => $academicYear->id,
                'name' => $cls['name'],
                'grade_level' => $cls['grade'],
            ]);
            $createdClassrooms[$cls['unit']->code->value.'_'.$cls['name']] = $created;
        }

        // 7. Create Fee Components
        $components = [
            // TKIT
            ['unit' => $tkit, 'code' => 'SPP-TKIT', 'name' => 'SPP Bulanan TKIT', 'type' => BillingType::MONTHLY, 'amount' => 450000],
            ['unit' => $tkit, 'code' => 'PANGKAL-TKIT', 'name' => 'Uang Pangkal Siswa Baru TKIT', 'type' => BillingType::INCIDENTAL, 'amount' => 5000000],
            ['unit' => $tkit, 'code' => 'SERAGAM-TKIT', 'name' => 'Seragam & Perlengkapan TKIT', 'type' => BillingType::ANNUAL, 'amount' => 1200000],

            // SDIT
            ['unit' => $sdit, 'code' => 'SPP-SDIT', 'name' => 'SPP Bulanan SDIT', 'type' => BillingType::MONTHLY, 'amount' => 750000],
            ['unit' => $sdit, 'code' => 'KATERING-SDIT', 'name' => 'Katering Makan Siang SDIT', 'type' => BillingType::MONTHLY, 'amount' => 350000],
            ['unit' => $sdit, 'code' => 'PANGKAL-SDIT', 'name' => 'Uang Gedung & Pangkal SDIT', 'type' => BillingType::INCIDENTAL, 'amount' => 8500000],
            ['unit' => $sdit, 'code' => 'DAFTAR_ULANG-SDIT', 'name' => 'Daftar Ulang Tahunan SDIT', 'type' => BillingType::ANNUAL, 'amount' => 2000000],

            // SMPIT
            ['unit' => $smpit, 'code' => 'SPP-SMPIT', 'name' => 'SPP Bulanan SMPIT', 'type' => BillingType::MONTHLY, 'amount' => 950000],
            ['unit' => $smpit, 'code' => 'PANGKAL-SMPIT', 'name' => 'Uang Pangkal SMPIT', 'type' => BillingType::INCIDENTAL, 'amount' => 10000000],

            // SMAIT
            ['unit' => $smait, 'code' => 'SPP-SMAIT', 'name' => 'SPP Bulanan SMAIT', 'type' => BillingType::MONTHLY, 'amount' => 1200000],
            ['unit' => $smait, 'code' => 'PANGKAL-SMAIT', 'name' => 'Uang Pangkal SMAIT', 'type' => BillingType::INCIDENTAL, 'amount' => 12500000],
        ];

        $createdComponents = [];
        foreach ($components as $comp) {
            $c = FeeComponent::create([
                'unit_id' => $comp['unit']->id,
                'academic_year_id' => $academicYear->id,
                'code' => $comp['code'],
                'name' => $comp['name'],
                'billing_type' => $comp['type'],
                'default_amount' => $comp['amount'],
                'is_active' => true,
            ]);
            $createdComponents[$comp['code']] = $c;
        }

        // 8. Create Discounts
        $beasiswaYatim = Discount::create([
            'unit_id' => null, // Berlaku seluruh unit
            'name' => 'Beasiswa Yatim/Piatu 100%',
            'type' => DiscountType::PERCENTAGE,
            'value' => 100,
            'is_active' => true,
        ]);

        $diskonAnakGuru = Discount::create([
            'unit_id' => null,
            'name' => 'Diskon Anak Guru 50%',
            'type' => DiscountType::PERCENTAGE,
            'value' => 50,
            'is_active' => true,
        ]);

        $diskonSaudara = Discount::create([
            'unit_id' => null,
            'name' => 'Diskon Saudara Kandung Rp 100.000',
            'type' => DiscountType::FIXED,
            'value' => 100000,
            'is_active' => true,
        ]);

        // 9. Create Cash Accounts
        $cashAccounts = [
            // Yayasan Central Accounts
            ['unit_id' => null, 'name' => 'BSI Rekening Yayasan Operasional', 'type' => CashAccountType::BANK, 'acc' => '7142000101', 'bank' => 'Bank Syariah Indonesia (BSI)', 'balance' => 250000000],
            ['unit_id' => null, 'name' => 'Bank Muamalat Yayasan Penampung', 'type' => CashAccountType::BANK, 'acc' => '3010099881', 'bank' => 'Bank Muamalat', 'balance' => 150000000],
            ['unit_id' => null, 'name' => 'Brankas Kas Tunai Yayasan', 'type' => CashAccountType::CASH, 'acc' => null, 'bank' => null, 'balance' => 20000000],

            // TKIT
            ['unit_id' => $tkit->id, 'name' => 'Kasir Tunai TKIT', 'type' => CashAccountType::CASH, 'acc' => null, 'bank' => null, 'balance' => 5000000],
            ['unit_id' => $tkit->id, 'name' => 'Petty Cash TKIT', 'type' => CashAccountType::PETTY_CASH, 'acc' => null, 'bank' => null, 'balance' => 3000000],

            // SDIT
            ['unit_id' => $sdit->id, 'name' => 'Kasir Tunai SDIT', 'type' => CashAccountType::CASH, 'acc' => null, 'bank' => null, 'balance' => 12000000],
            ['unit_id' => $sdit->id, 'name' => 'Petty Cash SDIT', 'type' => CashAccountType::PETTY_CASH, 'acc' => null, 'bank' => null, 'balance' => 5000000],

            // SMPIT
            ['unit_id' => $smpit->id, 'name' => 'Kasir Tunai SMPIT', 'type' => CashAccountType::CASH, 'acc' => null, 'bank' => null, 'balance' => 8000000],
            ['unit_id' => $smpit->id, 'name' => 'Petty Cash SMPIT', 'type' => CashAccountType::PETTY_CASH, 'acc' => null, 'bank' => null, 'balance' => 4000000],

            // SMAIT
            ['unit_id' => $smait->id, 'name' => 'Kasir Tunai SMAIT', 'type' => CashAccountType::CASH, 'acc' => null, 'bank' => null, 'balance' => 10000000],
            ['unit_id' => $smait->id, 'name' => 'Petty Cash SMAIT', 'type' => CashAccountType::PETTY_CASH, 'acc' => null, 'bank' => null, 'balance' => 5000000],
        ];

        foreach ($cashAccounts as $ca) {
            CashAccount::create([
                'unit_id' => $ca['unit_id'],
                'name' => $ca['name'],
                'type' => $ca['type'],
                'account_number' => $ca['acc'],
                'bank_name' => $ca['bank'],
                'current_balance' => $ca['balance'],
                'is_active' => true,
            ]);
        }

        // 10. Create Expense Categories
        $categories = [
            ['code' => 'LISTRIK_AIR_WIFI', 'name' => 'Listrik, Air, & Internet'],
            ['code' => 'GAJI_HONOR', 'name' => 'Gaji & Honor Guru/Karyawan'],
            ['code' => 'ATK_OPERASIONAL', 'name' => 'ATK & Perlengkapan Kantor'],
            ['code' => 'KEGIATAN_SISWA', 'name' => 'Kegiatan Siswa, PHBI & Outing'],
            ['code' => 'PEMELIHARAAN', 'name' => 'Pemeliharaan Gedung & Sarana Prasarana'],
            ['code' => 'KONSUMSI', 'name' => 'Konsumsi Rapat & Pelatihan Guru'],
            ['code' => 'PETTY_CASH_TOPUP', 'name' => 'Pengisian Kas Kecil (Petty Cash)'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::create([
                'code' => $cat['code'],
                'name' => $cat['name'],
                'is_active' => true,
            ]);
        }

        // 11. Create Sample Students
        $studentsData = [
            // TKIT
            [
                'unit' => $tkit,
                'classroom' => $createdClassrooms['TKIT_TK A (Al-Fatih)'],
                'nis' => 'TK26001',
                'nisn' => '315001001',
                'name' => 'Muhammad Rayyan Al-Ghifari',
                'parent_name' => 'Bambang Sudarmono',
                'parent_whatsapp' => '6281234567890',
                'status' => StudentStatus::ACTIVE,
                'discount' => null,
            ],
            [
                'unit' => $tkit,
                'classroom' => $createdClassrooms['TKIT_TK A (Al-Fatih)'],
                'nis' => 'TK26002',
                'nisn' => '315001002',
                'name' => 'Aisyah Humaira',
                'parent_name' => 'Ahmad Suhendra',
                'parent_whatsapp' => '6281234567891',
                'status' => StudentStatus::ACTIVE,
                'discount' => $diskonAnakGuru,
            ],

            // SDIT
            [
                'unit' => $sdit,
                'classroom' => $createdClassrooms['SDIT_1A Abu Bakar'],
                'nis' => 'SD26001',
                'nisn' => '312001001',
                'name' => 'Fathir Zaidan Al-Ayyubi',
                'parent_name' => 'H. Hendra Gunawan',
                'parent_whatsapp' => '6281234567892',
                'status' => StudentStatus::ACTIVE,
                'discount' => null,
            ],
            [
                'unit' => $sdit,
                'classroom' => $createdClassrooms['SDIT_1A Abu Bakar'],
                'nis' => 'SD26002',
                'nisn' => '312001002',
                'name' => 'Bilqis Adzra Nafisah',
                'parent_name' => 'Drs. Supriyanto',
                'parent_whatsapp' => '6281234567893',
                'status' => StudentStatus::ACTIVE,
                'discount' => $beasiswaYatim,
            ],
            [
                'unit' => $sdit,
                'classroom' => $createdClassrooms['SDIT_1B Umar bin Khattab'],
                'nis' => 'SD26003',
                'nisn' => '312001003',
                'name' => 'Kenzo Malik Ar-Rasyid',
                'parent_name' => 'Rudi Hartono, S.T.',
                'parent_whatsapp' => '6281234567894',
                'status' => StudentStatus::ACTIVE,
                'discount' => $diskonSaudara,
            ],

            // SMPIT
            [
                'unit' => $smpit,
                'classroom' => $createdClassrooms['SMPIT_7A Salahuddin'],
                'nis' => 'SMP26001',
                'nisn' => '309001001',
                'name' => 'Izzuddin Al-Qassam',
                'parent_name' => 'dr. Faisal Rahman, Sp.A',
                'parent_whatsapp' => '6281234567895',
                'status' => StudentStatus::ACTIVE,
                'discount' => null,
            ],

            // SMAIT
            [
                'unit' => $smait,
                'classroom' => $createdClassrooms['SMAIT_10 MIPA 1'],
                'nis' => 'SMA26001',
                'nisn' => '306001001',
                'name' => 'Nayla Khansa Mumtazah',
                'parent_name' => 'Ir. Bambang Trihatmojo',
                'parent_whatsapp' => '6281234567896',
                'status' => StudentStatus::ACTIVE,
                'discount' => null,
            ],
        ];

        foreach ($studentsData as $st) {
            $student = Student::create([
                'unit_id' => $st['unit']->id,
                'classroom_id' => $st['classroom']->id,
                'nis' => $st['nis'],
                'nisn' => $st['nisn'],
                'name' => $st['name'],
                'parent_name' => $st['parent_name'],
                'parent_whatsapp' => $st['parent_whatsapp'],
                'status' => $st['status'],
                'enrolled_at' => Carbon::now()->subMonths(3)->toDateString(),
            ]);

            if ($st['discount']) {
                StudentDiscount::create([
                    'student_id' => $student->id,
                    'discount_id' => $st['discount']->id,
                    'fee_component_id' => null, // applies to all components
                    'valid_from' => Carbon::now()->startOfYear()->toDateString(),
                    'valid_until' => Carbon::now()->addYears(2)->toDateString(),
                    'notes' => 'Penerima '.$st['discount']->name,
                ]);
            }
        }
    }
}
