<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description',
        'seo_title', 'seo_description', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}