<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::all();
        return view('inventory.index', compact('inventory'));
    }
    public function create()
    {
        $products = Product::all();
        return view('inventory.create', compact('products'));
    }
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'product_id' => 'required|exists:products,id', // Ensure the product exists
            'reorder_level' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
        ]);

        // Retrieve the product by its ID
        $product = Product::find($request->product_id);
        if (!$product) {
            return redirect()->back()->withErrors(['product_id' => 'Product not found']);
        }

        // Update product's quantity
        $product->quantity += $request->reorder_level;
        $product->save();

        // Prepare data for inventory creation
        $inventoryData = [
            'product_id' => $request->product_id,
            'reorder_level' => $request->reorder_level,
            'quantity_on_hand' => $request->reorder_level,
            'cost' => $request->cost,
        ];

        // Create a new inventory record
        Inventory::create($inventoryData);

        // Redirect with success message
        return redirect()->route('inventory.index')
            ->with('success', 'Inventory created successfully.');
    }
}
