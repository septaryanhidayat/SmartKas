<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
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
        if ($this->isSuperAdmin() || $this->isTreasurer()) {
            return true;
        }

        if (empty($targetUnitId)) {
            return false;
        }

        return (int) $this->unit_id === (int) $targetUnitId;
    }
}
