<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = number_format(fake()->randomFloat(2, 5, 5000), 2, '.', '');

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_price' => $price,
            'quantity' => $quantity,
            'subtotal' => bcmul($price, (string) $quantity, 2),
            'note' => fake()->optional()->randomElement(['بدون بصل', 'صوص إضافي', 'بدون مخلل']),
        ];
    }
}
