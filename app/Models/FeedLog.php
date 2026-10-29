<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['fed_on', 'session', 'amount_kg', 'note'])]
class FeedLog extends Model
{
    protected function casts(): array
    {
        return ['fed_on' => 'date:Y-m-d', 'amount_kg' => 'float'];
    }
}
