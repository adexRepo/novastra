<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_empty_category_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertModelMissing($category);
    }

    public function test_category_with_product_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['is_deleted' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors(['category' => 'Kategori hanya dapat dihapus jika tidak memiliki produk.']);

        $this->assertModelExists($category);
    }

    public function test_customer_cannot_delete_category(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $category = Category::factory()->create();

        $this->actingAs($customer)
            ->delete(route('admin.categories.destroy', $category))
            ->assertForbidden();

        $this->assertModelExists($category);
    }
}
