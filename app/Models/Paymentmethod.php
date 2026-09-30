<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['user_id', 'gateway', 'token', 'brand', 'last_four', 'is_default'];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}