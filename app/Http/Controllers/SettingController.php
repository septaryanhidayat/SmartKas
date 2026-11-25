<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UnitCode;
use App\Enums\UserRole;
use App\Models\Unit;
use App\Models\User;
use App\Services\AppSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    public function index(Request $request, AppSettingService $settingService): View
    {
        $activeTab = (string) $request->query('tab', 'general');
        if (! in_array($activeTab, ['general', 'invoice', 'appearance', 'units', 'users'], true)) {
            $activeTab = 'general';
        }

        $settings = $settingService->all();
        $fontOptions = AppSettingService::FONT_OPTIONS;
        $fontSizes = AppSettingService::FONT_SIZES;
        $themeColors = AppSettingService::THEME_COLORS;

        $units = Unit::orderBy('id')->get();
        $schoolUnits = $units->filter(fn (Unit $u) => $u->code !== UnitCode::YAYASAN);
        $users = User::with(['unit', 'roles'])->orderBy('id')->get();
        $roles = Role::orderBy('id')->get();

        return view('settings.index', compact(
            'activeTab',
            'settings',
            'fontOptions',
            'fontSizes',
            'themeColors',
            'units',
            'schoolUnits',
            'users',
            'roles'
        ));
    }

    public function updateGeneral(Request $request, AppSettingService $settingService): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:60'],
            'app_tagline' => ['required', 'string', 'max:120'],
            'foundation_name' => ['required', 'string', 'max:150'],
            'foundation_short_name' => ['required', 'string', 'max:80'],
            'foundation_address' => ['required', 'string', 'max:300'],
            'foundation_phone' => ['nullable', 'string', 'max:40'],
            'foundation_email' => ['nullable', 'email', 'max:120'],
            'academic_year_label' => ['required', 'string', 'max:20'],
            'sync_unit_addresses' => ['nullable', 'boolean'],
            'app_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'app_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'foundation_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $updates = [
            'app_name' => $validated['app_name'],
            'app_tagline' => $validated['app_tagline'],
            'foundation_name' => $validated['foundation_name'],
            'foundation_short_name' => $validated['foundation_short_name'],
            'foundation_address' => $validated['foundation_address'],
            'foundation_phone' => $validated['foundation_phone'] ?? '',
            'foundation_email' => $validated['foundation_email'] ?? '',
            'academic_year_label' => $validated['academic_year_label'],
        ];

        $logoMap = [
            'app_logo' => 'app_logo_path',
            'app_icon' => 'app_icon_path',
            'foundation_logo' => 'foundation_logo_path',
        ];

        foreach ($logoMap as $inputKey => $settingKey) {
            if ($request->hasFile($inputKey)) {
                $updates[$settingKey] = $this->storeBrandingUpload(
                    $request->file($inputKey),
                    $settingKey,
                    $settingService->get($settingKey)
                );
            }
        }

        $settingService->setMany($updates, 'general');

        if ($request->boolean('sync_unit_addresses')) {
            DB::transaction(function () use ($validated): void {
                Unit::query()->update(['address' => $validated['foundation_address']]);
            });
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['updated_keys' => array_keys($updates)])
            ->log('Memperbarui pengaturan identitas, alamat kop & logo aplikasi.');

        return redirect()
            ->route('settings.index', ['tab' => 'general'])
            ->with('success', 'Identitas aplikasi, alamat kop, dan logo berhasil diperbarui.');
    }

    public function updateInvoice(Request $request, AppSettingService $settingService): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_prefix' => ['required', 'alpha_dash', 'max:12'],
            'receipt_prefix' => ['required', 'alpha_dash', 'max:12'],
            'default_due_day' => ['required', 'integer', 'between:1,28'],
            'code_display_mode' => ['required', Rule::in(['compact', 'full'])],
            'show_foundation_logo_on_pdf' => ['nullable', 'boolean'],
            'invoice_footer_note' => ['nullable', 'string', 'max:500'],
            'receipt_signer_title' => ['required', 'string', 'max:100'],
        ]);

        $settingService->setMany([
            'invoice_prefix' => strtoupper($validated['invoice_prefix']),
            'receipt_prefix' => strtoupper($validated['receipt_prefix']),
            'default_due_day' => (string) $validated['default_due_day'],
            'code_display_mode' => $validated['code_display_mode'],
            'show_foundation_logo_on_pdf' => $request->boolean('show_foundation_logo_on_pdf') ? '1' : '0',
            'invoice_footer_note' => $validated['invoice_footer_note'] ?? '',
            'receipt_signer_title' => $validated['receipt_signer_title'],
        ], 'invoice');

        activity()
            ->causedBy(auth()->user())
            ->log('Memperbarui pengaturan format invoice, kwitansi & kop PDF.');

        return redirect()
            ->route('settings.index', ['tab' => 'invoice'])
            ->with('success', 'Pengaturan invoice, kwitansi, dan tampilan kode transaksi berhasil disimpan.');
    }

    public function updateAppearance(Request $request, AppSettingService $settingService): RedirectResponse
    {
        $validated = $request->validate([
            'font_family' => ['required', Rule::in(array_keys(AppSettingService::FONT_OPTIONS))],
            'font_size' => ['required', Rule::in(array_keys(AppSettingService::FONT_SIZES))],
            'table_density' => ['required', Rule::in(['comfortable', 'compact'])],
            'theme_color' => ['required', Rule::in(array_keys(AppSettingService::THEME_COLORS))],
        ]);

        $settingService->setMany($validated, 'appearance');

        activity()
            ->causedBy(auth()->user())
            ->withProperties($validated)
            ->log('Memperbarui pengaturan jenis huruf, ukuran huruf & tema tampilan.');

        return redirect()
            ->route('settings.index', ['tab' => 'appearance'])
            ->with('success', 'Pengaturan jenis huruf, ukuran huruf, dan tampilan berhasil diterapkan.');
    }

    public function updateUnit(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'principal_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = $unit->code === UnitCode::YAYASAN ? true : $request->boolean('is_active');

        DB::transaction(function () use ($unit, $validated, $isActive): void {
            $unit->update([
                'name' => $validated['name'],
                'principal_name' => $validated['principal_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_active' => $isActive,
            ]);
        });

        return redirect()
            ->route('settings.index', ['tab' => 'units'])
            ->with('success', "Data unit {$unit->name} berhasil diperbarui.");
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = DB::transaction(function () use ($validated, $request): User {
            $created = User::create([
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'unit_id' => $validated['unit_id'] ?: null,
                'password' => Hash::make($validated['password']),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $created->syncRoles([$validated['role']]);

            return $created;
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Menambahkan akun admin/pengguna baru: {$user->name} ({$user->email}).");

        return redirect()
            ->route('settings.index', ['tab' => 'users'])
            ->with('success', "Akun {$user->name} berhasil ditambahkan.");
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $willBeActive = $request->boolean('is_active');
        $newRole = $validated['role'];

        if ($user->id === auth()->id() && (! $willBeActive || ! $user->hasRole($newRole))) {
            return redirect()
                ->route('settings.index', ['tab' => 'users'])
                ->with('error', 'Anda tidak dapat menonaktifkan atau menurunkan hak akses akun sendiri yang sedang aktif login.');
        }

        if ($this->wouldRemoveLastSuperadmin($user, $newRole, $willBeActive)) {
            return redirect()
                ->route('settings.index', ['tab' => 'users'])
                ->with('error', 'Sistem wajib menyisakan minimal 1 akun Ketua Yayasan (Superadmin) aktif.');
        }

        DB::transaction(function () use ($user, $validated, $willBeActive, $newRole): void {
            $payload = [
                'name' => $validated['name'],
                'email' => strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'unit_id' => $validated['unit_id'] ?: null,
                'is_active' => $willBeActive,
            ];

            if (! empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $user->update($payload);
            $user->syncRoles([$newRole]);
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Memperbarui profil & hak akses akun: {$user->name} ({$user->email}).");

        return redirect()
            ->route('settings.index', ['tab' => 'users'])
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('settings.index', ['tab' => 'users'])
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan login.');
        }

        if ($this->wouldRemoveLastSuperadmin($user, '', false)) {
            return redirect()
                ->route('settings.index', ['tab' => 'users'])
                ->with('error', 'Akun Ketua Yayasan (Superadmin) terakhir tidak dapat dihapus.');
        }

        DB::transaction(function () use ($user): void {
            $user->update(['is_active' => false]);
            $user->delete();
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Menonaktifkan / soft-delete akun pengguna: {$user->name} ({$user->email}).");

        return redirect()
            ->route('settings.index', ['tab' => 'users'])
            ->with('success', "Akun {$user->name} berhasil dinonaktifkan.");
    }

    protected function storeBrandingUpload(UploadedFile $file, string $prefix, ?string $previousPath): string
    {
        $targetDir = public_path('uploads/settings');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = "{$prefix}_".now()->format('YmdHis').'_'.bin2hex(random_bytes(4)).".{$ext}";
        $file->move($targetDir, $filename);

        if ($previousPath && str_starts_with($previousPath, 'uploads/settings/')) {
            $oldFullPath = public_path($previousPath);
            if (is_file($oldFullPath)) {
                @unlink($oldFullPath);
            }
        }

        return "uploads/settings/{$filename}";
    }

    protected function wouldRemoveLastSuperadmin(User $targetUser, string $newRole, bool $willBeActive): bool
    {
        if (! $targetUser->hasRole(UserRole::SUPERADMIN->value)) {
            return false;
        }

        if ($willBeActive && $newRole === UserRole::SUPERADMIN->value) {
            return false;
        }

        return ! User::role(UserRole::SUPERADMIN->value)
            ->where('is_active', true)
            ->where('id', '!=', $targetUser->id)
            ->exists();
    }
}
