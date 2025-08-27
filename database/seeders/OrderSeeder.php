<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()->count(5)->create()->each(function ($order) {
            OrderItem::factory()->count(2)->create(['order_id' => $order->id]);
            $order->recalcTotal();
        });
    }
}
