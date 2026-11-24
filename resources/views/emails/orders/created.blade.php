<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recipient === 'admin' ? 'Pesanan baru' : 'Pesanan diterima' }} · {{ $order->order_number }}</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { padding: 20px 12px !important; }
            .email-card { border-radius: 18px !important; }
            .email-content { padding: 28px 20px !important; }
            .email-header { padding: 22px 20px !important; }
            .email-meta-cell { display: block !important; width: auto !important; padding: 0 0 14px !important; }
            .email-meta-cell:last-child { padding-bottom: 0 !important; }
            .item-price { font-size: 12px !important; }
        }
    </style>
</head>
@php
    $isAdmin = $recipient === 'admin';
    $statusLabels = [
        'PENDING' => 'Menunggu konfirmasi',
        'CONFIRMED' => 'Dikonfirmasi',
        'PROCESSING' => 'Sedang diproses',
        'SHIPPED' => 'Dalam pengiriman',
        'COMPLETED' => 'Selesai',
        'CANCELLED' => 'Dibatalkan',
    ];
    $paymentLabels = [
        'UNPAID' => 'Belum dibayar',
        'PAID' => 'Sudah dibayar',
        'REFUNDED' => 'Dikembalikan',
    ];
    $actionUrl = $isAdmin ? route('admin.orders.show', $order) : route('orders.show', $order);
    $preheader = $isAdmin
        ? "Pesanan baru {$order->order_number} dari {$order->customer_name_snapshot} perlu ditindaklanjuti."
        : "Pesanan {$order->order_number} telah kami terima dan sedang menunggu konfirmasi.";
