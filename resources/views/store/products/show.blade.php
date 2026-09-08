@extends('layouts.store')

@section('title', $product->name)

@section('content')
    @php
        $cartProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => $product->image_path,
        ];
    @endphp

    <section class="page-shell py-10 sm:py-16">
        <a href="{{ route('products.index') }}" class="text-sm text-ink/60">← Kembali ke produk</a>
        <div class="mt-7 grid gap-9 lg:grid-cols-2 lg:gap-16">
            <div class="overflow-hidden rounded-3xl bg-sand">
                <img
                    class="aspect-square h-full w-full object-cover"
                    src="{{ $product->image_path ?: '/uploads/products/novastra-fresh-collection.webp' }}"
                    alt="{{ $product->name }}"
                >
            </div>
            <div class="self-center">
                <p class="eyebrow">{{ $product->category->name }} · {{ $product->sku }}</p>
                <h1 class="mt-4 font-display text-4xl tracking-tight sm:text-6xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl font-semibold">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                <p class="mt-6 leading-7 text-ink/65">{{ $product->description }}</p>
                <div class="mt-8 flex items-center gap-3">
                    <input
                        id="product-quantity"
                        class="field mt-0 w-24"
                        type="number"
                        value="1"
                        min="1"
                        max="{{ max(1, $product->stock) }}"
                    >
                    <button
                        class="btn flex-1 sm:flex-none"
                        data-add-cart
                        data-quantity-target="#product-quantity"
                        data-product="{{ json_encode($cartProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) }}"
                        @disabled(!$product->stock)
                    >
                        Tambah ke keranjang
                    </button>
                </div>
                <p class="mt-3 text-sm {{ $product->stock ? 'text-brand-deep' : 'text-red-700' }}">
                    {{ $product->stock ? "Tersedia {$product->stock} unit" : 'Stok sedang habis' }}
                </p>
            </div>
        </div>
    </section>
@endsection
