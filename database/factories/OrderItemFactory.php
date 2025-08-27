<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 3);
        $price = $this->faker->randomFloat(2, 5, 100);
        return [
            'order_id' => Order::factory(),
            'description' => $this->faker->word,
            'quantity' => $qty,
            'unit_price' => $price,
            'line_total' => $qty * $price,
        ];
    }
}
