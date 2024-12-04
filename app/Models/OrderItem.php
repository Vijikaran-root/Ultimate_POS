<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'price',
        'quantity',
        'product_id',
        'order_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function orders()
    {
        return $this->belongsTo(Order::class);
    }
    //inventory
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
    public function costOfGoodsSold()
    {
        // return $this->quantity * $this->inventory->cost; get the first value
        return $this->quantity * $this->inventory->cost;
    }
}
