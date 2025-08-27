<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class OrderService
{
    private OrderRepository $repo;
    private string $cacheKey = 'orders:active';
    private int $cacheTtl = 30; // seconds

    public function __construct(OrderRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listActive()
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, fn() => $this->repo->allActive());
    }

    public function createOrder(string $clientName, array $items): Order
    {
        return DB::transaction(function () use ($clientName, $items) {
            $order = $this->repo->create([
                'client_name' => $clientName,
                'status' => 'initiated',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($items as $it) {
                $line = $it['quantity'] * $it['unit_price'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'description' => $it['description'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'line_total' => $line,
                ]);
                $total += $line;
            }

            $order->total = $total;
            $this->repo->save($order);

            // Clean cache so next list is fresh
            Cache::forget($this->cacheKey);

            return $order->load('items');
        });
    }

    public function advanceStatus(int $id): Order|bool
    {
        return DB::transaction(function () use ($id) {
            $order = $this->repo->find($id);
            if (!$order) {
                throw new InvalidArgumentException("Order not found");
            }

            $from = $order->status;
            $to = match($from) {
                'initiated' => 'sent',
                'sent' => 'delivered',
                default => throw new InvalidArgumentException("Cannot advance from status {$from}")
            };

            // log
            OrderLog::create([
                'order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $to,
            ]);

            if ($to === 'delivered') {
                // delete order (and cascade items & logs)
                $this->repo->delete($order);
                Cache::forget($this->cacheKey);
                return true;
            }

            $order->status = $to;
            $this->repo->save($order);
            Cache::forget($this->cacheKey);
            return $order->fresh('items');
        });
    }

    public function getById(int $id): ?Order
    {
        return $this->repo->find($id);
    }
}
