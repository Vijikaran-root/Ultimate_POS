<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        //cost of goods sold
        $cogs = $orders->map(function ($order) {
            if ($order->orderItems->isEmpty()) {
                return 0; // No items, COGS is 0
            }

            return $order->orderItems->map(function ($item) {
                // Get the first inventory related to the product (adjust if needed to get the correct inventory)
                $inventory = $item->product->inventories->first(); // Assuming 'inventories' is a relationship

                // Check if inventory exists and calculate COGS
                if ($inventory) {
                    return $inventory->cost * $item->quantity;
                }

                // Return 0 if no inventory exists
                return 0;
            })->sum();
        })->sum();



        $grossProfit = $total - $cogs;
        return view('report.view', compact('orders', 'month', 'year', 'total', 'receivedAmount', 'cogs', 'grossProfit'));
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
