@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Penjualan</p>
            <h1 class="section-title mt-3">Pesanan.</h1>
        </div>
        <a
            class="btn-outline size-11 px-0"
            href="{{ route('admin.reports.orders', request()->only(['q', 'status', 'payment'])) }}"
            title="Unduh Excel"
            aria-label="Unduh pesanan sesuai filter dalam Excel"
        >
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    <form class="mt-7 grid gap-2 sm:grid-cols-[1fr_180px_180px_auto]" method="get" action="{{ route('admin.orders.index') }}">
        <label class="sr-only" for="order-search">Cari nomor, pelanggan, atau email</label>
        <input id="order-search" class="field mt-0" name="q" value="{{ request('q') }}" placeholder="Nomor, pelanggan, atau email">
        <label class="sr-only" for="order-status">Status pesanan</label>
        <select id="order-status" class="field mt-0" name="status">
            <option value="">Semua status</option>
            @foreach (['PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'COMPLETED', 'CANCELLED'] as $status)
                <option @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="payment-status">Status pembayaran</label>
        <select id="payment-status" class="field mt-0" name="payment">
            <option value="">Semua pembayaran</option>
            @foreach (['UNPAID', 'PENDING', 'PAID', 'FAILED', 'REFUNDED'] as $status)
                <option @selected(request('payment') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="btn">Filter</button>
    </form>

    <div class="card mt-6 overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead>
                <tr class="text-ink/50">
                    <th class="p-4">Nomor</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Item</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t">
                        <td class="p-4"><a class="font-semibold text-brand-deep" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer_name_snapshot }}</td>
                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        <td>{{ $order->items_count }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->payment_status }}</td>
                        <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="border-t p-10 text-center text-ink/50">Tidak ada pesanan yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
