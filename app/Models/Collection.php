<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_product')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('collection_product.sort_order');
    }
}