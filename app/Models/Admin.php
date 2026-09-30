<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function statusChanges()
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by');
    }

    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class);
    }
}