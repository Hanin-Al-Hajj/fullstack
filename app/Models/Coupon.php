<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_spend_minor', 'max_uses',
        'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function isValidFor(int $subtotalMinor): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }
        if ($this->min_spend_minor && $subtotalMinor < $this->min_spend_minor) {
            return false;
        }
        return true;
    }
}