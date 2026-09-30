<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'price_minor', 'compare_at_price_minor',
        'currency', 'stock_qty', 'backorder_allowed', 'weight_grams',
        'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'backorder_allowed' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_value_product_variant')
            ->withTimestamps();
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function inStock(): bool
    {
        return $this->stock_qty > 0 || $this->backorder_allowed;
    }
}