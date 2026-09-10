<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return view('store.orders.index', ['orders' => $request->user()->orders()->withCount('items')->latest()->paginate(10)]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        return view('store.orders.show', ['order' => $order->load(['items', 'payment', 'feedback'])]);
    }

    public function feedback(Request $request, Order $order)
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($order->status !== 'COMPLETED') {
            return back()->withErrors(['feedback' => 'Masukan dapat dikirim setelah pesanan selesai.']);
        }

        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'message' => ['required', 'string', 'max:1000']]);
        Feedback::updateOrCreate(['order_id' => $order->id, 'customer_id' => $request->user()->id], [...$data, 'name' => $request->user()->name, 'email' => $request->user()->email]);

        return back()->with('success', 'Terima kasih atas masukan Anda.');
    }
}
