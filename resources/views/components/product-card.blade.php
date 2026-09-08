@php
    $cartProduct = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
        'stock' => $product->stock,
        'image' => $product->image_path,
    ];
@endphp

<article class="group overflow-hidden rounded-2xl bg-white">
    <a href="{{ route('products.show', $product) }}" class="block aspect-[4/3] overflow-hidden bg-sand">
        <img
            src="{{ $product->image_path ?: '/uploads/products/novastra-fresh-collection.webp' }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        >
    </a>
    <div class="p-4">
        <p class="text-xs text-ink/50">{{ $product->category->name }}</p>
        <h3 class="mt-1 font-display text-xl">
            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <div class="mt-4 flex items-end justify-between gap-3">
            <div>
                <p class="font-semibold">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                <p class="text-xs {{ $product->stock ? 'text-ink/50' : 'text-red-700' }}">
                    {{ $product->stock ? "Stok {$product->stock}" : 'Stok habis' }}
                </p>
            </div>
            <button
                class="rounded-full bg-brand px-4 py-2 text-xs font-semibold text-white disabled:opacity-40"
                data-add-cart
                data-product="{{ json_encode($cartProduct, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) }}"
                @disabled(!$product->stock)
            >
                Tambah
            </button>
        </div>
    </div>
</article>
