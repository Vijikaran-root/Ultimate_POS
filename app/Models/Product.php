<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'cost',
        'price',
        'quantity',
        'status'
    ];
    //inventory
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
