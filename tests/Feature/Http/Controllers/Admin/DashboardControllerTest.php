<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PageView;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_metrics_follow_selected_range(): void
    {
        $this->travelTo('2026-09-08 12:00:00');
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Order::factory()->create(['total' => 100000, 'payment_status' => 'PAID', 'created_at' => now()->subDays(10)]);
        Order::factory()->create(['total' => 200000, 'payment_status' => 'PAID', 'created_at' => now()->subMonths(4)]);
        PageView::create(['path' => '/', 'visitor_id' => 'visitor-recent', 'day' => now()->subDays(5), 'created_at' => now()->subDays(5)]);
        PageView::create(['path' => '/', 'visitor_id' => 'visitor-older', 'day' => now()->subMonths(4), 'created_at' => now()->subMonths(4)]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['range' => '1m']))
            ->assertViewHas('metrics', function (array $metrics): bool {
                return $metrics['Omzet 1 bulan'] === 100000
                    && $metrics['Pesanan 1 bulan'] === 1
                    && $metrics['Pengunjung'] === 1;
            });

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['range' => '6m']))
            ->assertViewHas('metrics', function (array $metrics): bool {
                return $metrics['Omzet 6 bulan'] === 300000
                    && $metrics['Pesanan 6 bulan'] === 2
                    && $metrics['Pengunjung'] === 2;
            });
    }

    public function test_invalid_dashboard_range_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->get(route('admin.dashboard', ['range' => 'all']))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('range');
    }

    public function test_dashboard_operational_data_follows_selected_range(): void
    {
        $this->travelTo('2026-09-08 12:00:00');
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $recentProduct = Product::factory()->create(['name' => 'Produk Bulan Ini']);
        $olderProduct = Product::factory()->create(['name' => 'Produk Lama']);
        $recentOrder = Order::factory()->create(['created_at' => now()->subDays(5)]);
        $olderOrder = Order::factory()->create(['created_at' => now()->subMonths(2)]);
        OrderItem::create(['order_id' => $recentOrder->id, 'product_id' => $recentProduct->id, 'product_name' => $recentProduct->name, 'product_sku' => $recentProduct->sku, 'unit_price' => 10000, 'quantity' => 3, 'line_total' => 30000]);
        OrderItem::create(['order_id' => $olderOrder->id, 'product_id' => $olderProduct->id, 'product_name' => $olderProduct->name, 'product_sku' => $olderProduct->sku, 'unit_price' => 10000, 'quantity' => 10, 'line_total' => 100000]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['range' => '1m']))
            ->assertViewHas('pending', 1)
            ->assertViewHas('unpaid', 1)
            ->assertViewHas('recentOrders', fn ($orders): bool => $orders->count() === 1 && $orders->first()->is($recentOrder))
            ->assertViewHas('topProducts', fn ($products): bool => $products->count() === 1 && $products->first()->is($recentProduct));
    }
}
