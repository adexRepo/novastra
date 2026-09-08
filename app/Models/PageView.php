<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = ['path', 'visitor_id', 'day'];

    protected function casts(): array
    {
        return ['day' => 'date'];
    }
}
