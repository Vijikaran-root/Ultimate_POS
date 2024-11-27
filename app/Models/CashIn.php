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
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function getCustomerName()
    {
        //get customer name from customer table for requested order
        $customer = Customer::where('id', $this->orders->customer_id)->first();
        return $customer->first_name . ' ' . $customer->last_name;
    }
}
