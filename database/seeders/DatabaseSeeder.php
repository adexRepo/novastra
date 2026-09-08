<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPasswordHash = config('novastra.admin.password_hash');
        $adminPassword = config('novastra.admin.password') ?: (app()->environment('local') ? 'adminnovastra123' : null);

        if (! $adminPasswordHash && ! $adminPassword) {
            throw new RuntimeException('Set ADMIN_PASSWORD_HASH or ADMIN_PASSWORD before seeding this environment.');
        }

        if ($adminPasswordHash && ! Hash::isHashed($adminPasswordHash)) {
            throw new RuntimeException('ADMIN_PASSWORD_HASH must contain a valid password hash.');
        }

        if (app()->environment('local')) {
            $customerPassword = config('novastra.customer.password') ?: 'novastra123';
            User::updateOrCreate(['username' => config('novastra.customer.username')], ['name' => config('novastra.customer.name'), 'email' => config('novastra.customer.email'), 'password' => Hash::make($customerPassword), 'role' => 'CUSTOMER']);
        }

        User::updateOrCreate(['username' => config('novastra.admin.username')], ['name' => 'Admin Novastra', 'email' => config('novastra.admin.email'), 'password' => $adminPasswordHash ?: Hash::make($adminPassword), 'role' => 'ADMIN']);

        $categories = collect([['Ayam & Daging', 'ayam-daging'], ['Ikan & Seafood', 'ikan-seafood'], ['Sayur & Buah', 'sayur-buah'], ['Bumbu & Rempah', 'bumbu-rempah']])->mapWithKeys(function ($item) {
            $category = Category::updateOrCreate(['slug' => $item[1]], ['name' => $item[0], 'description' => "Bahan {$item[0]} segar untuk masak sehari-hari.", 'status' => 'ACTIVE']);

            return [$item[1] => $category];
        });
        $products = [
            ['Dada Ayam Fillet 500 g', 'dada-ayam-fillet-500g', 'NST-AYM-001', 'ayam-daging', 42000, 24, true],
            ['Fillet Ikan Dori 500 g', 'fillet-ikan-dori-500g', 'NST-IKN-002', 'ikan-seafood', 48000, 11, true],
            ['Pakcoy Segar 250 g', 'pakcoy-segar-250g', 'NST-SYR-003', 'sayur-buah', 12000, 32, true],
            ['Paket Bumbu Dasar Merah', 'paket-bumbu-dasar-merah', 'NST-BMB-004', 'bumbu-rempah', 24000, 4, true],
            ['Ayam Potong 8 ±1 kg', 'ayam-potong-8-1kg', 'NST-AYM-005', 'ayam-daging', 58000, 18, false],
            ['Fillet Ikan Kakap 500 g', 'fillet-ikan-kakap-500g', 'NST-IKN-006', 'ikan-seafood', 62000, 9, false],
            ['Paket Sayur Sop 500 g', 'paket-sayur-sop-500g', 'NST-SYR-007', 'sayur-buah', 18000, 27, false],
            ['Bawang Putih Kupas 250 g', 'bawang-putih-kupas-250g', 'NST-BMB-008', 'bumbu-rempah', 20000, 0, false],
        ];
        foreach ($products as [$name,$slug,$sku,$category,$price,$stock,$featured]) {
            Product::updateOrCreate(['sku' => $sku], ['category_id' => $categories[$category]->id, 'name' => $name, 'slug' => $slug, 'short_description' => "{$name} yang segar, bersih, dan siap diolah.", 'description' => "{$name} dipilih dan ditangani dengan bersih agar praktis untuk kebutuhan masak keluarga.", 'price' => $price, 'stock' => $stock, 'featured' => $featured, 'status' => 'ACTIVE', 'image_path' => '/uploads/products/novastra-fresh-collection.webp']);
        }
    }
}
