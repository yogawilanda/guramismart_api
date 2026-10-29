<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\Request;

class WaterLogController extends Controller
{
    private function cycle(Request $request, int $id): Cycle
    {
        return $request->user()->cycles()->findOrFail($id);
    }

    private function rules(Cycle $cycle, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return [
            'recorded_on' => [$req, 'date', 'after_or_equal:'.$cycle->stocking_date->toDateString(), 'before_or_equal:today'],
            'temperature_c' => [$req, 'numeric', 'between:0,45'],
            'ph' => [$req, 'numeric', 'between:0,14'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function index(Request $request, int $cycle)
    {
        return response()->json(
            $this->cycle($request, $cycle)->waterLogs()
                ->when($request->query('from'), fn ($q, $v) => $q->whereDate('recorded_on', '>=', $v))
                ->when($request->query('to'), fn ($q, $v) => $q->whereDate('recorded_on', '<=', $v))
                ->orderByDesc('recorded_on')->paginate(31)
        );
    }

    public function store(Request $request, int $cycle)
    {
        $c = $this->cycle($request, $cycle);

        return response()->json($c->waterLogs()->create($request->validate($this->rules($c))), 201);
    }

    public function update(Request $request, int $cycle, int $water_log)
    {
        $c = $this->cycle($request, $cycle);
        $log = $c->waterLogs()->findOrFail($water_log);
        $log->update($request->validate($this->rules($c, true)));

        return response()->json($log);
    }

    public function destroy(Request $request, int $cycle, int $water_log)
    {
        $this->cycle($request, $cycle)->waterLogs()->findOrFail($water_log)->delete();

        return response()->noContent();
    }
}
