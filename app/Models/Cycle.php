<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'pond_area_m2', 'seed_count', 'seed_size', 'stocking_date', 'initial_capital', 'target_days'])]
class Cycle extends Model
{
    protected $appends = ['duration_days', 'duration_months', 'days_to_harvest'];

    protected function casts(): array
    {
        return [
            'stocking_date' => 'date:Y-m-d',
            'pond_area_m2' => 'float',
            'harvest_notified_at' => 'datetime',
        ];
    }

    public function harvest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Harvest::class);
    }

    public function feedLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeedLog::class);
    }

    public function waterLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaterLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function durationEnd(): \Carbon\CarbonInterface
    {
        return $this->harvest?->harvest_date ?? now()->startOfDay();
    }

    protected function durationDays(): Attribute
    {
        return Attribute::get(fn () => max((int) $this->stocking_date->diffInDays($this->durationEnd()), 0));
    }

    protected function durationMonths(): Attribute
    {
        return Attribute::get(fn () => intdiv($this->duration_days, 30));
    }

    protected function daysToHarvest(): Attribute
    {
        return Attribute::get(fn () => max($this->target_days - $this->duration_days, 0));
    }
}


