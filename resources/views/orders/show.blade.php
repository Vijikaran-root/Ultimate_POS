@extends('layouts.admin')

@section('title', 'Orders List')
@section('content-header', 'Order List')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>MRP</th>
                        <th>Our Price</th>
                        <th>Quantity</th>
                        <th>Total</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($order_items as $order)
                    <tr>
                        <td>{{ $order->order_id }}</td>
                        <td>{{ $order->product->name }}</td>
                        <td>{{ config('settings.currency_symbol') }} {{ $order->product->cost }}</td>
                        <td>{{ config('settings.currency_symbol') }} {{ $order->price / $order->quantity }}</td>
                        <td>{{ $order->quantity }}</td>
                        <td>{{ config('settings.currency_symbol') }} {{ number_format($order->price, 2) }}</td>
                    </tr>

                    @endforeach
                    <tr>
                        <td colspan="5" class="text-right">Total</td>
                        <td>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Cash Paid</td>
                        <td>{{ config('settings.currency_symbol') }} {{ number_format($receivedAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">Balance</td>
                        <td>{{ config('settings.currency_symbol') }} {{ number_format($balance, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection