@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="eyebrow">Katalog</p>
            <h1 class="section-title mt-3">Kategori.</h1>
        </div>
        <p class="text-sm text-ink/50">{{ $categories->count() }} kategori tersedia</p>
    </div>

    <section class="card mt-8 p-5">
        <div>
            <h2 class="font-display text-2xl text-balance">Tambah kategori</h2>
            <p class="mt-1 text-sm leading-6 text-ink/50">Gambar bersifat opsional. Kategori tanpa gambar akan memakai gambar default.</p>
        </div>

        <form class="mt-5 grid gap-4 lg:grid-cols-2 xl:grid-cols-[1fr_1fr_1.4fr_1.4fr_auto] xl:items-end" method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="text-sm font-medium">Nama<input class="field" name="name" value="{{ old('name') }}" required></label>
            <label class="text-sm font-medium">Slug <span class="font-normal text-ink/45">(opsional)</span><input class="field" name="slug" value="{{ old('slug') }}"></label>
            <label class="text-sm font-medium">Deskripsi <span class="font-normal text-ink/45">(opsional)</span><input class="field" name="description" value="{{ old('description') }}"></label>
            <div>
                <span class="block text-sm font-medium">Gambar <span class="font-normal text-ink/45">(opsional)</span></span>
                <label class="btn-outline mt-2 w-full cursor-pointer px-4 active:scale-[.96] transition-transform">
                    <span>Upload gambar</span>
                    <input class="sr-only" name="category_image" type="file" accept="image/jpeg,image/png,image/webp">
                </label>
            </div>
            <input type="hidden" name="status" value="ACTIVE">
            <button class="btn active:scale-[.96] transition-transform" type="submit">Tambah</button>
        </form>
        <p class="mt-3 text-xs leading-5 text-ink/50">Ukuran terbaik 1200 × 800 px (rasio 3:2), format JPG, PNG, atau WebP, maksimal 2 MB.</p>
    </section>

    @foreach ($categories as $category)
        <form id="category-update-{{ $category->id }}" method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
        </form>
    @endforeach

    <section class="card mt-6 overflow-hidden">
        <div class="border-b border-ink/10 px-5 py-4">
            <h2 class="font-display text-2xl text-balance">Tabel kategori</h2>
            <p class="mt-1 text-sm text-ink/50">Edit informasi, ganti gambar, lalu simpan pada baris yang sama.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left text-sm">
                <thead class="bg-sand/55 text-xs uppercase tracking-[.14em] text-ink/50">
                    <tr>
                        <th class="px-5 py-4">Preview</th>
                        <th class="px-4 py-4">Informasi kategori</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-4 py-4">Gambar</th>
                        <th class="px-4 py-4 text-center">Produk</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($categories as $category)
                        @php($formId = "category-update-{$category->id}")
                        <tr class="align-top">
                            <td class="px-5 py-5">
                                <img class="aspect-[3/2] h-20 w-28 rounded-xl object-cover outline outline-1 -outline-offset-1 outline-black/10" src="{{ $category->imageUrl() }}" alt="Gambar kategori {{ $category->name }}">
                                <p class="mt-2 max-w-28 truncate text-xs text-ink/45" title="{{ $category->image_path ? 'Gambar khusus' : 'Gambar default' }}">{{ $category->image_path ? 'Gambar khusus' : 'Gambar default' }}</p>
                            </td>
                            <td class="min-w-80 px-4 py-5">
                                <label class="sr-only" for="category-name-{{ $category->id }}">Nama kategori</label>
                                <input id="category-name-{{ $category->id }}" class="field mt-0 font-medium" form="{{ $formId }}" name="name" value="{{ $category->name }}" required>
                                <div class="mt-2 grid grid-cols-[140px_1fr] gap-2">
                                    <div>
                                        <label class="sr-only" for="category-slug-{{ $category->id }}">Slug kategori</label>
                                        <input id="category-slug-{{ $category->id }}" class="field mt-0" form="{{ $formId }}" name="slug" value="{{ $category->slug }}" placeholder="Slug">
                                    </div>
                                    <div>
                                        <label class="sr-only" for="category-description-{{ $category->id }}">Deskripsi kategori</label>
                                        <input id="category-description-{{ $category->id }}" class="field mt-0" form="{{ $formId }}" name="description" value="{{ $category->description }}" placeholder="Deskripsi singkat">
                                    </div>
                                </div>
                            </td>
                            <td class="w-36 px-4 py-5">
                                <label class="sr-only" for="category-status-{{ $category->id }}">Status kategori</label>
                                <select id="category-status-{{ $category->id }}" class="field mt-0" form="{{ $formId }}" name="status">
                                    <option @selected($category->status === 'ACTIVE')>ACTIVE</option>
                                    <option @selected($category->status === 'INACTIVE')>INACTIVE</option>
                                </select>
                            </td>
                            <td class="w-52 px-4 py-5">
                                <label class="btn-outline w-full cursor-pointer px-4 active:scale-[.96] transition-transform">
                                    <span>Upload gambar</span>
                                    <input class="sr-only" form="{{ $formId }}" name="category_image" type="file" accept="image/jpeg,image/png,image/webp">
                                </label>
                                <p class="mt-2 text-xs leading-5 text-ink/45">Kosongkan untuk mempertahankan gambar saat ini.</p>
                            </td>
                            <td class="px-4 py-5 text-center font-semibold tabular-nums text-ink/65">{{ $category->products_count }}</td>
                            <td class="px-5 py-5">
                                <div class="flex justify-end gap-2">
                                    <button class="btn-outline px-4 active:scale-[.96] transition-transform" form="{{ $formId }}" type="submit">Simpan</button>
                                    <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex min-h-11 items-center rounded-full px-3 text-sm font-semibold text-red-700 transition-[color,background-color,transform] hover:bg-red-50 disabled:cursor-not-allowed disabled:text-ink/30 active:scale-[.96]"
                                            title="{{ $category->products_count ? 'Kategori masih memiliki produk' : 'Hapus kategori' }}"
                                            @disabled($category->products_count > 0)
                                            type="submit"
                                        >Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-12 text-center text-sm text-ink/50" colspan="6">Belum ada kategori. Tambahkan kategori pertama melalui form di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
