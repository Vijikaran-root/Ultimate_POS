<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\OrderItem;
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
        $month = date('F');
        $year = date('Y');
        $orders = Order::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$month, $year])->with(['items', 'payments'])->get();
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
        //forloop for above $monthly_profit
        $dailyProfit = OrderItem::selectRaw('DATE(order_items.created_at) as date, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products to access inventory
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventories to get cost
            ->whereRaw('MONTHNAME(order_items.created_at) = ? AND YEAR(order_items.created_at) = ?', [$month, $year]) // Filter by month and year
            ->groupByRaw('DATE(order_items.created_at)')
            ->orderByRaw('DATE(order_items.created_at) DESC')
            ->get();
        $monthly_profit = $dailyProfit->map(function ($i) {
            return $i->profit;
        })->sum();

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
        $cashDetails = Cash::all();
        //get the cash balance
        // $cashPurchases where $cashDetails->name = 'cash purchase'
        $cashPurchases = $cashDetails->where('name', 'Cash Purchases')->sum('value');
        //Paid Suppliers
        $paidSuppliers = $cashDetails->where('name', 'Paid Suppliers')->sum('value');
        //Other Expenses
        $otherExpenses = $cashDetails->where('name', 'Other Expenses')->sum('value');
        //get all Other Expenses from $cashDetails
        $otherExpensesDetails = $cashDetails->where('name', 'Other Expenses');
        //New Capital
        $newCapital = $cashDetails->where('name', 'New Capital')->sum('value');
        //Purchase Return
        $purchaseReturn = $cashDetails->where('name', 'Purchase Return')->sum('value');
        //Drawings
        $drawings = $cashDetails->where('name', 'Drawings')->sum('value');
        // Calculate totals for B/F, C/D, B/D
        //get the current month and year
        $month = date('m');
        $year = date('Y');

        $previousMonth = date('F', strtotime("$year-$month-01 -1 month"));
        $previousYear = date('Y', strtotime("$year-$month-01 -1 month"));

        $previousMonthOrders = Order::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$previousMonth, $previousYear])->get();

        // Calculate the total for the previous month
        $previousMonthTotal = $previousMonthOrders->map(function ($order) {
            return $order->total(); // Assuming the `total()` method calculates the order total
        })->sum();

        $previousCashDetails = Cash::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$previousMonth, $previousYear])->get();
        $previousDebit = $previousMonthTotal + $previousCashDetails->where('name', 'New Capital')->sum('value') + $previousCashDetails->where('name', 'Purchase Return')->sum('value');
        $previousCredit = $previousCashDetails->where('name', 'Cash Purchases')->sum('value') + $previousCashDetails->where('name', 'Paid Suppliers')->sum('value') + $previousCashDetails->where('name', 'Other Expenses')->sum('value') + $previousCashDetails->where('name', 'Drawings')->sum('value');
        $bf = $previousDebit - $previousCredit;
        $sum = $bf + $total + $newCapital + $purchaseReturn; // Total credits
        $sum1 = $cashPurchases + $paidSuppliers + $otherExpenses + $drawings; // Total debits
        $difference = $sum - $sum1;


        $cashinhand = $difference;
        // inventoryValue
        $inventoryValue = Inventory::all()->sum(function ($product) {
            return $product->quantity * $product->cost;
        });

        $totalAssets = $cashinhand + $inventoryValue;

        $capital = $newCapital;
        $netprofit = $monthly_profit;
        $totalEquity = $capital + $netprofit - $drawings;
        $supplierBalance = $inventoryValue - $paidSuppliers - $cashPurchases;
        $totalLiabilities = $supplierBalance;

        return view('home', compact(
            'totalAssets',
            'totalEquity',
            'totalLiabilities',
            'supplierBalance',
            'cashinhand',
            'inventoryValue',
            'capital',
            'netprofit',
            'drawings',
            'difference',
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
