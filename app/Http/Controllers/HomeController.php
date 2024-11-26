<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $orders = Order::with(['items', 'payments'])->get();
        $customers_count = Customer::count();
        $products_count = Product::count();
        //monthly sales
        $monthly_sales = $orders->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')) . ' 00:00:00')->map(function ($i) {
            if ($i->receivedAmount() > $i->total()) {
                return $i->total();
            }
            return $i->receivedAmount();
        })->sum();
        //daily sales
        $daily_sales = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
            if ($i->receivedAmount() > $i->total()) {
                return $i->total();
            }
            return $i->receivedAmount();
        })->sum();
        //monthly profit(order_item.price - (order_item.quantity*product.cost)) 
        $monthly_profit = $orders->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')) . ' 00:00:00')->map(function ($i) {
            return $i->items->map(function ($j) {
                return ($j->price - ($j->quantity * $j->product->cost));
            })->sum();
        })->sum();
        //daily profit(order_item.price - (order_item.quantity*product.cost))
        $daily_profit = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
            return $i->items->map(function ($j) {
                return ($j->price - ($j->quantity * $j->product->cost));
            })->sum();
        })->sum();
        //monthly orders
        $monthly_orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')) . ' 00:00:00')->count();
        //daily orders
        $daily_orders = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->count();

        //inventory balance
        $inventory_balance = Product::sum('quantity');
        //inventory value for each product sum of (quantity * cost)
        $inventory_value = Product::all()->sum(function ($product) {
            return $product->quantity * $product->cost;
        });




        return view('home', compact(
            'orders',
            'customers_count',
            'products_count',
            'monthly_sales',
            'daily_sales',
            'monthly_profit',
            'daily_profit',
            'monthly_orders',
            'daily_orders',
            'inventory_balance',
            'inventory_value'
        ));
    }
}
