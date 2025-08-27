<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class OrderService
{
    private OrderRepository $repo;
    private string $cacheKey = 'orders:active';
    private int $cacheTtl = 30; 

    public function __construct(OrderRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listActive()
    {
        try {
            return Cache::remember($this->cacheKey, $this->cacheTtl, fn() => $this->repo->allActive());
        } catch (Exception $e) {
            Log::error('Error retrieving active orders', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to retrieve active orders');
        }
    }

    public function createOrder(string $clientName, array $items): Order
    {
        try {
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

                Cache::forget($this->cacheKey);

                return $order->load('items');
            });
        } catch (Throwable $e) {
            Log::error('Error creating order', [
                'client_name' => $clientName,
                'items_count' => count($items),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Cache::forget($this->cacheKey);

            throw new Exception('Failed to create order: ' . $e->getMessage());
        }
    }

    public function advanceStatus(int $id): Order|bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $order = $this->repo->find($id);
                if (!$order) {
                    throw new InvalidArgumentException("Order not found");
                }

                $from = $order->status;
                $to = match ($from) {
                    'initiated' => 'sent',
                    'sent' => 'delivered',
                    default => throw new InvalidArgumentException("Cannot advance from status {$from}")
                };

                OrderLog::create([
                    'order_id' => $order->id,
                    'from_status' => $from,
                    'to_status' => $to,
                ]);

                if ($to === 'delivered') {
                    $this->repo->delete($order);
                    Cache::forget($this->cacheKey);
                    return true;
                }

                $order->status = $to;
                $this->repo->save($order);
                Cache::forget($this->cacheKey);
                return $order->fresh('items');
            });
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Error advancing order status', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Cache::forget($this->cacheKey);

            throw new Exception('Failed to advance order status: ' . $e->getMessage());
        }
    }

    public function getById(int $id): ?Order
    {
        try {
            return $this->repo->find($id);
        } catch (Exception $e) {
            Log::error('Error retrieving order by ID', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to retrieve order');
        }
    }
}
