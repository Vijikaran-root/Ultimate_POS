<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        //get the month names where the orders are placed
        $orders = Order::selectRaw('MONTHNAME(created_at) as month, YEAR(created_at) as year, count(*) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at), MONTHNAME(created_at)')
            ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
            ->get();
        //get the daily turnover sales


        return view('report.index', compact('orders'));
    }
    public function viewReport($month, $year)
    {
        // Fetch all orders for the selected month and year
        $orders = Order::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$month, $year])->get();
        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

        $dailyProfit = OrderItem::selectRaw('DATE(order_items.created_at) as date, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products to access inventory
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventories to get cost
            ->whereRaw('MONTHNAME(order_items.created_at) = ? AND YEAR(order_items.created_at) = ?', [$month, $year]) // Filter by month and year
            ->groupByRaw('DATE(order_items.created_at)')
            ->orderByRaw('DATE(order_items.created_at) DESC')
            ->get();
        //get the sum of $dailyProfit
        $dailyProfitSum = $dailyProfit->map(function ($i) {
            return $i->profit;
        })->sum();
        $cogs = $total - $dailyProfitSum;
        $grossProfit = $total - $cogs;
        $dailyTurnover = OrderItem::selectRaw('DATE(created_at) as date, SUM(price) as total')
            ->whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$month, $year]) // Filter by month and year
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();

        //get the cash purchases from cash model
        $cashDetails = Cash::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$month, $year])->get();
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

        $previousMonth = date('F', strtotime("$year-$month-01 -1 month"));
        $previousYear = date('Y', strtotime("$year-$month-01 -1 month"));

        $nextMonth = date('F', strtotime("$year-$month-01 +1 month"));
        $nextYear = date('Y', strtotime("$year-$month-01 +1 month"));
        // Fetch orders from the previous month

        $previousMonthOrders = Order::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$previousMonth, $previousYear])->get();

        // Calculate the total for the previous month
        $previousMonthTotal = $previousMonthOrders->map(function ($order) {
            return $order->total(); // Assuming the `total()` method calculates the order total
        })->sum();
        $sum = $previousMonthTotal + $total + $newCapital + $purchaseReturn; // Total credits
        $sum1 = $cashPurchases + $paidSuppliers + $otherExpenses + $drawings; // Total debits
        $difference = abs($sum - $sum1);
        $previousCashDetails = Cash::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$previousMonth, $previousYear])->get();
        $previousDebit = $previousMonthTotal + $previousCashDetails->where('name', 'New Capital')->sum('value') + $previousCashDetails->where('name', 'Purchase Return')->sum('value');
        $previousCredit = $previousCashDetails->where('name', 'Cash Purchases')->sum('value') + $previousCashDetails->where('name', 'Paid Suppliers')->sum('value') + $previousCashDetails->where('name', 'Other Expenses')->sum('value') + $previousCashDetails->where('name', 'Drawings')->sum('value');
        $bf = $previousDebit - $previousCredit;
        // Check if credit or debit is higher
        if ($sum > $sum1) {
            //$bf for previous month $bd
            $bf1 = $bf;

            $cd = $sum - $sum1;       // C/D matches total credit
            $bd = $cd; // B/D equals debit side adjusted for difference
        } else {
            $bf1 = $bf;      // B/F is based on the larger debit
            $cd = $sum1 - $sum;      // C/D matches total debit
            $bd = $cd; // B/D equals credit side adjusted for difference
        }
        return view('report.view', compact(
            'dailyProfitSum',
            'dailyProfit',
            'previousMonth',
            'previousYear',
            'nextMonth',
            'nextYear',
            'otherExpensesDetails',
            'sum',
            'sum1',
            'difference',
            'bf',
            'bf1',
            'cd',
            'bd',
            'drawings',
            'purchaseReturn',
            'newCapital',
            'otherExpenses',
            'paidSuppliers',
            'cashPurchases',
            'dailyTurnover',
            'orders',
            'month',
            'year',
            'total',
            'receivedAmount',
            'cogs',
            'grossProfit'
        ));
    }

    public function downloadReport($month, $year)
    {
        // Fetch all orders for the selected month and year
        $orders = Order::whereRaw('MONTHNAME(created_at) = ? AND YEAR(created_at) = ?', [$month, $year])->get();

        // Generate CSV content
        $csvData = "Order ID,User ID,Total Amount,Date\n";
        foreach ($orders as $order) {
            $csvData .= "{$order->id},{$order->user_id},{$order->total_amount},{$order->created_at}\n";
        }

        $fileName = "report_{$month}_{$year}.csv";

        // Return the CSV as a downloadable response
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$fileName\"");
    }
}
