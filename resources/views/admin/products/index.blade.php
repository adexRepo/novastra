@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Katalog</p>
            <h1 class="section-title mt-3">Produk.</h1>
        </div>
        <div class="flex items-center gap-2">
            <a
                class="btn-outline size-11 px-0"
                href="{{ route('admin.reports.products') }}"
                title="Unduh Excel"
                aria-label="Unduh semua produk dalam Excel"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <a class="btn" href="{{ route('admin.products.create') }}">Tambah produk</a>
        </div>
    </div>

    <form class="mt-7 flex flex-col gap-2 sm:flex-row" method="get" action="{{ route('admin.products.index') }}">
        <label class="sr-only" for="product-search">Cari produk atau SKU</label>
        <input id="product-search" class="field mt-0 max-w-md" name="q" value="{{ request('q') }}" placeholder="Cari produk atau SKU">
        <div class="flex gap-2">
            <button class="btn-outline" type="submit">Cari</button>
            @if (request('q'))
                <a class="btn-outline" href="{{ route('admin.products.index') }}">Reset</a>
            @endif
        </div>
    </form>

    <div class="card mt-6 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead>
                <tr class="text-ink/50">
                    <th class="p-4">Produk</th>
                    <th>SKU</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t">
                        <td class="p-4 font-semibold">{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td><span class="badge">{{ $product->status }}</span></td>
                        <td><a class="font-semibold text-brand-deep" href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="border-t p-10 text-center text-ink/50">Tidak ada produk yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
