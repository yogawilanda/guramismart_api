<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CycleMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private CycleMetricsService $metrics) {}

    public function summary(Request $request)
    {
        return response()->json(['data' => $this->metrics->forUser($request->user())]);
    }

    public function cycle(Request $request, int $cycle)
    {
        $model = $request->user()->cycles()->findOrFail($cycle);

        return response()->json(['data' => $this->metrics->forCycle($model)]);
    }
}
