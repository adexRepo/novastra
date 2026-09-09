<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function show(): View
    {
        return view('store.checkout', ['checkoutToken' => Str::uuid()->toString()]);
    }

    public function store(Request $request, OrderService $orders, NotificationService $notifications)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'notes' => ['nullable', 'string', 'max:300'],
            'checkout_token' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        try {
            $order = $orders->create($request->user(), $data);
            if ($order->wasRecentlyCreated) {
                $notifications->orderCreated($order);
            }

            return redirect()->route('orders.show', $order)->with([
                'success' => 'Pesanan berhasil dibuat.',
                'clear_cart' => true,
            ]);
        } catch (RuntimeException $error) {
            $message = match ($error->getMessage()) {
                'INSUFFICIENT_STOCK' => 'Stok berubah. Periksa kembali keranjang Anda.',
                'PRODUCT_UNAVAILABLE' => 'Salah satu produk sudah tidak tersedia.',
                'INVALID_ITEMS' => 'Keranjang berisi produk yang tidak valid.',
                default => 'Pesanan belum berhasil dibuat.',
            };

            return back()->withInput()->withErrors(['cart' => $message]);
        }
    }
}
