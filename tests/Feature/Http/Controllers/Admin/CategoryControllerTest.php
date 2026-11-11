<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $uploadRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploadRoot = storage_path('framework/testing/category-uploads');
        File::deleteDirectory($this->uploadRoot);
        config(['novastra.public_upload_dir' => $this->uploadRoot]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->uploadRoot);

        parent::tearDown();
    }

    public function test_admin_can_create_category_with_optional_image(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Buah Segar',
                'slug' => 'buah-segar',
                'description' => 'Pilihan buah segar setiap hari.',
                'status' => 'ACTIVE',
                'category_image' => UploadedFile::fake()->image('buah.jpg', 1200, 800),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('slug', 'buah-segar')->sole();

        $this->assertMatchesRegularExpression('#^/uploads/categories/[a-f0-9-]+\.jpg$#', (string) $category->image_path);
        $this->assertFileExists($this->uploadRoot.'/'.substr((string) $category->image_path, strlen('/uploads/')));
        $this->get(route('categories.index'))->assertSee($category->image_path, false);
    }

    public function test_category_without_upload_uses_default_image(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Kebutuhan Dapur',
                'description' => 'Kebutuhan dapur pilihan.',
                'status' => 'ACTIVE',
            ])
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('slug', 'kebutuhan-dapur')->sole();
        $this->assertNull($category->image_path);
        $this->assertSame(Category::DEFAULT_IMAGE_PATH, $category->imageUrl());
        $this->get(route('categories.index'))->assertSee(Category::DEFAULT_IMAGE_PATH, false);
    }

    public function test_replacing_category_image_removes_previous_managed_file(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $oldPath = '/uploads/categories/11111111-1111-4111-8111-111111111111.jpg';
        $oldAbsolutePath = $this->uploadRoot.'/categories/11111111-1111-4111-8111-111111111111.jpg';
        File::ensureDirectoryExists(dirname($oldAbsolutePath));
        File::put($oldAbsolutePath, 'old image');
        $category = Category::factory()->create(['image_path' => $oldPath]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'status' => 'ACTIVE',
                'category_image' => UploadedFile::fake()->image('replacement.webp', 1200, 800),
            ])
            ->assertSessionHasNoErrors();

        $category->refresh();
        $this->assertNotSame($oldPath, $category->image_path);
        $this->assertFileDoesNotExist($oldAbsolutePath);
        $this->assertFileExists($this->uploadRoot.'/'.substr((string) $category->image_path, strlen('/uploads/')));
    }

    public function test_invalid_category_image_is_rejected_with_clear_message(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->from(route('admin.categories.index'))
            ->post(route('admin.categories.store'), [
                'name' => 'Kategori Tidak Valid',
                'status' => 'ACTIVE',
                'category_image' => UploadedFile::fake()->create('category.svg', 10, 'image/svg+xml'),
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors([
                'category_image' => 'Gambar kategori harus berformat JPG, PNG, atau WebP.',
            ]);

        $this->assertDatabaseMissing('categories', ['slug' => 'kategori-tidak-valid']);
    }

    public function test_category_form_explains_recommended_image_size(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('1200 × 800 px')
            ->assertSee('Jika kosong, gambar default akan digunakan.');
    }

    public function test_deleting_empty_category_removes_its_managed_image(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $imagePath = '/uploads/categories/22222222-2222-4222-8222-222222222222.webp';
        $absolutePath = $this->uploadRoot.'/categories/22222222-2222-4222-8222-222222222222.webp';
        File::ensureDirectoryExists(dirname($absolutePath));
        File::put($absolutePath, 'category image');
        $category = Category::factory()->create(['image_path' => $imagePath]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertModelMissing($category);
        $this->assertFileDoesNotExist($absolutePath);
    }

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
