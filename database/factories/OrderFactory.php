<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
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
        $amount = number_format(fake()->randomFloat(2, 10, 50000), 2, '.', '');

        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'customer_name' => fake()->optional()->name(),
            'customer_phone' => fake()->optional()->numerify('09########'),
            'notes' => fake()->optional()->sentence(),
            'subtotal' => $amount,
            'total' => $amount,
            'currency' => 'ل.س',
            'status' => OrderStatus::Pending,
            'whatsapp_sent_at' => null,
        ];
    }
}
