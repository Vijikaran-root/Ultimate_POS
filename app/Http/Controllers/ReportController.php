<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Initialize the Order query
        $orders = new Order();

        // Apply date range filters if provided
        if ($request->start_date) {
            $orders = $orders->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $orders = $orders->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $items = $orders->items();

        // Fetch orders and group by month
        $monthlySales = $items->selectRaw('DATE_FORMAT(created_at, "%M %Y") as month, SUM(price) as total_sales')
            ->groupBy('month')
            ->orderByRaw('MIN(created_at) DESC')
            ->get();

        // Calculate total sales and received amounts
        $orders = $orders->with(['items', 'payments', 'customer'])->get();
        $total = $orders->map(function ($order) {
            return $order->total();
        })->sum();

        $receivedAmount = $orders->map(function ($order) {
            return $order->receivedAmount();
        })->sum();

        // Pass data to the view
        return view('report.index', compact('monthlySales', 'total', 'receivedAmount'));
    }
}
