<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminOrderFilterRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function index(AdminOrderFilterRequest $request): View
    {
        $orders = Order::withCount('items')
            ->adminFilters($request->filters())
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load(['items', 'histories.actor']), 'products' => Product::active()->where('stock', '>', 0)->orderBy('name')->get()]);
    }

    public function status(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['CONFIRMED', 'PROCESSING', 'SHIPPED', 'COMPLETED', 'CANCELLED'])]]);
        try {
            $service->transitionOrder($order, $data['status'], $request->user());

            return back()->with('success', 'Status pesanan diperbarui.');
        } catch (RuntimeException $error) {
            return back()->withErrors(['status' => $error->getMessage() === 'INSUFFICIENT_STOCK' ? 'Stok tidak mencukupi.' : 'Perubahan status tidak diizinkan.']);
        }
    }

    public function addItem(Request $request, Order $order, OrderService $service): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'between:1,100']]);
        try {
            $service->addItem($order, (int) $data['product_id'], (int) $data['quantity'], $request->user());

            return back()->with('success', 'Produk ditambahkan ke pesanan.');
        } catch (RuntimeException) {
            return back()->withErrors(['item' => 'Produk tidak dapat ditambahkan pada status atau stok saat ini.']);
        }
    }
}
