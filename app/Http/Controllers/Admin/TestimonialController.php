<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::with('feedback')->orderBy('sort_order')->latest()->paginate(25),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $feedback = $request->integer('feedback') ? Feedback::findOrFail($request->integer('feedback')) : null;

        if ($feedback?->testimonial()->exists()) {
            return redirect()->route('admin.testimonials.index')
                ->withErrors(['feedback_id' => 'Feedback ini sudah digunakan sebagai testimoni.']);
        }

        return view('admin.testimonials.form', [
            'testimonial' => new Testimonial([
                'feedback_id' => $feedback?->id,
                'display_name' => $feedback?->name,
                'rating' => $feedback?->rating,
                'content' => $feedback?->message,
                'status' => 'DRAFT',
            ]),
            'sourceFeedback' => $feedback,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni ditambahkan.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.form', [
            'testimonial' => $testimonial,
            'sourceFeedback' => $testimonial->feedback,
        ]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request, $testimonial));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni diperbarui.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni dihapus.');
    }

    private function validated(Request $request, ?Testimonial $testimonial = null): array
    {
        $data = $request->validate([
            'feedback_id' => ['nullable', 'integer', Rule::exists('feedback', 'id'), Rule::unique('testimonials', 'feedback_id')->ignore($testimonial?->id)],
            'display_name' => ['required', 'string', 'max:120'],
            'customer_type' => ['nullable', 'string', 'max:120'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['DRAFT', 'PUBLISHED', 'HIDDEN'])],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        $data['featured'] = $request->boolean('featured') && $data['status'] === 'PUBLISHED';

        return $data;
    }
}