@endphp
<body style="margin:0; background:#f7fafc; color:#031730; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">{{ $preheader }}</div>
    <div class="email-shell" style="padding:40px 16px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr><td align="center">
                <table role="presentation" class="email-card" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px; overflow:hidden; border:1px solid rgba(3,23,48,.10); border-radius:24px; background:#ffffff; box-shadow:0 18px 50px rgba(3,23,48,.08);">
                    <tr><td class="email-header" style="padding:26px 36px; background:#031730; color:#ffffff;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
                            <td><p style="margin:0; font-size:20px; font-weight:700; letter-spacing:-.02em;">{{ $company['company_name'] }}</p><p style="margin:4px 0 0; color:rgba(255,255,255,.58); font-size:11px; font-weight:700; letter-spacing:.14em; text-transform:uppercase;">{{ $company['brand_suffix'] }}</p></td>
                            <td align="right"><span style="display:inline-block; border-radius:999px; background:#fba02d; padding:8px 12px; color:#031730; font-size:11px; font-weight:700; letter-spacing:.05em;">{{ $isAdmin ? 'PESANAN BARU' : 'PESANAN DITERIMA' }}</span></td>
                        </tr></table>
                    </td></tr>
                    <tr><td class="email-content" style="padding:38px 36px 34px;">
                        <p style="margin:0 0 12px; color:#52657a; font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;">{{ $isAdmin ? 'Perlu ditindaklanjuti' : 'Terima kasih sudah berbelanja' }}</p>
                        <h1 style="margin:0; color:#031730; font-size:28px; line-height:1.25; letter-spacing:-.025em;">{{ $isAdmin ? 'Ada pesanan baru untuk diproses.' : 'Pesanan Anda sudah kami terima.' }}</h1>
                        <p style="margin:16px 0 0; color:#52657a; font-size:15px; line-height:1.7;">
                            @if ($isAdmin)
                                {{ $order->customer_name_snapshot }} telah membuat pesanan. Periksa detail dan konfirmasi ketersediaan produk melalui dashboard admin.
                            @else
                                Halo {{ $order->customer_name_snapshot }}, tim kami akan memeriksa ketersediaan produk dan menghubungi Anda untuk konfirmasi berikutnya.
                            @endif
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px; border-radius:16px; background:#eef6f8;"><tr>
                            <td class="email-meta-cell" width="34%" style="padding:18px 10px 18px 20px; vertical-align:top;"><p style="margin:0; color:#65768a; font-size:10px; font-weight:700; letter-spacing:.10em; text-transform:uppercase;">Nomor pesanan</p><p style="margin:7px 0 0; color:#031730; font-size:14px; font-weight:700;">{{ $order->order_number }}</p></td>
                            <td class="email-meta-cell" width="33%" style="padding:18px 10px; vertical-align:top;"><p style="margin:0; color:#65768a; font-size:10px; font-weight:700; letter-spacing:.10em; text-transform:uppercase;">Status</p><p style="margin:7px 0 0; color:#031730; font-size:14px; font-weight:700;">{{ $statusLabels[$order->status] ?? $order->status }}</p></td>
                            <td class="email-meta-cell" width="33%" style="padding:18px 20px 18px 10px; vertical-align:top;"><p style="margin:0; color:#65768a; font-size:10px; font-weight:700; letter-spacing:.10em; text-transform:uppercase;">Pembayaran</p><p style="margin:7px 0 0; color:#031730; font-size:14px; font-weight:700;">{{ $paymentLabels[$order->payment_status] ?? $order->payment_status }}</p></td>
                        </tr></table>

                        <h2 style="margin:30px 0 12px; color:#031730; font-size:16px;">Ringkasan pesanan</h2>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td style="border-top:1px solid #d5e3e9; padding:15px 8px 15px 0; vertical-align:top;"><p style="margin:0; color:#031730; font-size:14px; font-weight:700; line-height:1.45;">{{ $item->product_name }}</p><p style="margin:5px 0 0; color:#65768a; font-size:12px;">{{ $item->product_sku }} · {{ $item->quantity }} × Rp{{ number_format($item->unit_price, 0, ',', '.') }}</p></td>
                                    <td class="item-price" align="right" style="border-top:1px solid #d5e3e9; padding:15px 0 15px 8px; color:#031730; font-size:14px; font-weight:700; white-space:nowrap; vertical-align:top;">Rp{{ number_format($item->line_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:6px; border-top:1px solid #d5e3e9;">
                            <tr><td style="padding:14px 0 5px; color:#52657a; font-size:13px;">Subtotal</td><td align="right" style="padding:14px 0 5px; color:#031730; font-size:13px;">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
                            <tr><td style="padding:5px 0; color:#52657a; font-size:13px;">Pengiriman</td><td align="right" style="padding:5px 0; color:#031730; font-size:13px;">Rp{{ number_format($order->shipping_total, 0, ',', '.') }}</td></tr>
                            <tr><td style="padding:12px 0 0; color:#031730; font-size:16px; font-weight:700;">Total</td><td align="right" style="padding:12px 0 0; color:#031730; font-size:18px; font-weight:700;">Rp{{ number_format($order->total, 0, ',', '.') }}</td></tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px; border-top:1px solid #d5e3e9;"><tr><td style="padding-top:22px; vertical-align:top;">
                            <p style="margin:0; color:#65768a; font-size:10px; font-weight:700; letter-spacing:.10em; text-transform:uppercase;">Dikirim kepada</p>
                            <p style="margin:8px 0 0; color:#031730; font-size:13px; font-weight:700; line-height:1.65;">{{ $order->customer_name_snapshot }}</p>
                            <p style="margin:0; color:#52657a; font-size:13px; line-height:1.65;">{{ $order->phone_snapshot }}<br>{{ $order->address_snapshot }}</p>
                        </td></tr></table>

                        @if (filled($order->notes))
                            <div style="margin-top:20px; border-left:3px solid #fba02d; background:#eef6f8; padding:14px 16px;"><p style="margin:0; color:#65768a; font-size:10px; font-weight:700; letter-spacing:.10em; text-transform:uppercase;">Catatan pesanan</p><p style="margin:7px 0 0; color:#294057; font-size:13px; line-height:1.6;">{{ $order->notes }}</p></div>
                        @endif

                        <div style="margin-top:30px;"><a href="{{ $actionUrl }}" style="display:inline-block; border-radius:999px; background:#031730; padding:14px 22px; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none;">{{ $isAdmin ? 'Kelola pesanan' : 'Lihat detail pesanan' }}</a></div>
                        @unless ($isAdmin)
                            <p style="margin:18px 0 0; color:#65768a; font-size:12px; line-height:1.6;">Butuh bantuan? Hubungi kami melalui <a href="mailto:{{ $company['email'] }}" style="color:#9a4f00; font-weight:700;">{{ $company['email'] }}</a> atau {{ $company['phone'] }}.</p>
                        @endunless
                    </td></tr>
                    <tr><td style="padding:22px 36px; background:#eef6f8; color:#65768a; font-size:11px; line-height:1.6;">Email ini dikirim otomatis oleh {{ $company['company_name'] }} {{ $company['brand_suffix'] }}.<br>{{ $company['legal_name'] }} · {{ $company['location'] }}</td></tr>
                </table>
            </td></tr>
        </table>
    </div>
</body>
</html>
