<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = ['name', 'discount_type', 'discount_value', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product')->withTimestamps();
    }

    public function isLive(): bool
    {
        return $this->is_active
            && now()->between($this->starts_at, $this->ends_at);
    }
}