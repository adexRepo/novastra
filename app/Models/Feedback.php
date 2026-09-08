<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = ['order_id', 'customer_id', 'name', 'email', 'rating', 'message'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
