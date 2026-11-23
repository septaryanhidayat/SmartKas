<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExpenseRequest extends Model
{
    use BelongsToUnit, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'expense_category_id',
        'cash_account_id',
        'request_number',
        'title',
        'description',
        'expense_type',
        'requested_amount',
        'approved_amount',
        'transaction_date',
        'status',
        'rejection_reason',
        'attachment_path',
        'requested_by',
        'principal_approved_by',
        'principal_approved_at',
        'treasurer_approved_by',
        'treasurer_approved_at',
        'disbursed_by',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'expense_type' => ExpenseType::class,
            'status' => ExpenseStatus::class,
            'requested_amount' => 'integer',
            'approved_amount' => 'integer',
            'transaction_date' => 'date',
            'principal_approved_at' => 'datetime',
            'treasurer_approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['request_number', 'requested_amount', 'approved_amount', 'status', 'rejection_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function principalApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'principal_approved_by');
    }

    public function treasurerApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_approved_by');
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
