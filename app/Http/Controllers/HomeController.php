<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
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
        $monthly_sales = $orders->whereBetween('created_at', [
            date('Y-m-01 00:00:00'), // First day of the current month
            date('Y-m-t 23:59:59'),  // Last day of the current month
        ])->map(function ($i) {
            return $i->total();
        })->sum();

        //daily sales
        $daily_sales = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
            return $i->total();
        })->sum();

        //monthly profit(order_item.price - (order_item.quantity*product.cost)) 
        $monthly_profit = $orders->whereBetween('created_at', [
            date('Y-m-01 00:00:00'), // First day of the current month
            date('Y-m-t 23:59:59'),  // Last day of the current month
        ])->map(function ($order) {
            return $order->items->map(function ($item) {
                // Get the inventory related to the product (adjust logic if necessary)
                $inventory = $item->product->inventories->first(); // Assuming 'inventories' is a relationship
                // If no inventory, set cost to 0

                // Calculate profit for the item
                return ($item->price) - ($item->quantity * $inventory->cost);
            })->sum(); // Sum the profit for all items in the order
        })->sum(); // Sum the profit for all orders in the current month


        //daily profit(order_item.price - (order_item.quantity*product.cost))
        $daily_profit = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')
            ->map(function ($order) {
                return $order->items->map(function ($item) {
                    $inventory = $item->product->inventories->first(); // Access the inventory via the product relationship
                    $inventoryCost = $inventory ? $inventory->cost : 0; // Handle null inventory
                    return ($item->price - ($item->quantity * $inventoryCost));
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
        //total pending due
        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $total_pending_due = $total - $receivedAmount;
        //suppliercount
        $supplier_count = Supplier::all()->count();

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
            'inventory_value',
            'total_pending_due',
            'supplier_count'
        ));
    }
}
