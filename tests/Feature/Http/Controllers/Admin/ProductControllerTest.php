<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_index_searches_database_and_paginates_twenty_five_rows(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Product::factory()->count(25)->create();
        $matchingProduct = Product::factory()->create(['name' => 'Ayam Kampung Premium', 'sku' => 'SPECIAL-001']);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['q' => 'SPECIAL-001']))
            ->assertViewHas('products', function ($products) use ($matchingProduct): bool {
                return $products->perPage() === 25
                    && $products->total() === 1
                    && $products->first()->is($matchingProduct);
            });
    }

    public function test_product_index_orders_status_then_created_at_descending(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $olderInactive = Product::factory()->create(['status' => 'INACTIVE', 'created_at' => now()->subDay()]);
        $newerInactive = Product::factory()->create(['status' => 'INACTIVE', 'created_at' => now()]);
        $active = Product::factory()->create(['status' => 'ACTIVE', 'created_at' => now()->addDay()]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertViewHas('products', function ($products) use ($olderInactive, $newerInactive, $active): bool {
                return $products->pluck('id')->all() === [$newerInactive->id, $olderInactive->id, $active->id];
            });
    }

    public function test_formatted_price_is_saved_as_integer(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Ayam Segar',
            'sku' => 'AYAM-5000',
            'category_id' => $category->id,
            'short_description' => 'Ayam segar siap dimasak.',
            'description' => 'Ayam segar yang telah dibersihkan dan siap dimasak.',
            'price' => '5.000',
            'stock' => 10,
            'status' => 'ACTIVE',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'AYAM-5000',
            'price' => 5000,
        ]);
    }

    public function test_product_form_exposes_currency_and_upload_controls(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Category::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertSee('data-currency-input', false)
            ->assertSee('data-file-input', false)
            ->assertSee('Pilih gambar produk');
    }

    public function test_destroy_hides_product_without_removing_historical_row(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $product = Product::factory()->create(['featured' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_deleted' => true,
            'status' => 'INACTIVE',
            'featured' => false,
        ]);
        $this->actingAs($admin)->get(route('admin.products.index'))->assertDontSee($product->name);
        $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertNotFound();
        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_customer_cannot_delete_product(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->delete(route('admin.products.destroy', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_deleted' => false]);
    }
}
