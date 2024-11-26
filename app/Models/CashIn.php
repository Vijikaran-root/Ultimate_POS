<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashIn extends Model
{
    use HasFactory;
    protected $table = 'cash_in';
    protected $fillable = [
        'id',
        'order_id',
        'amount',
        'created_at',
        'updated_at',
    ];
    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
