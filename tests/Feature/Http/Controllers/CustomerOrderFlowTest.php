<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerOrderFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_can_log_in_with_normalized_username_and_admin_cannot_use_customer_login(): void
    {
        $customer = User::factory()->create(['username' => 'pelanggan']);
        $admin = User::factory()->create(['username' => 'admin-toko', 'role' => 'ADMIN']);

        $this->post(route('login.store'), [
            'username' => '  PELANGGAN  ',
            'password' => 'password',
            'callback' => '/checkout',
        ])->assertRedirect('/checkout');
        $this->assertAuthenticatedAs($customer);

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'username' => $admin->username,
            'password' => 'password',
        ])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_checkout_requires_an_authenticated_customer(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->get(route('checkout'))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('checkout'))->assertForbidden();
    }

    public function test_checkout_uses_server_price_instead_of_client_price(): void
    {
        Mail::fake();
        $customer = User::factory()->create();
        $product = Product::factory()->create(['price' => 40000, 'stock' => 10]);

        $this->actingAs($customer)
            ->post(route('checkout.store'), $this->checkoutPayload($product, [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 1,
            ]))
            ->assertRedirect();

        $order = Order::query()->sole();
        $this->assertSame(80000, $order->subtotal);
        $this->assertSame(25000, $order->shipping_total);
        $this->assertSame(105000, $order->total);
        $this->assertSame(40000, $order->items()->sole()->unit_price);
    }

    public function test_repeated_checkout_submission_is_idempotent(): void
    {
        Mail::fake();
        $customer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $payload = $this->checkoutPayload($product);

        $firstResponse = $this->actingAs($customer)->post(route('checkout.store'), $payload);
        $order = Order::query()->sole();
        $firstResponse->assertRedirect(route('orders.show', $order));

        $this->actingAs($customer)
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_duplicate_product_representations_are_rejected_without_partial_order(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $payload = $this->checkoutPayload($product);
        $payload['items'][] = ['product_id' => $product->id, 'quantity' => 1];

        $this->actingAs($customer)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $payload)
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_unavailable_product_and_insufficient_stock_are_rejected(): void
    {
        $customer = User::factory()->create();
        $inactiveProduct = Product::factory()->create(['status' => 'INACTIVE', 'stock' => 10]);

        $this->actingAs($customer)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->checkoutPayload($inactiveProduct))
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('cart');

        $lowStockProduct = Product::factory()->create(['stock' => 1]);

        $this->actingAs($customer)
            ->from(route('checkout'))
            ->post(route('checkout.store'), $this->checkoutPayload($lowStockProduct, ['quantity' => 2]))
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_malformed_checkout_payload_is_rejected(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $payload = $this->checkoutPayload($product, ['product_id' => -1, 'quantity' => 0]);
        $payload['checkout_token'] = 'not-a-uuid';

        $this->actingAs($customer)
            ->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors(['checkout_token', 'items.0.product_id', 'items.0.quantity']);

        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * @param  array<string, mixed>  $itemOverrides
     * @return array<string, mixed>
     */
    private function checkoutPayload(Product $product, array $itemOverrides = []): array
    {
        return [
            'name' => 'Pelanggan Uji',
            'phone' => '081234567890',
            'address' => 'Jalan Pengujian Nomor 123, Tangerang',
            'notes' => 'Tolong kirim pagi.',
            'checkout_token' => Str::uuid()->toString(),
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                ...$itemOverrides,
            ]],
        ];
    }
}
