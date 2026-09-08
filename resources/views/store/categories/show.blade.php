@extends('layouts.store')
@section('title',$category->name)
@section('content')<section class="page-shell py-12 sm:py-16"><p class="eyebrow">Kategori</p><h1 class="section-title mt-3">{{ $category->name }}</h1><p class="mt-4 max-w-2xl text-ink/60">{{ $category->description }}</p><div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">@forelse($products as $product)<x-product-card :product="$product" />@empty<p>Belum ada produk aktif.</p>@endforelse</div><div class="mt-10">{{ $products->links() }}</div></section>@endsection
