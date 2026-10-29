<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Guide;

class GuideController extends Controller
{
    public function index()
    {
        return response()->json(Guide::orderBy('order')->get(['id', 'slug', 'title', 'category', 'order']));
    }

    public function show(string $slug)
    {
        return response()->json(Guide::where('slug', $slug)->firstOrFail());
    }
}
