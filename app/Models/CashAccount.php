<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CashAccountType;
use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAccount extends Model
{
    use BelongsToUnit, HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'type',
        'account_number',
        'bank_name',
        'current_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CashAccountType::class,
            'current_balance' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenseRequests(): HasMany
    {
        return $this->hasMany(ExpenseRequest::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(CashLedger::class);
    }
}
