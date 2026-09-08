@extends('layouts.admin')

@section('title', 'Pembayaran')

@section('content')
    <p class="eyebrow">Keuangan</p>
    <h1 class="section-title mt-3">Pembayaran.</h1>

    <div class="mt-8 space-y-3">
        @forelse ($orders as $order)
            <form class="card grid gap-4 p-4 lg:grid-cols-[minmax(0,1fr)_150px_180px_minmax(180px,1fr)_auto] lg:items-end" method="post" action="{{ route('admin.payments.update', $order) }}">
                @csrf
                @method('PATCH')

                <div>
                    <a class="font-semibold" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a>
                    <p class="mt-1 text-xs text-ink/50">{{ $order->customer_name_snapshot }} · Rp{{ number_format($order->total, 0, ',', '.') }}</p>
                    @if ($order->payment?->paid_at)
                        <p class="mt-1 text-xs text-brand-deep">Dibayar {{ $order->payment->paid_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>

                <label>Status
                    <select class="field" name="status">
                        @foreach (['UNPAID', 'PENDING', 'PAID', 'FAILED', 'REFUNDED'] as $status)
                            <option @selected($order->payment_status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Metode
                    <input class="field" name="payment_method" value="{{ $order->payment?->payment_method }}" placeholder="Transfer bank">
                </label>

                <label>Catatan
                    <input class="field" name="note" value="{{ $order->payment?->note }}" placeholder="Referensi pembayaran">
                </label>

                <button class="btn-outline">Simpan</button>
            </form>
        @empty
            <div class="card py-16 text-center text-sm text-ink/50">Belum ada pembayaran.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
