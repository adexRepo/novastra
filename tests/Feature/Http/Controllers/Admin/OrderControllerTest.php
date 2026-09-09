<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_order_index_paginates_twenty_five_rows(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Order::factory()->count(26)->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertViewHas('orders', fn ($orders): bool => $orders->perPage() === 25 && $orders->count() === 25 && $orders->total() === 26);
    }

    public function test_order_index_filters_database_by_search_status_and_payment(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $matchingOrder = Order::factory()->create([
            'order_number' => 'NVS-MATCH-001',
            'status' => 'PROCESSING',
            'payment_status' => 'PAID',
        ]);
        Order::factory()->create([
            'order_number' => 'NVS-MATCH-002',
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['q' => 'MATCH', 'status' => 'PROCESSING', 'payment' => 'PAID']))
            ->assertViewHas('orders', function ($orders) use ($matchingOrder): bool {
                return $orders->total() === 1 && $orders->first()->is($matchingOrder);
            });
    }

    public function test_invalid_order_filter_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->get(route('admin.orders.index', ['status' => 'DROP TABLE']))
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHasErrors('status');
    }

    public function test_customer_cannot_mutate_order_or_payment_through_admin_routes(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer, 'customer')->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)->patch(route('admin.orders.status', $order), ['status' => 'CONFIRMED'])->assertForbidden();
        $this->actingAs($customer)->post(route('admin.orders.items.store', $order), ['product_id' => $product->id, 'quantity' => 1])->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.payments.update', $order), ['status' => 'PAID'])->assertForbidden();

        $this->assertSame('PENDING', $order->fresh()->status);
    }

    public function test_confirming_order_deducts_stock_once_and_records_one_history(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->orderWithItem($product, 3);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'CONFIRMED'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'CONFIRMED'])->assertSessionHasNoErrors();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_deducted);
        $this->assertDatabaseCount('order_histories', 1);
    }

    public function test_failed_confirmation_rolls_back_all_stock_changes(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $availableProduct = Product::factory()->create(['stock' => 10]);
        $unavailableProduct = Product::factory()->create(['stock' => 1]);
        $order = $this->orderWithItem($availableProduct, 3);
        $this->addOrderItem($order, $unavailableProduct, 2);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'CONFIRMED'])
            ->assertSessionHasErrors('status');

        $this->assertSame(10, $availableProduct->fresh()->stock);
        $this->assertSame(1, $unavailableProduct->fresh()->stock);
        $this->assertSame('PENDING', $order->fresh()->status);
        $this->assertDatabaseCount('order_histories', 0);
    }

    public function test_cancelling_confirmed_order_restores_stock_once(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::factory()->create(['stock' => 7]);
        $order = $this->orderWithItem($product, 3, [
            'status' => 'CONFIRMED',
            'stock_deducted' => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'CANCELLED'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'CANCELLED'])->assertSessionHasNoErrors();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_restored);
        $this->assertDatabaseCount('order_histories', 1);
    }

    public function test_invalid_status_transition_does_not_mutate_order(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::factory()->create(['stock' => 10]);
        $order = $this->orderWithItem($product, 2);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'SHIPPED'])
            ->assertSessionHasErrors('status');

        $this->assertSame('PENDING', $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('order_histories', 0);
    }

    public function test_admin_cannot_add_more_than_available_stock_to_pending_order(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::factory()->create(['price' => 20000, 'stock' => 5]);
        $order = $this->orderWithItem($product, 4);
        $originalTotal = $order->total;

        $this->actingAs($admin)
            ->post(route('admin.orders.items.store', $order), ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHasErrors('item');

        $this->assertSame(4, $order->items()->sole()->quantity);
        $this->assertSame($originalTotal, $order->fresh()->total);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('order_histories', 0);
    }

    public function test_adding_item_to_confirmed_order_deducts_stock_and_recomputes_totals(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $existingProduct = Product::factory()->create(['price' => 50000, 'stock' => 8]);
        $addedProduct = Product::factory()->create(['price' => 75000, 'stock' => 4]);
        $order = $this->orderWithItem($existingProduct, 2, [
            'status' => 'CONFIRMED',
            'stock_deducted' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.items.store', $order), ['product_id' => $addedProduct->id, 'quantity' => 2])
            ->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertSame(2, $addedProduct->fresh()->stock);
        $this->assertSame(250000, $order->subtotal);
        $this->assertSame(0, $order->shipping_total);
        $this->assertSame(250000, $order->total);
        $this->assertDatabaseHas('order_histories', ['order_id' => $order->id, 'action' => 'ITEM_ADDED']);
    }

    public function test_payment_transitions_are_validated_and_idempotent(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $order = Order::factory()->create();
        Payment::create(['order_id' => $order->id, 'status' => 'UNPAID']);

        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $order), ['status' => 'REFUNDED'])
            ->assertSessionHasErrors('payment');

        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $order), ['status' => 'PAID', 'payment_method' => 'Transfer bank'])
            ->assertSessionHasNoErrors();
        $paidAt = $order->payment()->sole()->paid_at;

        $this->actingAs($admin)
            ->patch(route('admin.payments.update', $order), ['status' => 'PAID', 'payment_method' => 'Transfer bank', 'note' => 'Terverifikasi'])
            ->assertSessionHasNoErrors();

        $this->assertSame('PAID', $order->fresh()->payment_status);
        $this->assertTrue($paidAt->equalTo($order->payment()->sole()->paid_at));
        $this->assertDatabaseCount('order_histories', 1);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'note' => 'Terverifikasi']);
    }

    public function test_order_page_only_offers_valid_actions_for_current_status(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $order = Order::factory()->create(['status' => 'CANCELLED']);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Pesanan sudah berada pada status akhir.')
            ->assertSee('Produk tidak dapat ditambahkan pada status CANCELLED.')
            ->assertDontSee(route('admin.orders.status', $order))
            ->assertDontSee(route('admin.orders.items.store', $order));
    }

    public function test_payment_page_only_offers_current_and_valid_next_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $order = Order::factory()->create(['payment_status' => 'PAID']);
        Payment::create(['order_id' => $order->id, 'status' => 'PAID', 'paid_at' => now()]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('<option selected>PAID</option>', false)
            ->assertSee('>REFUNDED</option>', false)
            ->assertDontSee('>UNPAID</option>', false)
            ->assertDontSee('>FAILED</option>', false);
    }

    /**
     * @param  array<string, mixed>  $orderOverrides
     */
    private function orderWithItem(Product $product, int $quantity, array $orderOverrides = []): Order
    {
        $subtotal = $product->price * $quantity;
        $shipping = $subtotal >= 250000 ? 0 : 25000;
        $order = Order::factory()->create([
            'subtotal' => $subtotal,
            'shipping_total' => $shipping,
            'total' => $subtotal + $shipping,
            ...$orderOverrides,
        ]);
        $this->addOrderItem($order, $product, $quantity);

        return $order;
    }

    private function addOrderItem(Order $order, Product $product, int $quantity): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ]);
    }
}
