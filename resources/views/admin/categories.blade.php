@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <p class="eyebrow">Katalog</p>
    <h1 class="section-title mt-3">Kategori.</h1>

    <div class="mt-8 grid gap-6 xl:grid-cols-[360px_1fr]">
        <form class="card p-5" method="post" action="{{ route('admin.categories.store') }}">
            @csrf
            <h2 class="font-display text-2xl">Kategori baru</h2>
            <label class="mt-5 block">Nama<input class="field" name="name" required></label>
            <label class="mt-4 block">Slug<input class="field" name="slug"></label>
            <label class="mt-4 block">Deskripsi<textarea class="field py-3" name="description"></textarea></label>
            <input type="hidden" name="status" value="ACTIVE">
            <button class="btn mt-5">Tambah</button>
        </form>

        <div class="space-y-3">
            @forelse ($categories as $category)
                <article class="card p-4">
                    <form class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end" method="post" action="{{ route('admin.categories.update', $category) }}">
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
                        <input type="hidden" name="description" value="{{ $category->description }}">
                        <button class="btn-outline">Simpan</button>
                    </form>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t pt-3">
                        <p class="text-xs text-ink/50">{{ $category->products_count }} produk · {{ $category->description }}</p>
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button
                                class="text-sm font-semibold text-red-700 disabled:cursor-not-allowed disabled:text-ink/30"
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
