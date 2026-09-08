@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div>
            <p class="eyebrow">Ringkasan bisnis</p>
            <h1 class="section-title mt-3">Dashboard pemilik.</h1>
        </div>
        <nav class="flex flex-wrap gap-2" aria-label="Rentang waktu dashboard">
            @foreach ($rangeOptions as $value => $label)
                <a
                    href="{{ route('admin.dashboard', ['range' => $value]) }}"
                    class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $range === $value ? 'border-brand bg-brand text-white' : 'border-ink/15 bg-white hover:border-brand hover:text-brand' }}"
                    @if ($range === $value) aria-current="page" @endif
                >
                    {{ ucfirst($label) }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as $label => $value)
            <div class="card p-5">
                <p class="text-sm text-ink/55">{{ $label }}</p>
                <p class="mt-3 text-2xl font-bold">
                    {{ str_contains($label, 'Omzet') || str_contains($label, 'Rata') ? 'Rp'.number_format($value, 0, ',', '.') : number_format($value) }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div class="card p-5">
            <p class="text-sm text-ink/55">Perlu diproses</p>
            <p class="mt-2 text-3xl font-bold">{{ $pending }} pesanan</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-ink/55">Belum dibayar</p>
            <p class="mt-2 text-3xl font-bold">{{ $unpaid }} pesanan</p>
        </div>
    </div>

    <section class="card mt-6 p-5 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">{{ ucfirst($rangeLabel) }} terakhir</p>
                <h2 class="mt-2 font-display text-2xl">Tren penjualan & kunjungan</h2>
            </div>
            <div class="flex gap-4 text-xs text-ink/60">
                <span class="flex items-center gap-2"><i class="size-2.5 rounded-full bg-brand"></i> Omzet</span>
                <span class="flex items-center gap-2"><i class="size-2.5 rounded-full bg-red-500"></i> Pengunjung</span>
            </div>
        </div>

        <div class="mt-7 overflow-x-auto pb-2">
            <div
                class="grid gap-2"
                style="min-width: {{ max(680, $trend->count() * 34) }}px; grid-template-columns: repeat({{ $trend->count() }}, minmax(24px, 1fr));"
                aria-label="Grafik omzet dan pengunjung {{ $rangeLabel }} terakhir"
            >
                @foreach ($trend as $day)
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-40 w-full items-end justify-center gap-1 rounded-lg bg-sand/45 px-1 pt-3">
                            <div class="w-2.5 rounded-t bg-brand" style="height: {{ max(3, round(($day['revenue'] / $maximumRevenue) * 100)) }}%" title="Omzet Rp{{ number_format($day['revenue'], 0, ',', '.') }}"></div>
                            <div class="w-2.5 rounded-t bg-red-500" style="height: {{ max(3, round(($day['visitors'] / $maximumVisitors) * 100)) }}%" title="{{ $day['visitors'] }} pengunjung"></div>
                        </div>
                        <span class="text-[10px] text-ink/50">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card p-5">
            <h2 class="font-display text-2xl">Produk paling sering dipesan</h2>
            <div class="mt-5 space-y-4">
                @forelse ($topProducts as $product)
                    <div>
                        <div class="flex justify-between gap-4 text-sm">
                            <span>{{ $product->name }}</span>
                            <strong>{{ $product->ordered_quantity }} unit</strong>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-sand">
                            <div class="h-2 rounded-full bg-brand" style="width: {{ min(100, $product->ordered_quantity * 8) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-5 text-sm text-ink/50">Belum ada data penjualan.</p>
                @endforelse
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-display text-2xl">Stok menipis</h2>
            <div class="mt-4 divide-y">
                @forelse ($lowStock as $product)
                    <div class="flex justify-between py-3 text-sm">
                        <span>{{ $product->name }}</span>
                        <strong class="{{ $product->stock ? 'text-amber-700' : 'text-red-700' }}">{{ $product->stock }}</strong>
                    </div>
                @empty
                    <p class="py-5 text-sm text-ink/50">Semua stok aman.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="card mt-6 overflow-x-auto p-5">
        <h2 class="font-display text-2xl">Pesanan terbaru</h2>
        <table class="mt-4 w-full min-w-[600px] text-left text-sm">
            <thead class="text-ink/50">
                <tr><th class="py-3">Nomor</th><th>Pelanggan</th><th>Status</th><th>Total</th></tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr class="border-t">
                        <td class="py-3"><a class="font-semibold" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer_name_snapshot }}</td>
                        <td>{{ $order->status }}</td>
                        <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="border-t py-8 text-center text-ink/50">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
