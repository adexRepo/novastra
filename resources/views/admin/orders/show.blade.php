@extends('layouts.admin')

@section('title', $order->order_number)

@section('content')
    <a href="{{ route('admin.orders.index') }}" class="text-sm">← Semua pesanan</a>
    <h1 class="mt-4 font-display text-4xl">{{ $order->order_number }}</h1>

    <div class="mt-7 grid gap-6 xl:grid-cols-[1fr_360px]">
        <div>
            <div class="card overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead><tr><th class="p-4">Produk</th><th>Harga</th><th>Jumlah</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-t">
                                <td class="p-4"><strong>{{ $item->product_name }}</strong><br><span class="text-xs text-ink/50">{{ $item->product_sku }}</span></td>
                                <td>Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>Rp{{ number_format($item->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card mt-5 p-5">
                <h2 class="font-display text-2xl">Riwayat</h2>
                <div class="mt-3 divide-y">
                    @forelse ($order->histories as $history)
                        <p class="py-3 text-sm">{{ $history->action }}: {{ $history->old_value }} → {{ $history->new_value }} <span class="text-ink/45">· {{ $history->created_at->format('d/m H:i') }}</span></p>
                    @empty
                        <p class="py-4 text-sm text-ink/50">Belum ada perubahan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="card p-5">
                <h2 class="font-display text-2xl">Status</h2>
                <p class="mt-2 text-sm text-ink/60">Status saat ini: <strong>{{ $order->status }}</strong></p>

                @if ($statusOptions !== [])
                    <form class="mt-4" method="post" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <label>Status berikutnya
                            <select class="field" name="status">
                                @foreach ($statusOptions as $status)
                                    <option>{{ $status }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="btn mt-4 w-full">Perbarui status</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-ink/55">Pesanan sudah berada pada status akhir.</p>
                @endif
            </div>

            <div class="card p-5">
                <h2 class="font-display text-2xl">Tambah produk</h2>

                @if ($canAddItems && $products->isNotEmpty())
                    <form class="mt-4" method="post" action="{{ route('admin.orders.items.store', $order) }}">
                        @csrf
                        <label>Produk
                            <select class="field" name="product_id">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->stock }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="mt-4 block">Jumlah
                            <input class="field" type="number" name="quantity" min="1" max="100" value="1">
                        </label>
                        <button class="btn-outline mt-4 w-full">Tambahkan</button>
                    </form>
                @elseif (! $canAddItems)
                    <p class="mt-4 text-sm text-ink/55">Produk tidak dapat ditambahkan pada status {{ $order->status }}.</p>
                @else
                    <p class="mt-4 text-sm text-ink/55">Tidak ada produk aktif yang memiliki stok.</p>
                @endif
            </div>

            <div class="card p-5 text-sm">
                <strong>{{ $order->customer_name_snapshot }}</strong>
                <p class="mt-2 text-ink/60">{{ $order->customer_email_snapshot }}<br>{{ $order->phone_snapshot }}<br>{{ $order->address_snapshot }}</p>
                <p class="mt-5 text-lg font-bold">Total Rp{{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
        </aside>
    </div>
@endsection
