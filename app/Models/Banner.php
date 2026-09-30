<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'link_url', 'sort_order', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}