<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function __invoke()
    {
        return view('admin.feedback', ['feedback' => Feedback::with('order')->latest()->paginate(20)]);
    }
}
