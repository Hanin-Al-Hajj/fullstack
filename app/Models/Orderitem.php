<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'product_name', 'variant_label',
        'sku', 'unit_price_minor', 'qty', 'line_total_minor',
    ];

    protected function casts(): array
    {
        return ['product_name' => 'array'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Nullable: the variant may have been deleted since the order was placed.
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}