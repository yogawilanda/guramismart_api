<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['harvest_date', 'total_weight_kg', 'total_sales'])]
class Harvest extends Model
{
    protected function casts(): array
    {
        return ['harvest_date' => 'date:Y-m-d', 'total_weight_kg' => 'float'];
    }
}
