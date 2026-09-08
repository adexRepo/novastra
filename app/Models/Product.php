<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'sku', 'description', 'short_description', 'price', 'stock', 'image_path', 'status', 'featured'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock' => 'integer', 'featured' => 'boolean', 'is_deleted' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->visible()->where('status', 'ACTIVE');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
