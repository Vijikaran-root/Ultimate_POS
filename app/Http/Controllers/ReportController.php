<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Import the PDF facade
use Illuminate\Support\Facades\DB;

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
        $ordersAnnum = Order::selectRaw('YEAR(created_at) as year, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at)')
            ->orderByRaw('YEAR(created_at) DESC')
            ->get();


        return view('report.index', compact('orders', 'ordersAnnum'));
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
        // $topProducts = Product:: top 5 selling products
        // Get the top 5 selling products for the given month and year
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('MONTHNAME(orders.created_at) = ? AND YEAR(orders.created_at) = ?', [$month, $year])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();
        // $bottomProducts = Product:: top 5 lowest selling products

        // Get the bottom 5 selling products for the given month and year
        $bottomProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('MONTHNAME(orders.created_at) = ? AND YEAR(orders.created_at) = ?', [$month, $year])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'asc') // Ascending order to get the lowest selling
            ->take(5)
            ->get();

        $cashinhand = $difference;
        // inventoryValue
        $inventoryValue = Inventory::all()->sum(function ($product) {
            return $product->quantity_on_hand * $product->cost;
        });

        $totalAssets = $cashinhand + $inventoryValue;

        $capital = $newCapital;
        $netprofit = $dailyProfitSum - $otherExpenses;
        $totalEquity = $capital + $netprofit - $drawings;
        $supplierBalance = $inventoryValue - $paidSuppliers - $cashPurchases;
        $totalLiabilities = $supplierBalance;

        return view('report.view', compact(
            'totalAssets',
            'totalEquity',
            'totalLiabilities',
            'supplierBalance',
            'capital',
            'netprofit',
            'inventoryValue',
            'cashinhand',
            'topProducts',
            'bottomProducts',
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
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('MONTHNAME(orders.created_at) = ? AND YEAR(orders.created_at) = ?', [$month, $year])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();
        // $bottomProducts = Product:: top 5 lowest selling products

        // Get the bottom 5 selling products for the given month and year
        $bottomProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('MONTHNAME(orders.created_at) = ? AND YEAR(orders.created_at) = ?', [$month, $year])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'asc') // Ascending order to get the lowest selling
            ->take(5)
            ->get();
        $cashinhand = $difference;
        // inventoryValue
        $inventoryValue = Inventory::all()->sum(function ($product) {
            return $product->quantity_on_hand * $product->cost;
        });

        $totalAssets = $cashinhand + $inventoryValue;

        $capital = $newCapital;
        $netprofit = $dailyProfitSum - $otherExpenses;
        $totalEquity = $capital + $netprofit - $drawings;
        $supplierBalance = $inventoryValue - $paidSuppliers - $cashPurchases;
        $totalLiabilities = $supplierBalance;

        $data = [
            'totalAssets' => $totalAssets,
            'totalEquity' => $totalEquity,
            'totalLiabilities' => $totalLiabilities,
            'supplierBalance' => $supplierBalance,
            'capital' => $capital,
            'netprofit' => $netprofit,
            'inventoryValue' => $inventoryValue,
            'cashinhand' => $cashinhand,
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'month' => $month,
            'year' => $year,
            'total' => $total,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'otherExpensesDetails' => $otherExpensesDetails,
            'otherExpenses' => $otherExpenses,
            'dailyTurnover' => $dailyTurnover,
            'dailyProfit' => $dailyProfit,
            'dailyProfitSum' => $dailyProfitSum,
            'sum' => $sum,
            'sum1' => $sum1,
            'bf1' => $bf1,
            'cd' => $cd,
            'bd' => $bd,
            'drawings' => $drawings,
            'purchaseReturn' => $purchaseReturn,
            'newCapital' => $newCapital,
            'paidSuppliers' => $paidSuppliers,
            'cashPurchases' => $cashPurchases,
            'previousMonth' => $previousMonth,
            'previousYear' => $previousYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
        ];


        // Load the view and generate the PDF
        $pdf = Pdf::loadView('report.download', $data);

        // Set file name
        $fileName = "MonthlyReport{$month}{$year}.pdf";

        // Return the generated PDF as a download
        return $pdf->download($fileName);
    }
    //viewSofp
    public function viewSofp($year)
    {
        $orders = Order::whereRaw('YEAR(created_at) = ?', [$year])->get();
        //get the sum(amount) in orders monthly in $year

        $monthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)')) // Order by actual month number for correct sequence
            ->get();
        $top5MonthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderByDesc('total') // Order by turnover in descending order
            ->limit(5)            // Get the top 5 records
            ->get();
        $lowest5MonthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderBy('total')   // Order by turnover in ascending order
            ->limit(5)           // Get the top 5 lowest records
            ->get();
        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $monthlyProfit = OrderItem::selectRaw('MONTHNAME(order_items.created_at) as month, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products table
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventory table to get cost
            ->whereYear('order_items.created_at', $year) // Filter by year
            ->groupBy(DB::raw('MONTHNAME(order_items.created_at)')) // Group by month name
            ->orderBy(DB::raw('MONTH(order_items.created_at)')) // Order by month number for proper order
            ->get();
        $monthlyProfitSum = $monthlyProfit->map(function ($i) {
            return $i->profit;
        })->sum();
        $dailyProfit = OrderItem::selectRaw('DATE(order_items.created_at) as date, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products to access inventory
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventories to get cost
            ->whereRaw('YEAR(order_items.created_at) = ?', [$year]) // Filter by month and year
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
            ->whereRaw('YEAR(created_at) = ?', [$year]) // Filter by month and year
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();
        //get the cash purchases from cash model
        $cashDetails = Cash::whereRaw('YEAR(created_at) = ?', [$year])->get();
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
        // Calculate totals for B/F, C/D, B/D previous year and next year
        $previousYear = date('Y', strtotime("$year-01-01 -1 year"));
        $nextYear = date('Y', strtotime("$year-01-01 +1 year"));
        // Fetch orders from the previous year
        $previousYearOrders = Order::whereRaw('YEAR(created_at) = ?', [$previousYear])->get();
        // Calculate the total for the previous year
        $previousYearTotal = $previousYearOrders->map(function ($order) {
            return $order->total(); // Assuming the `total()` method calculates the order total
        })->sum();
        $sum = $previousYearTotal + $total + $newCapital + $purchaseReturn;
        $sum1 = $cashPurchases + $paidSuppliers + $otherExpenses + $drawings;
        $difference = abs($sum - $sum1);
        $previousCashDetails = Cash::whereRaw('YEAR(created_at) = ?', [$previousYear])->get();
        $previousDebit = $previousYearTotal + $previousCashDetails->where('name', 'New Capital')->sum('value') + $previousCashDetails->where('name', 'Purchase Return')->sum('value');
        $previousCredit = $previousCashDetails->where('name', 'Cash Purchases')->sum('value') + $previousCashDetails->where('name', 'Paid Suppliers')->sum('value') + $previousCashDetails->where('name', 'Other Expenses')->sum('value') + $previousCashDetails->where('name', 'Drawings')->sum('value');
        $bf = $previousDebit - $previousCredit;
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
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('YEAR(orders.created_at) = ?', [$year])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();
        // $bottomProducts = Product:: top 5 lowest selling products
        $bottomProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('YEAR(orders.created_at) = ?', [$year])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'asc') // Ascending order to get the lowest selling
            ->take(5)
            ->get();

        $cashinhand = $difference;
        // inventoryValue
        $inventoryValue = Inventory::all()->sum(function ($product) {
            return $product->reorder_level * $product->cost;
        });

        $totalAssets = $cashinhand + $inventoryValue - $cogs;

        $capital = $newCapital;
        $netprofit = $dailyProfitSum - $otherExpenses;
        $totalEquity = $capital + $netprofit - $drawings;
        $supplierBalance = $inventoryValue - $paidSuppliers - $cashPurchases;
        $totalLiabilities = $supplierBalance;
        return view(
            'report.sofp',
            ['year' => $year,],
            compact(
                'monthlyProfitSum',
                'monthlyProfit',
                'lowest5MonthlyTurnover',
                'top5MonthlyTurnover',
                'monthlyTurnover',
                'totalAssets',
                'totalEquity',
                'totalLiabilities',
                'supplierBalance',
                'capital',
                'netprofit',
                'inventoryValue',
                'cashinhand',
                'topProducts',
                'bottomProducts',
                'dailyProfitSum',
                'dailyProfit',
                'previousYear',
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
                'year',
                'total',
                'receivedAmount',
                'cogs',
                'grossProfit'
            )
        );
    }
    public function downloadSofp($year)
    {
        $orders = Order::whereRaw('YEAR(created_at) = ?', [$year])->get();
        $monthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)')) // Order by actual month number for correct sequence
            ->get();
        $top5MonthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderByDesc('total') // Order by turnover in descending order
            ->limit(5)            // Get the top 5 records
            ->get();
        $lowest5MonthlyTurnover = OrderItem::selectRaw('MONTHNAME(created_at) as month, SUM(price) as total')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTHNAME(created_at)'))
            ->orderBy('total')   // Order by turnover in ascending order
            ->limit(5)           // Get the top 5 lowest records
            ->get();
        $monthlyProfit = OrderItem::selectRaw('MONTHNAME(order_items.created_at) as month, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products table
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventory table to get cost
            ->whereYear('order_items.created_at', $year) // Filter by year
            ->groupBy(DB::raw('MONTHNAME(order_items.created_at)')) // Group by month name
            ->orderBy(DB::raw('MONTH(order_items.created_at)')) // Order by month number for proper order
            ->get();
        $monthlyProfitSum = $monthlyProfit->map(function ($i) {
            return $i->profit;
        })->sum();
        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $dailyProfit = OrderItem::selectRaw('DATE(order_items.created_at) as date, SUM(order_items.price - (order_items.quantity * inventory.cost)) as profit')
            ->join('products', 'order_items.product_id', '=', 'products.id') // Join products to access inventory
            ->join('inventory', 'products.id', '=', 'inventory.product_id') // Join inventories to get cost
            ->whereRaw('YEAR(order_items.created_at) = ?', [$year]) // Filter by month and year
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
            ->whereRaw('YEAR(created_at) = ?', [$year]) // Filter by month and year
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at) DESC')
            ->get();
        //get the cash purchases from cash model
        $cashDetails = Cash::whereRaw('YEAR(created_at) = ?', [$year])->get();
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
        // Calculate totals for B/F, C/D, B/D previous year and next year
        $previousYear = date('Y', strtotime("$year-01-01 -1 year"));
        $nextYear = date('Y', strtotime("$year-01-01 +1 year"));
        // Fetch orders from the previous year
        $previousYearOrders = Order::whereRaw('YEAR(created_at) = ?', [$previousYear])->get();
        // Calculate the total for the previous year
        $previousYearTotal = $previousYearOrders->map(function ($order) {
            return $order->total(); // Assuming the `total()` method calculates the order total
        })->sum();
        $sum = $previousYearTotal + $total + $newCapital + $purchaseReturn;
        $sum1 = $cashPurchases + $paidSuppliers + $otherExpenses + $drawings;
        $difference = abs($sum - $sum1);
        $previousCashDetails = Cash::whereRaw('YEAR(created_at) = ?', [$previousYear])->get();
        $previousDebit = $previousYearTotal + $previousCashDetails->where('name', 'New Capital')->sum('value') + $previousCashDetails->where('name', 'Purchase Return')->sum('value');
        $previousCredit = $previousCashDetails->where('name', 'Cash Purchases')->sum('value') + $previousCashDetails->where('name', 'Paid Suppliers')->sum('value') + $previousCashDetails->where('name', 'Other Expenses')->sum('value') + $previousCashDetails->where('name', 'Drawings')->sum('value');
        $bf = $previousDebit - $previousCredit;
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
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('YEAR(orders.created_at) = ?', [$year])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();
        // $bottomProducts = Product:: top 5 lowest selling products
        $bottomProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->whereRaw('YEAR(orders.created_at) = ?', [$year])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'asc') // Ascending order to get the lowest selling
            ->take(5)
            ->get();

        $cashinhand = $difference;
        // inventoryValue
        $inventoryValue = Inventory::all()->sum(function ($product) {
            return $product->reorder_level * $product->cost;
        });

        $totalAssets = $cashinhand + $inventoryValue - $cogs;

        $capital = $newCapital;
        $netprofit = $dailyProfitSum - $otherExpenses;
        $totalEquity = $capital + $netprofit - $drawings;
        $supplierBalance = $inventoryValue - $paidSuppliers - $cashPurchases;
        $totalLiabilities = $supplierBalance;
        $data = [
            'monthlyProfitSum' => $monthlyProfitSum,
            'monthlyProfit' => $monthlyProfit,
            'lowest5MonthlyTurnover' => $lowest5MonthlyTurnover,
            'top5MonthlyTurnover' => $top5MonthlyTurnover,
            'monthlyTurnover' => $monthlyTurnover,
            'year' => $year,
            'totalAssets' => $totalAssets,
            'totalEquity' => $totalEquity,
            'totalLiabilities' => $totalLiabilities,
            'supplierBalance' => $supplierBalance,
            'capital' => $capital,
            'netprofit' => $netprofit,
            'inventoryValue' => $inventoryValue,
            'cashinhand' => $cashinhand,
            'topProducts' => $topProducts,
            'bottomProducts' => $bottomProducts,
            'dailyProfitSum' => $dailyProfitSum,
            'dailyProfit' => $dailyProfit,
            'previousYear' => $previousYear,
            'nextYear' => $nextYear,
            'otherExpensesDetails' => $otherExpensesDetails,
            'sum' => $sum,
            'sum1' => $sum1,
            'difference' => $difference,
            'bf' => $bf,
            'bf1' => $bf1,
            'cd' => $cd,
            'bd' => $bd,
            'drawings' => $drawings,
            'purchaseReturn' => $purchaseReturn,
            'newCapital' => $newCapital,
            'otherExpenses' => $otherExpenses,
            'paidSuppliers' => $paidSuppliers,
            'cashPurchases' => $cashPurchases,
            'dailyTurnover' => $dailyTurnover,
            'orders' => $orders,
            'year' => $year,
            'total' => $total,
            'receivedAmount' => $receivedAmount,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
        ];
        $pdf = Pdf::loadView('report.sofp-download', $data);
        $fileName = "AnnualReport{$year}.pdf";
        return $pdf->download($fileName);
    }
}
