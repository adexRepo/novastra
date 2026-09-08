<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('NVS-######'),
            'customer_id' => User::factory(),
            'customer_name_snapshot' => fake()->name(),
            'customer_email_snapshot' => fake()->safeEmail(),
            'address_snapshot' => fake()->address(),
            'phone_snapshot' => fake()->numerify('08##########'),
            'subtotal' => 100000,
            'shipping_total' => 25000,
            'total' => 125000,
            'status' => 'PENDING',
            'payment_status' => 'UNPAID',
            'stock_deducted' => false,
            'stock_restored' => false,
        ];
    }
}
