<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'guest_email', 'guest_phone', 'status',
        'payment_status', 'payment_method', 'currency', 'exchange_rate',
        'subtotal_minor', 'shipping_minor', 'discount_minor', 'tax_minor',
        'total_minor', 'coupon_id', 'shipping_rate_id', 'shipping_method_label',
        'shipping_address', 'billing_address', 'notes', 'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'placed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function isGuestOrder(): bool
    {
        return is_null($this->user_id);
    }
}