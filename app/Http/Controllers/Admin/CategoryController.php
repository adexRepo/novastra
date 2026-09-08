<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories', ['categories' => Category::withCount('products')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Category::create($data);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        DB::transaction(function () use ($category): void {
            $lockedCategory = Category::query()->lockForUpdate()->findOrFail($category->id);

            if ($lockedCategory->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Kategori hanya dapat dihapus jika tidak memiliki produk.',
                ]);
            }

            $lockedCategory->delete();
        }, 3);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $requestedSlug = $request->input('slug');
        $requestedName = $request->input('name');
        $slugSource = is_string($requestedSlug) && trim($requestedSlug) !== '' ? $requestedSlug : (is_string($requestedName) ? $requestedName : '');

        $request->merge([
            'slug' => Str::slug($slugSource),
        ]);

        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'slug' => ['required', 'string', 'max:120', Rule::unique('categories')->ignore($category?->id)], 'description' => ['nullable', 'string', 'max:1000'], 'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])]]);

        return $data;
    }
}
