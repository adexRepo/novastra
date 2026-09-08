@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Produk Baru')

@section('content')
    @php
        $priceValue = old('price', $product->exists ? number_format($product->price, 0, ',', '.') : '');
    @endphp

    <p class="eyebrow">Katalog</p>
    <h1 class="section-title mt-3">{{ $product->exists ? 'Edit produk.' : 'Produk baru.' }}</h1>

    <form
        class="card mt-8 max-w-4xl p-5 sm:p-7"
        method="post"
        enctype="multipart/form-data"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
    >
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <label>Nama<input class="field" name="name" value="{{ old('name', $product->name) }}" required></label>
            <label>SKU<input class="field" name="sku" value="{{ old('sku', $product->sku) }}" required></label>
            <label>Slug<input class="field" name="slug" value="{{ old('slug', $product->slug) }}" placeholder="otomatis-dari-nama"></label>
            <label>
                Kategori
                <select class="field" name="category_id" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Harga (rupiah)
                <span class="relative mt-2 block">
                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-semibold text-ink/50">Rp</span>
                    <input
                        class="field mt-0 pl-10"
                        type="text"
                        inputmode="numeric"
                        name="price"
                        value="{{ $priceValue }}"
                        placeholder="5.000"
                        autocomplete="off"
                        data-currency-input
                        required
                    >
                </span>
            </label>
            <label>Stok<input class="field" type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required></label>
            <label>
                Status
                <select class="field" name="status">
                    <option @selected(old('status', $product->status) === 'ACTIVE')>ACTIVE</option>
                    <option @selected(old('status', $product->status) === 'INACTIVE')>INACTIVE</option>
                </select>
            </label>
            <label>
                Gambar
                <span class="mt-2 flex min-h-28 cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-ink/25 bg-sand/35 px-4 text-center transition hover:border-brand hover:bg-sand/60">
                    <svg class="size-6 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 16V4m0 0-4 4m4-4 4 4M5 15v4h14v-4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="text-sm font-semibold" data-file-name>Pilih gambar produk</span>
                    <span class="text-xs text-ink/50">JPG, PNG, atau WebP · maksimal 2 MB</span>
                    <input class="sr-only" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-file-input>
                </span>
            </label>
            <label class="sm:col-span-2">Deskripsi singkat<input class="field" name="short_description" maxlength="255" value="{{ old('short_description', $product->short_description) }}" required></label>
            <label class="sm:col-span-2">Deskripsi<textarea class="field min-h-32 py-3" name="description" required>{{ old('description', $product->description) }}</textarea></label>
            <label class="flex items-center gap-2"><input type="checkbox" name="featured" value="1" @checked(old('featured', $product->featured))> Produk unggulan</label>
        </div>
        <button class="btn mt-7">Simpan produk</button>
    </form>

    @if ($product->exists)
        <section class="mt-8 max-w-4xl rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-6">
            <h2 class="font-semibold text-red-800">Hapus produk dari katalog</h2>
            <p class="mt-2 text-sm leading-6 text-red-700">Produk tidak akan tampil lagi di toko maupun daftar admin. Histori pesanan tetap tersimpan.</p>
            <form class="mt-4" method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini secara permanen dari katalog?')">
                @csrf
                @method('DELETE')
                <button class="inline-flex min-h-11 items-center justify-center rounded-full bg-red-700 px-6 text-sm font-semibold text-white transition hover:bg-red-800">Hapus permanen</button>
            </form>
        </section>
    @endif
@endsection
