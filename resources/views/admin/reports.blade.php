@extends('layouts.admin')
@section('title','Laporan')
@section('content')<p class="eyebrow">Ekspor</p><h1 class="section-title mt-3">Laporan.</h1><div class="card mt-8 max-w-xl p-6"><h2 class="font-display text-2xl">Laporan pesanan</h2><p class="mt-3 text-sm leading-6 text-ink/60">Unduh seluruh pesanan dengan produk, total, status pesanan, dan pembayaran dalam format Excel.</p><a class="btn mt-6" href="{{ route('admin.reports.orders') }}">Unduh Excel</a></div>@endsection
