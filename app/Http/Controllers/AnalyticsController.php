<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['path' => ['required', 'string', 'max:200'], 'visitor_id' => ['required', 'string', 'max:64']]);
        if (! str_starts_with($data['path'], '/admin')) {
            PageView::firstOrCreate(['path' => $data['path'], 'visitor_id' => hash('sha256', $data['visitor_id']), 'day' => today()]);
        }

        return response()->noContent();
    }
}
