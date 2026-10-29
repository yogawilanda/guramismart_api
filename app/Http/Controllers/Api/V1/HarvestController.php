<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarvestController extends Controller
{
    private function cycle(Request $request, int $id): Cycle
    {
        return $request->user()->cycles()->findOrFail($id);
    }

    private function rules(Cycle $cycle, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return [
            'harvest_date' => [$req, 'date', 'after_or_equal:'.$cycle->stocking_date->toDateString(), 'before_or_equal:today'],
            'total_weight_kg' => [$req, 'numeric', 'gt:0'],
            'total_sales' => [$req, 'integer', 'min:0'],
        ];
    }

    public function show(Request $request, int $cycle)
    {
        return response()->json($this->cycle($request, $cycle)->harvest()->firstOrFail());
    }

    public function store(Request $request, int $cycle)
    {
        $c = $this->cycle($request, $cycle);

        if ($c->harvest()->exists()) {
            return response()->json(['message' => 'Siklus ini sudah memiliki data panen.'], 422);
        }

        $data = $request->validate($this->rules($c));

        $harvest = DB::transaction(function () use ($c, $data) {
            $h = $c->harvest()->create($data);
            $c->forceFill(['status' => 'harvested'])->save();

            return $h;
        });

        return response()->json($harvest, 201);
    }

    public function update(Request $request, int $cycle)
    {
        $c = $this->cycle($request, $cycle);
        $harvest = $c->harvest()->firstOrFail();
        $harvest->update($request->validate($this->rules($c, true)));

        return response()->json($harvest);
    }
}
