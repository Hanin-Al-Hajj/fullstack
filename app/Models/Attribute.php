<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name', 'slug', 'type'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}