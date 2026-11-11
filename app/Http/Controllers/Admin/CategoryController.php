<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories', ['categories' => Category::withCount('products')->orderBy('name')->get()]);
    }

    public function store(Request $request, ImageStorageService $storage): RedirectResponse
    {
        $data = $this->validated($request);
        $newImage = $this->storeImage($request, $storage);

        if ($newImage instanceof RedirectResponse) {
            return $newImage;
        }

        if ($newImage) {
            $data['image_path'] = $newImage;
        }

        try {
            Category::create($data);
        } catch (Throwable $error) {
            $storage->delete($newImage);
            throw $error;
        }

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category, ImageStorageService $storage): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $oldImage = $category->image_path;
        $newImage = $this->storeImage($request, $storage);

        if ($newImage instanceof RedirectResponse) {
            return $newImage;
        }

        if ($newImage) {
            $data['image_path'] = $newImage;
        }

        try {
            DB::transaction(fn () => $category->update($data));
        } catch (Throwable $error) {
            $storage->delete($newImage);
            throw $error;
        }

        if ($newImage) {
            $storage->delete($oldImage);
        }

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category, ImageStorageService $storage): RedirectResponse
    {
        $imagePath = null;

        DB::transaction(function () use ($category, &$imagePath): void {
            $lockedCategory = Category::query()->lockForUpdate()->findOrFail($category->id);

            if ($lockedCategory->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => 'Kategori hanya dapat dihapus jika tidak memiliki produk.',
                ]);
            }

            $imagePath = $lockedCategory->image_path;
            $lockedCategory->delete();
        }, 3);

        $storage->delete($imagePath);

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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('categories')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'category_image' => ['nullable', 'file', 'max:2048', 'mimetypes:image/jpeg,image/png,image/webp'],
        ]);
        unset($data['category_image']);

        return $data;
    }

    private function storeImage(Request $request, ImageStorageService $storage): string|RedirectResponse|null
    {
        if (! $request->hasFile('category_image')) {
            return null;
        }

        try {
            return $storage->save($request->file('category_image'), 'categories');
        } catch (RuntimeException) {
            return back()->withInput()->withErrors([
                'category_image' => 'Gambar kategori tidak valid atau gagal disimpan.',
            ]);
        }
    }
}
