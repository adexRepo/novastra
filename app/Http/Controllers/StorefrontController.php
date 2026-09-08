<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Testimonial;
use App\Services\CompanySettings;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(CompanySettings $settings)
    {
        $company = $settings->all();

        return view('store.home', [
            'featured' => Product::with('category')->active()->where('featured', true)->latest()->limit(4)->get(),
            'categories' => Category::where('status', 'ACTIVE')->withCount(['products' => fn ($query) => $query->active()])->get(),
            'testimonials' => $company['show_testimonials'] === '1'
                ? Testimonial::published()->where('featured', true)->orderBy('sort_order')->latest()->limit(8)->get()
                : collect(),
            'homeFaqs' => $company['show_faq'] === '1'
                ? Faq::active()->where('show_on_home', true)->orderBy('sort_order')->limit(6)->get()
                : collect(),
        ]);
    }

    public function faq(): View
    {
        return view('store.faq', ['faqs' => Faq::active()->orderBy('sort_order')->paginate(20)]);
    }

    public function products(Request $request)
    {
        $products = Product::with('category')->active()
            ->when($request->string('q')->trim()->value(), fn ($query, $q) => $query->where(fn ($inner) => $inner->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->when($request->string('category')->value(), fn ($query, $slug) => $query->whereHas('category', fn ($category) => $category->where('slug', $slug)))
            ->when($request->boolean('available'), fn ($query) => $query->where('stock', '>', 0));

        match ($request->string('sort')->value()) {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            default => $products->latest(),
        };

        return view('store.products.index', ['products' => $products->paginate(12)->withQueryString(), 'categories' => Category::where('status', 'ACTIVE')->get()]);
    }

    public function product(Product $product)
    {
        abort_unless($product->status === 'ACTIVE', 404);
        $product->load('category');

        return view('store.products.show', compact('product'));
    }

    public function categories()
    {
        return view('store.categories.index', ['categories' => Category::where('status', 'ACTIVE')->withCount(['products' => fn ($query) => $query->active()])->get()]);
    }

    public function category(Category $category)
    {
        abort_unless($category->status === 'ACTIVE', 404);

        return view('store.categories.show', ['category' => $category, 'products' => $category->products()->active()->paginate(12)]);
    }
}
