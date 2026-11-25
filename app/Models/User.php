<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'unit_id', 'is_active', 'phone'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPERADMIN->value);
    }

    public function isTreasurer(): bool
    {
        return $this->hasRole(UserRole::TREASURER->value);
    }

    public function isPrincipal(): bool
    {
        return $this->hasRole(UserRole::PRINCIPAL->value);
    }

    public function isFinanceAdmin(): bool
    {
        return $this->hasRole(UserRole::FINANCE_ADMIN->value);
    }

    public function canAccessUnit(?int $targetUnitId): bool
    {
        if ($this->isSuperAdmin() || $this->isTreasurer() || $this->can('view-all-units')) {
            return true;
        }

        if (empty($targetUnitId)) {
            return false;
        }

        return (int) $this->unit_id === (int) $targetUnitId;
    }

    /**
     * Human-readable institutional title for UI badges and greetings.
     */
    public function displayRoleTitle(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Ketua Yayasan';
        }

        if ($this->isTreasurer()) {
            return 'Bendahara Yayasan';
        }

        if ($this->isPrincipal()) {
            return $this->unit ? "Kepala Sekolah {$this->unit->code->value}" : 'Kepala Sekolah';
        }

        if ($this->isFinanceAdmin()) {
            if (! $this->unit_id) {
                return 'Admin Yayasan (Pusat Pembayaran)';
            }

            return "Admin Keuangan {$this->unit->code->value}";
        }

        return $this->roles->first()?->name ?? 'Pengguna';
    }

    /**
     * Descriptive role subtitle for executive dashboard header.
     */
    public function displayRoleDescription(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Ketua Yayasan Generasi Robbani • Pengawas Utama 4 Unit Sekolah';
        }

        if ($this->isTreasurer()) {
            return 'Bendahara Yayasan • Otorisator Pengeluaran & Kas Terpusat';
        }

        if ($this->isPrincipal()) {
            return $this->unit ? "Kepala Sekolah • {$this->unit->name}" : 'Kepala Sekolah Unit';
        }

        if ($this->isFinanceAdmin()) {
            if (! $this->unit_id) {
                return 'Admin Yayasan • Pusat Pembayaran Siswa Seluruh Unit';
            }

            return "Admin Keuangan • {$this->unit->name}";
        }

        return $this->unit?->name ?? 'Yayasan Generasi Robbani Sumatera Selatan';
    }
}
