<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CompanySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(CompanySettings $settings): View
    {
        return view('admin.settings.index', [
            'definitions' => $settings->definitions(),
            'values' => $settings->all(),
        ]);
    }

    public function update(Request $request, CompanySettings $settings): RedirectResponse
    {
        $definitions = $settings->definitions();
        $rules = [
            'settings' => ['required', 'array:'.implode(',', array_keys($definitions))],
        ];

        foreach ($definitions as $key => $definition) {
            $rules["settings.{$key}"] = $definition['rules'];
        }

        $validated = $request->validate($rules);
        $settings->update($validated['settings']);

        return back()->with('success', 'Informasi situs berhasil diperbarui.');
    }

    public function reset(CompanySettings $settings): RedirectResponse
    {
        $settings->reset();

        return back()->with('success', 'Informasi situs dikembalikan ke nilai default ENV.');
    }
}
