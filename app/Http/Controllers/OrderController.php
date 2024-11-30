<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = new Order();
        if ($request->start_date) {
            $orders = $orders->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $orders = $orders->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $orders = $orders->with(['items', 'payments', 'customer'])->latest()->paginate(20);

        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

        return view('orders.index', compact('orders', 'total', 'receivedAmount'));
    }

    public function store(OrderStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user()->id,
            ]);

            // Retrieve cart items
            $cart = $request->user()->cart()->get();

            foreach ($cart as $item) {
                // Create order items
                $order->items()->create([
                    'price' => $item->price * $item->pivot->quantity,
                    'quantity' => $item->pivot->quantity,
                    'product_id' => $item->id,
                ]);
            }

            // Deduct stock using FIFO
            $this->deductfifostocks($order);

            // Clear the user's cart
            $request->user()->cart()->detach();

            // Record the payment
            $order->payments()->create([
                'amount' => $request->amount,
                'user_id' => $request->user()->id,
            ]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    //deductfifostocks functions
    public function deductfifostocks(Order $order)
    {
        // Retrieve order items with related products and inventories
        $order_items = $order->items()->with(['product', 'product.inventories'])->get();

        foreach ($order_items as $item) {
            $remainingQuantity = $item->quantity;

            // Deduct from inventories using FIFO logic
            foreach ($item->product->inventories()->orderBy('created_at')->get() as $inventory) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                if ($inventory->quantity_on_hand >= $remainingQuantity) {
                    $inventory->quantity_on_hand -= $remainingQuantity;
                    $inventory->save();
                    $remainingQuantity = 0;
                } else {
                    $remainingQuantity -= $inventory->quantity_on_hand;
                    $inventory->quantity_on_hand = 0;
                    $inventory->save();
                }
            }

            // Update the product's total stock
            $item->product->quantity -= $item->quantity;
            $item->product->save();
        }
    }


    public function show(Order $order)
    {
        //order_items table data for the requested order
        $order_items = OrderItem::where('order_id', $order->id)->with('product')->get();
        $orders = Order::where('id', $order->id)->get();
        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $balance = $receivedAmount - $total;
        return view('orders.show', compact('order_items', 'total', 'receivedAmount', 'balance'));
    }
    //delete order
    public function delete(Order $order)
    {
        $order->delete();
        return redirect()->back();
    }
}
