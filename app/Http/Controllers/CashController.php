<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function index()
    {
        $cash = Cash::orderBy('created_at', 'desc')->get();
        return view('cash.index', compact('cash'));
    }
    public function create()
    {
        return view('cash.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'value' => 'required',
            'type' => 'required',

        ]);
        Cash::create($request->all());
        return redirect()->route('cash.index')->with('success', 'Cash created successfully.');
    }
    public function edit(Cash $cash)
    {
        return view('cash.edit', compact('cash'));
    }
    public function update(Request $request, Cash $cash)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'value' => 'required|numeric',
            'type' => 'required|boolean',
        ]);
        $cash->update($request->all());
        return redirect()->route('cash.index')->with('success', 'Cash updated successfully.');
    }
    public function destroy(Cash $cash)
    {
        $cash->delete();
        return redirect()->route('cash.index')->with('success', 'Cash deleted successfully.');
    }
}
