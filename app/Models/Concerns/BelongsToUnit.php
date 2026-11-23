<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUnit
{
    public static function bootBelongsToUnit(): void
    {
        static::addGlobalScope('unit_scope', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();

                // If user is tied to a specific unit, scope all queries to that unit
                if (! empty($user->unit_id)) {
                    $builder->where($builder->getModel()->qualifyColumn('unit_id'), $user->unit_id);
                }
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->getAttribute('unit_id')) && auth()->check()) {
                $user = auth()->user();
                if (! empty($user->unit_id)) {
                    $model->setAttribute('unit_id', $user->unit_id);
                }
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeForUnit(Builder $query, ?int $unitId): Builder
    {
        if ($unitId) {
            return $query->where($this->qualifyColumn('unit_id'), $unitId);
        }

        return $query;
    }

    public function scopeWithoutUnitScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('unit_scope');
    }
}
