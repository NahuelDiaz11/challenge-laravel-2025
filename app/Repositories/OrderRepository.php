<?php
namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function allActive()
    {
        return Order::where('status', '!=', 'delivered')->with('items')->orderBy('created_at','desc')->get();
    }

    public function find(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    public function save(Order $order): void
    {
        $order->save();
    }
}
