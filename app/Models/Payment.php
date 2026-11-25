<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payment extends Model
{
    use BelongsToUnit, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'student_id',
        'cash_account_id',
        'receipt_number',
        'payment_date',
        'payment_method',
        'total_amount',
        'reference_number',
        'proof_path',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
        'whatsapp_sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'payment_method' => PaymentMethod::class,
            'total_amount' => 'integer',
            'status' => PaymentStatus::class,
            'voided_at' => 'datetime',
            'whatsapp_sent_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['receipt_number', 'total_amount', 'status', 'void_reason', 'voided_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            PaymentItem::class,
            'payment_id',
            'id',
            'id',
            'invoice_id'
        );
    }

    protected function totalAmountRupiah(): Attribute
    {
        return Attribute::make(
            get: fn () => format_rupiah($this->total_amount)
        );
    }

    protected function terbilangAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => ucwords(terbilang($this->total_amount))
        );
    }
}
