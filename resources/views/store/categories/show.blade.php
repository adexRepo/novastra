@extends('layouts.store')

@section('title', $category->name)

@section('content')
    <section class="page-shell py-12 sm:py-16">
        <div class="grid overflow-hidden rounded-3xl bg-ink text-white lg:grid-cols-[1fr_1.15fr]">
            <img class="aspect-[3/2] h-full min-h-64 w-full object-cover opacity-85 outline outline-1 -outline-offset-1 outline-white/10" src="{{ $category->imageUrl() }}" alt="Bahan segar kategori {{ $category->name }}">
            <div class="flex flex-col justify-end p-7 sm:p-10 lg:p-12">
                <p class="eyebrow text-brand">Kategori</p>
                <h1 class="mt-3 font-display text-4xl text-balance sm:text-5xl">{{ $category->name }}</h1>
                <p class="mt-4 max-w-2xl text-pretty leading-7 text-white/65">{{ $category->description }}</p>
            </div>
        </div>
        <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p>Belum ada produk aktif.</p>
            @endforelse
        </div>
        <div class="mt-10">{{ $products->links() }}</div>
    </section>
@endsection
