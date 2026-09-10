<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_all_public_page_destinations_load(): void
    {
        foreach (['home', 'products.index', 'categories.index', 'about', 'faq', 'contact', 'cart', 'login', 'register'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_store_navigation_is_available_on_mobile_and_marks_current_section(): void
    {
        $response = $this->get(route('products.index'));

        $response
            ->assertOk()
            ->assertSee('aria-label="Buka menu utama"', false)
            ->assertSee('aria-label="Navigasi utama mobile"', false)
            ->assertSee('href="'.route('products.index').'"', false)
            ->assertSee('data-checkout-url="'.route('checkout').'"', false)
            ->assertSee('data-analytics-url="'.route('analytics.view').'"', false);

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('products.index'), '/').'"[^>]*aria-current="page"/s',
            $response->getContent(),
        );
    }

    public function test_catalog_rejects_malformed_filters_without_server_error(): void
    {
        $this->from(route('products.index'))
            ->get(route('products.index', ['q' => ['unexpected']]))
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('q');

        $this->from(route('products.index'))
            ->get(route('products.index', ['sort' => 'not-a-sort', 'available' => 'sometimes']))
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors(['sort', 'available']);
    }

    public function test_catalog_available_filter_hides_out_of_stock_products(): void
    {
        $availableProduct = Product::factory()->create(['name' => 'Produk Tersedia', 'stock' => 3]);
        $outOfStockProduct = Product::factory()->create(['name' => 'Produk Habis', 'stock' => 0]);

        $this->get(route('products.index', ['available' => 1]))
            ->assertOk()
            ->assertSee($availableProduct->name)
            ->assertDontSee($outOfStockProduct->name)
            ->assertSee('Hanya yang tersedia');
    }

    public function test_deleted_product_cannot_be_opened_even_if_its_status_is_active(): void
    {
        $deletedProduct = Product::factory()->create(['status' => 'ACTIVE', 'is_deleted' => true]);

        $this->get(route('products.show', $deletedProduct))->assertNotFound();
        $this->get(route('products.index'))->assertDontSee($deletedProduct->name);
    }

    public function test_product_in_inactive_category_is_hidden_from_customers(): void
    {
        $inactiveCategory = Category::factory()->create(['status' => 'INACTIVE']);
        $product = Product::factory()->for($inactiveCategory)->create(['status' => 'ACTIVE']);

        $this->get(route('products.show', $product))->assertNotFound();
        $this->get(route('products.index'))->assertDontSee($product->name);
        $this->get(route('home'))->assertDontSee($product->name);
    }

    public function test_existing_order_feedback_is_shown_for_safe_updates(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer, 'customer')->create(['status' => 'COMPLETED']);
        Feedback::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'source' => 'WEBSITE',
            'name' => $customer->name,
            'email' => $customer->email,
            'rating' => 4,
            'message' => 'Pesanan sebelumnya sangat baik.',
        ]);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Perbarui masukan Anda')
            ->assertSee('Pesanan sebelumnya sangat baik.');
    }

    public function test_contact_mail_failure_returns_actionable_error_and_preserves_input(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new RuntimeException('SMTP unavailable'));

        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Pelanggan Kontak',
                'email' => 'pelanggan@example.com',
                'message' => 'Saya ingin menanyakan pengiriman.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('contact')
            ->assertSessionHasInput('name', 'Pelanggan Kontak')
            ->assertSessionHasInput('email', 'pelanggan@example.com')
            ->assertSessionHasInput('message', 'Saya ingin menanyakan pengiriman.');
    }

    public function test_contact_message_can_be_sent(): void
    {
        Mail::fake();

        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'name' => 'Pelanggan Kontak',
                'email' => 'pelanggan@example.com',
                'message' => 'Saya ingin menanyakan pengiriman.',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');
    }
}
