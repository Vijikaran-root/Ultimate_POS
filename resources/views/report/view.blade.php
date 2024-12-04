@extends('layouts.admin')

@section('title', 'Report Management')
@section('content-header', 'Report Management')

@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="container">
    <h1>Profit and Loss Statement for {{ $month }} {{ $year }}</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- Revenue Section -->
            <tr>
                <td>Revenue</td>
                <td>Total Sales</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</td>
            </tr>

            <!-- Cost of Goods Sold Section -->
            <tr>
                <td>Cost of Goods Sold (COGS)</td>
                <td>Total Cost of Goods Sold</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($cogs, 2) }}</td>
            </tr>

            <!-- Gross Profit -->
            <tr class="table-primary">
                <td>Gross Profit</td>
                <td>Revenue - COGS</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($grossProfit, 2) }}</td>
            </tr>

            <!-- Operating Expenses -->

        </tbody>
    </table>
</div>


@endsection