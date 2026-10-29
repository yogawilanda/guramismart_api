<?php

namespace App\Services;

use App\Models\Cycle;
use App\Models\User;

class CycleMetricsService
{
    public function forCycle(Cycle $cycle): array
    {
        $harvest = $cycle->harvest;

        $feedTotal = (float) $cycle->feedLogs()->sum('amount_kg');
        $feedDays = $cycle->feedLogs()->distinct()->count('fed_on');

        $waterTrend = $cycle->waterLogs()
            ->selectRaw('recorded_on as date, ROUND(AVG(temperature_c), 1) as temperature_c, ROUND(AVG(ph), 1) as ph')
            ->groupBy('recorded_on')->orderBy('recorded_on')
            ->get()
            ->map(fn ($r) => [
                'date' => substr((string) $r->date, 0, 10),
                'temperature_c' => (float) $r->temperature_c,
                'ph' => (float) $r->ph,
            ]);

        $sales = $harvest?->total_sales;
        $profit = $harvest ? $sales - $cycle->initial_capital : null;

        return [
            'cycle' => [
                'id' => $cycle->id,
                'name' => $cycle->name,
                'status' => $cycle->status,
                'stocking_date' => $cycle->stocking_date->toDateString(),
                'duration_days' => $cycle->duration_days,
                'target_days' => $cycle->target_days,
                'days_to_harvest' => $cycle->days_to_harvest,
            ],
            'finance' => [
                'initial_capital' => $cycle->initial_capital,
                'total_sales' => $sales,
                'profit' => $profit,
                'roi_percent' => $harvest && $cycle->initial_capital > 0
                    ? round($profit / $cycle->initial_capital * 100, 1) : null,
            ],
            'feed' => [
                'total_kg' => round($feedTotal, 2),
                'days_logged' => $feedDays,
                'avg_per_day_kg' => $feedDays ? round($feedTotal / $feedDays, 2) : 0,
            ],
            'productivity' => [
                'pond_area_m2' => $cycle->pond_area_m2,
                'seed_count' => $cycle->seed_count,
                'harvest_weight_kg' => $harvest?->total_weight_kg,
                'kg_per_m2' => $harvest ? round($harvest->total_weight_kg / $cycle->pond_area_m2, 2) : null,
            ],
            'water_trend' => $waterTrend,
        ];
    }

    public function forUser(User $user): array
    {
        $cycles = $user->cycles()->with('harvest')->get();
        $harvested = $cycles->where('status', 'harvested');

        $capital = $harvested->sum('initial_capital');
        $sales = $harvested->sum(fn ($c) => $c->harvest?->total_sales ?? 0);

        return [
            'cycles' => [
                'total' => $cycles->count(),
                'active' => $cycles->where('status', 'active')->count(),
                'harvested' => $harvested->count(),
            ],
            'finance_harvested' => [
                'initial_capital' => $capital,
                'total_sales' => $sales,
                'profit' => $sales - $capital,
            ],
            'active_cycles' => $cycles->where('status', 'active')->values()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'duration_days' => $c->duration_days,
                'days_to_harvest' => $c->days_to_harvest,
            ]),
        ];
    }
}
