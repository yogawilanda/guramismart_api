<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    private function rules(bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return [
            'name' => ['nullable', 'string', 'max:100'],
            'pond_area_m2' => [$req, 'numeric', 'gt:0'],
            'seed_count' => [$req, 'integer', 'min:1'],
            'seed_size' => ['nullable', 'string', 'max:50'],
            'stocking_date' => [$req, 'date', 'before_or_equal:today'],
            'initial_capital' => [$req, 'integer', 'min:0'],
            'target_days' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function index(Request $request)
    {
        return response()->json(
            $request->user()->cycles()
                ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
                ->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['target_days'] ??= config('guramismart.default_target_days');

        return response()->json($request->user()->cycles()->create($data), 201);
    }

    public function show(Request $request, int $cycle)
    {
        return response()->json($request->user()->cycles()->findOrFail($cycle));
    }

    public function update(Request $request, int $cycle)
    {
        $model = $request->user()->cycles()->findOrFail($cycle);

        if ($model->status === 'harvested') {
            return response()->json(['message' => 'Siklus sudah dipanen dan tidak bisa diubah.'], 422);
        }

        $model->update($request->validate($this->rules(true)));

        return response()->json($model);
    }

    public function destroy(Request $request, int $cycle)
    {
        $request->user()->cycles()->findOrFail($cycle)->delete();

        return response()->noContent();
    }
}
