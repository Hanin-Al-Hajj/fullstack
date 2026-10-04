<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value', 'meta'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'meta' => 'array',
        ];
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'attribute_value_product_variant')
            ->withTimestamps();
    }
}