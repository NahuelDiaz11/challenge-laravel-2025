<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_and_list()
    {
        $payload = [
            "client_name" => "Carlos Gómez",
            "items" => [
                ["description" => "Lomo saltado", "quantity" => 1, "unit_price" => 60],
                ["description" => "Inka Kola", "quantity" => 2, "unit_price" => 10],
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);
        $response->assertStatus(201)
            ->assertJsonPath('data.client_name', 'Carlos Gómez')
            ->assertJsonCount(2, 'data.items');

        $list = $this->getJson('/api/orders');
        $list->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_advance_order_to_delivered_deletes_it()
    {
        $order = Order::factory()->create();
        $order->items()->create([
            'description' => 'x',
            'quantity' => 1,
            'unit_price' => 10,
            'line_total' => 10
        ]);
        $order->recalcTotal();

        $res1 = $this->postJson("/api/orders/{$order->id}/advance");
        $res1->assertStatus(200);

        $res2 = $this->postJson("/api/orders/{$order->id}/advance");
        $res2->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
