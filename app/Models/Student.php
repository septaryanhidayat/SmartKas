<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\StudentStatus;
use App\Models\Concerns\BelongsToUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToUnit, HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'classroom_id',
        'nis',
        'nisn',
        'name',
        'parent_name',
        'parent_whatsapp',
        'status',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudentStatus::class,
            'enrolled_at' => 'date',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function studentDiscounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate total discount amount (Rupiah) for a specific fee component.
     */
    public function calculateDiscountFor(FeeComponent $component, ?Carbon $date = null): int
    {
        $targetDate = $date ?? Carbon::now();

        $activeDiscounts = $this->studentDiscounts()
            ->where('valid_from', '<=', $targetDate->toDateString())
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $targetDate->toDateString());
            })
            ->where(function ($q) use ($component) {
                $q->whereNull('fee_component_id')
                    ->orWhere('fee_component_id', $component->id);
            })
            ->with('discount')
            ->get();

        $totalDiscount = 0;
        $componentAmount = (int) $component->default_amount;

        foreach ($activeDiscounts as $sd) {
            $discount = $sd->discount;
            if (! $discount || ! $discount->is_active) {
                continue;
            }

            if ($discount->type === DiscountType::PERCENTAGE) {
                $totalDiscount += (int) round(($componentAmount * (int) $discount->value) / 100);
            } else {
                $totalDiscount += (int) $discount->value;
            }
        }

        return min($totalDiscount, $componentAmount);
    }
}
