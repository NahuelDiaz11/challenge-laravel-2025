<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'from_status', 'to_status'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
