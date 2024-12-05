<?php

namespace App\Http\Controllers;

use App\Models\CashIn;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashinController extends Controller
{
    public function index()
    {
        $cashin = CashIn::all();
        return view('due.index', compact('cashin'));
    }
    public function create()
    {
        // $orders = OrderItem::all(); where payments.order_id.amount != order_items.price
        $orders = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->select(
                'orders.id',
                'orders.customer_id',
                'payments.amount',
                DB::raw('SUM(order_items.price) as total_price')
            )
            ->groupBy('orders.id', 'orders.customer_id', 'payments.amount') // Include all non-aggregated columns
            ->havingRaw('SUM(order_items.price) != payments.amount')
            ->get();



        return view('due.create', compact('orders'));
    }
    public function store(Request $request)
    {
        $cashin = new CashIn;
        $order = Order::find($request->order_id);
        $cashin->amount = $request->amount;
        $cashin->order_id = $request->order_id;
        $payment = $order->payments()->where('order_id', $request->order_id)->first();
        $payment->amount = $payment->amount + $request->amount;
        $payment->save();
        $cashin->save();
        return redirect()->route('due.index');
    }
    public function edit($id)
    {
        $cashin = CashIn::find($id);
        return view('due.edit', compact('cashin'));
    }
    public function update(Request $request, $id)
    {
        $cashin = CashIn::find($id);
        $cashin->cashin = $request->cashin;
        $cashin->save();
        return redirect()->route('due.index');
    }
    public function destroy($id)
    {
        $cashin = CashIn::find($id);
        $cashin->delete();
        return redirect()->route('due.index');
    }
}
