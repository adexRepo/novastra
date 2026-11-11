@extends('layouts.store')

@section('title', 'Kategori')

@section('content')
    <section class="page-shell py-12 sm:py-16">
        <p class="eyebrow">Kategori</p>
        <h1 class="section-title mt-3 text-balance">Belanja sesuai kebutuhan.</h1>
        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="group card overflow-hidden transition-[transform,border-color] duration-150 hover:-translate-y-1 hover:border-brand active:scale-[0.96]">
                    <img class="aspect-[3/2] w-full object-cover outline outline-1 -outline-offset-1 outline-black/10 transition-transform duration-150 group-hover:scale-[1.03]" src="{{ $category->imageUrl() }}" alt="Bahan segar kategori {{ $category->name }}">
                    <div class="p-7">
                        <h2 class="font-display text-3xl text-balance">{{ $category->name }}</h2>
                        <p class="mt-3 text-pretty text-ink/60">{{ $category->description }}</p>
                        <p class="mt-5 text-sm font-semibold tabular-nums">{{ $category->products_count }} produk →</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
