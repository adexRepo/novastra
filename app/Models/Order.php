<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_number', 'customer_id', 'checkout_token', 'customer_name_snapshot', 'customer_email_snapshot', 'address_snapshot', 'phone_snapshot', 'subtotal', 'shipping_total', 'total', 'notes', 'status', 'payment_status', 'stock_deducted', 'stock_restored'];

    protected function casts(): array
    {
        return ['subtotal' => 'integer', 'shipping_total' => 'integer', 'total' => 'integer', 'stock_deducted' => 'boolean', 'stock_restored' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * @param  array{q: string, status: string, payment: string}  $filters
     */
    public function scopeAdminFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'], fn (Builder $query, string $search): Builder => $query->where(fn (Builder $inner): Builder => $inner
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name_snapshot', 'like', "%{$search}%")
                ->orWhere('customer_email_snapshot', 'like', "%{$search}%")))
            ->when($filters['status'], fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['payment'], fn (Builder $query, string $payment): Builder => $query->where('payment_status', $payment));
    }
}
