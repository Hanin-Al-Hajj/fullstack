<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['order_id', 'status', 'note', 'changed_by'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'changed_by');
    }
}