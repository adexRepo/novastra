<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PaymentController extends Controller
{
    public function index(OrderService $service)
    {
        return view('admin.payments', [
            'orders' => Order::with('payment')->latest()->paginate(20),
            'paymentTransitions' => collect(['UNPAID', 'PENDING', 'PAID', 'FAILED', 'REFUNDED'])
                ->mapWithKeys(fn (string $status): array => [$status => $service->allowedPaymentTransitions($status)]),
        ]);
    }

    public function update(Request $request, Order $order, OrderService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['UNPAID', 'PENDING', 'PAID', 'FAILED', 'REFUNDED'])], 'payment_method' => ['nullable', 'string', 'max:80'], 'note' => ['nullable', 'string', 'max:300']]);
        try {
            $service->transitionPayment($order, $data['status'], $request->user(), $data['payment_method'] ?? null, $data['note'] ?? null);

            return back()->with('success', 'Pembayaran diperbarui.');
        } catch (RuntimeException) {
            return back()->withErrors(['payment' => 'Perubahan status pembayaran tidak diizinkan.']);
        }
    }
}
