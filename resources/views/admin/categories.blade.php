@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <p class="eyebrow">Katalog</p>
    <h1 class="section-title mt-3">Kategori.</h1>

    <div class="mt-8 grid gap-6 xl:grid-cols-[360px_1fr]">
        <form class="card p-5" method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <h2 class="font-display text-2xl">Kategori baru</h2>
            <label class="mt-5 block">Nama<input class="field" name="name" value="{{ old('name') }}" required></label>
            <label class="mt-4 block">Slug<input class="field" name="slug" value="{{ old('slug') }}"></label>
            <label class="mt-4 block">Deskripsi<textarea class="field py-3" name="description">{{ old('description') }}</textarea></label>
            <label class="mt-4 block">
                Gambar kategori <span class="font-normal text-ink/45">(opsional)</span>
                <input class="field file:mr-3 file:rounded-full file:border-0 file:bg-sand file:px-3 file:py-2 file:font-semibold" name="category_image" type="file" accept="image/jpeg,image/png,image/webp">
                <span class="mt-2 block text-xs leading-5 text-ink/50">Ukuran terbaik 1200 × 800 px (rasio 3:2), format JPG, PNG, atau WebP, maksimal 2 MB. Jika kosong, gambar default akan digunakan.</span>
            </label>
            <input type="hidden" name="status" value="ACTIVE">
            <button class="btn mt-5">Tambah</button>
        </form>

        <div class="space-y-3">
            @forelse ($categories as $category)
                <article class="card overflow-hidden">
                    <div class="grid sm:grid-cols-[180px_1fr]">
                        <img class="aspect-[3/2] h-full min-h-36 w-full object-cover outline outline-1 -outline-offset-1 outline-black/10" src="{{ $category->imageUrl() }}" alt="Gambar kategori {{ $category->name }}">
                        <form class="grid gap-3 p-4 sm:grid-cols-2" method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <label>Nama<input class="field" name="name" value="{{ $category->name }}" required></label>
                            <label>Slug<input class="field" name="slug" value="{{ $category->slug }}"></label>
                            <label>
                                Status
                                <select class="field" name="status">
                                    <option @selected($category->status === 'ACTIVE')>ACTIVE</option>
                                    <option @selected($category->status === 'INACTIVE')>INACTIVE</option>
                                </select>
                            </label>
                            <label class="sm:col-span-2">Deskripsi<textarea class="field py-3" name="description">{{ $category->description }}</textarea></label>
                            <label class="sm:col-span-2">
                                Ganti gambar <span class="font-normal text-ink/45">(opsional)</span>
                                <input class="field file:mr-3 file:rounded-full file:border-0 file:bg-sand file:px-3 file:py-2 file:font-semibold" name="category_image" type="file" accept="image/jpeg,image/png,image/webp">
                                <span class="mt-2 block text-xs leading-5 text-ink/50">Disarankan 1200 × 800 px (3:2), maksimal 2 MB. Kosongkan untuk mempertahankan gambar saat ini.</span>
                            </label>
                            <div class="sm:col-span-2"><button class="btn-outline">Simpan perubahan</button></div>
                        </form>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t pt-3">
                        <p class="px-4 pb-3 text-xs text-ink/50">{{ $category->products_count }} produk</p>
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button
                                class="mr-4 mb-3 min-h-10 text-sm font-semibold text-red-700 disabled:cursor-not-allowed disabled:text-ink/30"
                                title="{{ $category->products_count ? 'Kategori masih memiliki produk' : 'Hapus kategori' }}"
                                @disabled($category->products_count > 0)
                            >
                                Hapus
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="card p-10 text-center text-sm text-ink/50">Belum ada kategori.</div>
            @endforelse
        </div>
    </div>
@endsection
