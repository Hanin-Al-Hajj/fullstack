<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'governorates', 'is_active'];

    protected function casts(): array
    {
        return [
            'governorates' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }
}