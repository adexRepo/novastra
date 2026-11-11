<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const DEFAULT_IMAGE_PATH = '/uploads/products/novastra-fresh-collection.webp';

    protected $fillable = ['name', 'slug', 'description', 'image_path', 'status'];

    public function imageUrl(): string
    {
        return $this->image_path ?: self::DEFAULT_IMAGE_PATH;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
