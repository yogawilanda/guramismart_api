<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use Illuminate\Http\Request;

class FeedLogController extends Controller
{
    private function cycle(Request $request, int $id): Cycle
    {
        return $request->user()->cycles()->findOrFail($id);
    }

    private function rules(Cycle $cycle, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return [
            'fed_on' => [$req, 'date', 'after_or_equal:'.$cycle->stocking_date->toDateString(), 'before_or_equal:today'],
            'session' => ['nullable', 'in:pagi,sore'],
            'amount_kg' => [$req, 'numeric', 'gt:0', 'max:10000'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function index(Request $request, int $cycle)
    {
        return response()->json(
            $this->cycle($request, $cycle)->feedLogs()
                ->when($request->query('from'), fn ($q, $v) => $q->whereDate('fed_on', '>=', $v))
                ->when($request->query('to'), fn ($q, $v) => $q->whereDate('fed_on', '<=', $v))
                ->orderByDesc('fed_on')->paginate(31)
        );
    }

    public function store(Request $request, int $cycle)
    {
        $c = $this->cycle($request, $cycle);

        return response()->json($c->feedLogs()->create($request->validate($this->rules($c))), 201);
    }

    public function update(Request $request, int $cycle, int $feed_log)
    {
        $c = $this->cycle($request, $cycle);
        $log = $c->feedLogs()->findOrFail($feed_log);
        $log->update($request->validate($this->rules($c, true)));

        return response()->json($log);
    }

    public function destroy(Request $request, int $cycle, int $feed_log)
    {
        $this->cycle($request, $cycle)->feedLogs()->findOrFail($feed_log)->delete();

        return response()->noContent();
    }
}
