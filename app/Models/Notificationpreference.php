<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'order_updates', 'promotions'];

    protected function casts(): array
    {
        return [
            'order_updates' => 'boolean',
            'promotions' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}