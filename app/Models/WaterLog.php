<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['recorded_on', 'temperature_c', 'ph', 'note'])]
class WaterLog extends Model
{
    protected $appends = ['status'];

    protected function casts(): array
    {
        return ['recorded_on' => 'date:Y-m-d', 'temperature_c' => 'float', 'ph' => 'float'];
    }

    public static function evaluate(float $value, array $range): string
    {
        return $value < $range['min'] ? 'low' : ($value > $range['max'] ? 'high' : 'optimal');
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn () => [
            'temperature' => self::evaluate($this->temperature_c, config('guramismart.water.temperature')),
            'ph' => self::evaluate($this->ph, config('guramismart.water.ph')),
        ]);
    }
}
