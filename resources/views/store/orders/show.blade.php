@extends('layouts.store')

@section('title', $order->order_number)

@section('content')
    @if (session('clear_cart'))
        <span data-clear-cart hidden></span>
    @endif

    <section class="page-shell py-12">
        <a href="{{ route('orders.index') }}" class="text-sm">← Semua pesanan</a>
        <div class="mt-6 grid gap-7 lg:grid-cols-[1fr_340px]">
            <div>
                <p class="eyebrow">Pesanan</p>
                <h1 class="mt-3 font-display text-4xl">{{ $order->order_number }}</h1>

                <div class="card mt-7 divide-y">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between gap-4 p-5">
                            <div>
                                <strong>{{ $item->product_name }}</strong>
                                <p class="text-sm text-ink/50">{{ $item->product_sku }} · {{ $item->quantity }} × Rp{{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <strong>Rp{{ number_format($item->line_total, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>

                @if ($order->status === 'COMPLETED')
                    <form class="card mt-6 p-5" method="post" action="{{ route('orders.feedback', $order) }}">
                        @csrf
                        <h2 class="font-display text-2xl">{{ $order->feedback ? 'Perbarui masukan Anda' : 'Bagaimana pesanan Anda?' }}</h2>
                        <select class="field" name="rating" required>
                            <option value="5" @selected((int) old('rating', $order->feedback?->rating ?? 5) === 5)>5 — Sangat baik</option>
                            <option value="4" @selected((int) old('rating', $order->feedback?->rating) === 4)>4 — Baik</option>
                            <option value="3" @selected((int) old('rating', $order->feedback?->rating) === 3)>3 — Cukup</option>
                            <option value="2" @selected((int) old('rating', $order->feedback?->rating) === 2)>2 — Kurang</option>
                            <option value="1" @selected((int) old('rating', $order->feedback?->rating) === 1)>1 — Buruk</option>
                        </select>
                        <textarea class="field py-3" name="message" placeholder="Ceritakan pengalaman Anda" required>{{ old('message', $order->feedback?->message) }}</textarea>
                        <button class="btn mt-4">{{ $order->feedback ? 'Perbarui masukan' : 'Kirim masukan' }}</button>
                    </form>
                @endif
            </div>

            <aside class="card h-fit p-6">
                <div class="flex gap-2"><span class="badge">{{ $order->status }}</span><span class="badge">{{ $order->payment_status }}</span></div>
                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt>Pengiriman</dt><dd>Rp{{ number_format($order->shipping_total, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t pt-4 text-base font-bold"><dt>Total</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div>
                </dl>
                <div class="mt-6 text-sm leading-6 text-ink/60"><strong class="text-ink">Dikirim ke</strong><br>{{ $order->customer_name_snapshot }}<br>{{ $order->phone_snapshot }}<br>{{ $order->address_snapshot }}</div>
                <a class="btn mt-6 w-full" target="_blank" rel="noopener" href="https://wa.me/{{ $companySettings['whatsapp'] }}?text={{ urlencode('Halo '.$companySettings['company_name'].', saya ingin melanjutkan pesanan '.$order->order_number) }}">Lanjut via WhatsApp</a>
            </aside>
        </div>
    </section>
@endsection
