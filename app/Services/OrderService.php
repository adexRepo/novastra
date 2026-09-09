<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    private const ORDER_TRANSITIONS = [
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['PROCESSING', 'CANCELLED'],
        'PROCESSING' => ['SHIPPED', 'CANCELLED'],
        'SHIPPED' => ['COMPLETED'],
        'COMPLETED' => [],
        'CANCELLED' => [],
    ];

    private const PAYMENT_TRANSITIONS = [
        'UNPAID' => ['PENDING', 'PAID', 'FAILED'],
        'PENDING' => ['PAID', 'FAILED'],
        'PAID' => ['REFUNDED'],
        'FAILED' => ['PENDING', 'PAID'],
        'REFUNDED' => [],
    ];

    /**
     * @return array<int, string>
     */
    public function allowedOrderTransitions(string $status): array
    {
        return self::ORDER_TRANSITIONS[$status] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function allowedPaymentTransitions(string $status): array
    {
        return self::PAYMENT_TRANSITIONS[$status] ?? [];
    }

    public function canAddItems(string $status): bool
    {
        return in_array($status, ['PENDING', 'CONFIRMED'], true);
    }

    public function create(User $customer, array $data): Order
    {
        try {
            return DB::transaction(function () use ($customer, $data) {
                $existingOrder = Order::query()
                    ->where('customer_id', $customer->id)
                    ->where('checkout_token', $data['checkout_token'])
                    ->first();

                if ($existingOrder) {
                    return $existingOrder->load('items');
                }

                $requested = collect($data['items'])->mapWithKeys(fn (array $item): array => [
                    (int) $item['product_id'] => ['quantity' => (int) $item['quantity']],
                ]);
                if ($requested->count() !== count($data['items'])) {
                    throw new RuntimeException('INVALID_ITEMS');
                }

                $products = Product::query()->active()->whereIn('id', $requested->keys())->lockForUpdate()->get();
                if ($products->count() !== $requested->count()) {
                    throw new RuntimeException('PRODUCT_UNAVAILABLE');
                }

                $items = $products->map(function (Product $product) use ($requested) {
                    $quantity = $requested[$product->id]['quantity'];
                    if ($quantity < 1 || $quantity > $product->stock) {
                        throw new RuntimeException('INSUFFICIENT_STOCK');
                    }

                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'line_total' => $product->price * $quantity,
                    ];
                });
                $subtotal = (int) $items->sum('line_total');
                $shipping = $subtotal >= 250000 ? 0 : 25000;
                $order = Order::create([
                    'order_number' => 'NVS-'.now()->format('ymd').'-'.strtoupper(Str::random(6)),
                    'customer_id' => $customer->id,
                    'checkout_token' => $data['checkout_token'],
                    'customer_name_snapshot' => $data['name'],
                    'customer_email_snapshot' => $customer->email,
                    'phone_snapshot' => $data['phone'],
                    'address_snapshot' => $data['address'],
                    'notes' => $data['notes'] ?? null,
                    'subtotal' => $subtotal,
                    'shipping_total' => $shipping,
                    'total' => $subtotal + $shipping,
                ]);
                $order->items()->createMany($items->all());
                $order->payment()->create(['status' => 'UNPAID']);

                return $order->load('items');
            }, 3);
        } catch (QueryException $error) {
            $existingOrder = Order::query()
                ->where('customer_id', $customer->id)
                ->where('checkout_token', $data['checkout_token'])
                ->first();

            if ($existingOrder) {
                return $existingOrder->load('items');
            }

            throw $error;
        }
    }

    public function transitionOrder(Order $order, string $target, User $actor): Order
    {
        return DB::transaction(function () use ($order, $target, $actor) {
            $locked = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($locked->status === $target) {
                return $locked;
            }
            if (! in_array($target, $this->allowedOrderTransitions($locked->status), true)) {
                throw new RuntimeException('INVALID_ORDER_TRANSITION');
            }

            if ($target === 'CONFIRMED' && ! $locked->stock_deducted) {
                foreach ($locked->items as $item) {
                    $changed = Product::query()->whereKey($item->product_id)->active()->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity);
                    if ($changed !== 1) {
                        throw new RuntimeException('INSUFFICIENT_STOCK');
                    }
                }
                $locked->stock_deducted = true;
            }

            if ($target === 'CANCELLED' && $locked->stock_deducted && ! $locked->stock_restored) {
                foreach ($locked->items as $item) {
                    if ($item->product_id) {
                        Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }
                $locked->stock_restored = true;
            }

            $old = $locked->status;
            $locked->status = $target;
            $locked->save();
            $locked->histories()->create(['changed_by' => $actor->id, 'action' => 'STATUS_CHANGED', 'old_value' => $old, 'new_value' => $target]);

            return $locked;
        }, 3);
    }

    public function transitionPayment(Order $order, string $target, User $actor, ?string $method = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $target, $actor, $method, $note) {
            $locked = Order::with('payment')->lockForUpdate()->findOrFail($order->id);
            $statusChanged = $locked->payment_status !== $target;

            if ($statusChanged && ! in_array($target, $this->allowedPaymentTransitions($locked->payment_status), true)) {
                throw new RuntimeException('INVALID_PAYMENT_TRANSITION');
            }

            $old = $locked->payment_status;
            $payment = $locked->payment()->firstOrNew();
            $payment->fill([
                'status' => $target,
                'payment_method' => $method,
                'note' => $note,
                'updated_by' => $actor->id,
            ]);

            if ($target === 'PAID' && ! $payment->paid_at) {
                $payment->paid_at = now();
            }

            if ($payment->isDirty()) {
                $payment->save();
            }

            if ($statusChanged) {
                $locked->payment_status = $target;
                $locked->save();
                $locked->histories()->create(['changed_by' => $actor->id, 'action' => 'PAYMENT_CHANGED', 'old_value' => $old, 'new_value' => $target]);
            }

            return $locked->fresh('payment');
        }, 3);
    }

    public function addItem(Order $order, int $productId, int $quantity, User $actor): Order
    {
        return DB::transaction(function () use ($order, $productId, $quantity, $actor) {
            $locked = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if (! $this->canAddItems($locked->status)) {
                throw new RuntimeException('INVALID_ORDER_TRANSITION');
            }
            $product = Product::query()->active()->lockForUpdate()->findOrFail($productId);
            $item = $locked->items->firstWhere('product_id', $product->id);
            $newQuantity = ($item?->quantity ?? 0) + $quantity;

            if ($quantity < 1 || $newQuantity > 100 || $quantity > $product->stock || ($locked->status === 'PENDING' && $newQuantity > $product->stock)) {
                throw new RuntimeException('INSUFFICIENT_STOCK');
            }
            if ($locked->status === 'CONFIRMED') {
                $product->decrement('stock', $quantity);
            }
            if ($item) {
                $item->quantity = $newQuantity;
                $item->line_total = $item->unit_price * $item->quantity;
                $item->save();
            } else {
                $locked->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'product_sku' => $product->sku, 'unit_price' => $product->price, 'quantity' => $quantity, 'line_total' => $product->price * $quantity]);
            }
            $subtotal = (int) $locked->items()->sum('line_total');
            $shipping = $subtotal >= 250000 ? 0 : 25000;
            $locked->update(['subtotal' => $subtotal, 'shipping_total' => $shipping, 'total' => $subtotal + $shipping]);
            $locked->histories()->create(['changed_by' => $actor->id, 'action' => 'ITEM_ADDED', 'new_value' => "{$product->sku} x {$quantity}"]);

            return $locked->fresh('items');
        }, 3);
    }
}
