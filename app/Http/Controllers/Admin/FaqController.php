<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', ['faqs' => Faq::orderBy('sort_order')->latest()->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ ditambahkan.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:240'],
            'answer' => ['required', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_home'] = $data['is_active'] && $request->boolean('show_on_home');

        return $data;
    }
}
