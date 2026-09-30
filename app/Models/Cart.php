<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_token', 'currency'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotalMinor(): int
    {
        return $this->items->sum(fn (CartItem $item) => $item->unit_price_minor * $item->qty);
    }
}