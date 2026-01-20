<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isInStock()
    {
        return $this->stock > 0;
    }
}

