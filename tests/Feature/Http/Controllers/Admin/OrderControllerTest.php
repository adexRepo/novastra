<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Order;
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
}
