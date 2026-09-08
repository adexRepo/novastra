<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = ['feedback_id', 'display_name', 'customer_type', 'rating', 'content', 'status', 'featured', 'sort_order'];

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'rating' => 'integer', 'sort_order' => 'integer'];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }
}
