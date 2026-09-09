@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <p class="eyebrow">Ekspor</p>
    <h1 class="section-title mt-3">Laporan.</h1>

    <div class="mt-8 grid max-w-5xl gap-5 md:grid-cols-2">
        <section class="card p-6">
            <h2 class="font-display text-2xl">Laporan pesanan</h2>
            <p class="mt-3 text-sm leading-6 text-ink/60">Unduh seluruh pesanan dengan produk, total, status pesanan, dan pembayaran dalam format Excel.</p>
            <a class="btn mt-6" href="{{ route('admin.reports.orders') }}">Unduh laporan pesanan</a>
        </section>

        <section class="card p-6">
            <h2 class="font-display text-2xl">Laporan produk</h2>
            <p class="mt-3 text-sm leading-6 text-ink/60">Unduh katalog produk aktif maupun nonaktif beserta kategori, harga, stok, dan statusnya.</p>
            <a class="btn mt-6" href="{{ route('admin.reports.products') }}">Unduh laporan produk</a>
        </section>
    </div>
@endsection
