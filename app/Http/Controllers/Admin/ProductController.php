<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:150']]);
        $search = trim((string) ($validated['q'] ?? ''));
        $products = Product::with('category')
            ->visible()
            ->when($search, fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->orderByDesc('status')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request, ImageStorageService $storage): RedirectResponse
    {
        $data = $this->validated($request);
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $storage->save($request->file('image'));
                $data['image_path'] = $newImage;
            }

            Product::create($data);
        } catch (RuntimeException) {
            if ($newImage) {
                $storage->delete($newImage);
            }

            return back()->withInput()->withErrors(['image' => 'Gambar tidak valid atau gagal disimpan.']);
        } catch (Throwable $error) {
            if ($newImage) {
                $storage->delete($newImage);
            }

            throw $error;
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        abort_if($product->is_deleted, 404);

        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product, ImageStorageService $storage): RedirectResponse
    {
        abort_if($product->is_deleted, 404);

        $data = $this->validated($request, $product);
        $oldImage = $product->image_path;
        $newImage = null;
        try {
            if ($request->hasFile('image')) {
                $newImage = $storage->save($request->file('image'));
                $data['image_path'] = $newImage;
            }
            DB::transaction(fn () => $product->update($data));
        } catch (RuntimeException) {
            if ($newImage) {
                $storage->delete($newImage);
            }

            return back()->withInput()->withErrors(['image' => 'Gambar tidak valid atau gagal disimpan.']);
        } catch (Throwable $error) {
            if ($newImage) {
                $storage->delete($newImage);
            }

            throw $error;
        }

        if ($newImage) {
            $storage->delete($oldImage);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            if ($lockedProduct->is_deleted) {
                return;
            }

            $lockedProduct->forceFill([
                'is_deleted' => true,
                'status' => 'INACTIVE',
                'featured' => false,
            ])->save();
        }, 3);

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus permanen dari katalog.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $requestedSlug = $request->input('slug');
        $requestedName = $request->input('name');
        $slugSource = is_string($requestedSlug) && trim($requestedSlug) !== '' ? $requestedSlug : (is_string($requestedName) ? $requestedName : '');

        $rawPrice = $request->input('price');
        $normalizedPrice = is_string($rawPrice) && preg_match('/^\s*(?:\d+|\d{1,3}(?:\.\d{3})+)\s*$/', $rawPrice)
            ? str_replace('.', '', trim($rawPrice))
            : $rawPrice;

        $request->merge([
            'slug' => Str::slug($slugSource),
            'price' => $normalizedPrice,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:170', Rule::unique('products')->ignore($product?->id)],
            'sku' => ['required', 'string', 'max:80', Rule::unique('products')->ignore($product?->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'max:2048', 'mimetypes:image/jpeg,image/png,image/webp'],
        ], [
            'slug.required' => 'Nama produk harus memuat huruf atau angka agar alamat produk dapat dibuat.',
        ]);
        $data['featured'] = $request->boolean('featured');
        unset($data['image']);

        return $data;
    }
}
