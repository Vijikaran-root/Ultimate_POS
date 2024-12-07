@extends('layouts.admin')

@section('title', "Report {$month} {$year}")
@section('content-header', "Report {$month} {$year}")


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
            @foreach ($otherExpensesDetails as $expense)
            <tr>
                <td>Operating Expenses</td>
                <td>{{ $expense->description }}</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($expense->value, 2) }}</td>
            </tr>
            @endforeach

            <!-- Net Profit -->
            <tr class="table-success">
                <td>Net Profit</td>
                <td>Gross Profit - Operating Expenses</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($grossProfit - $otherExpenses, 2) }}</td>
            </tr>

        </tbody>
    </table>
    <h1>Cash Statement for {{ $month }} {{ $year }}</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Month</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Month</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- Brought Forward (B/F) -->
            @if($sum>$sum1) <tr>
                <td>{{ $previousMonth }} {{ $previousYear }}</td>
                <td>B/F</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($bf1, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @else
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $previousMonth }} {{ $previousYear }}</td>
                <td>B/F</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($bf1, 2) }}</td>
            </tr>
            @endif

            <!-- Credit and Debit Entries -->
            <tr>
                <td>{{ $month }} {{ $year }}</td>
                <td>Total Sales</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</td>
                <td>{{ $month }} {{ $year }}</td>
                <td>Cash Purchases</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($cashPurchases, 2) }}</td>
            </tr>
            <tr>
                <td>{{ $month }} {{ $year }}</td>
                <td>Additional Capital</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($newCapital, 2) }}</td>
                <td>{{ $month }} {{ $year }}</td>
                <td>Paid Suppliers</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($paidSuppliers, 2) }}</td>
            </tr>
            <tr>
                <td>{{ $month }} {{ $year }}</td>
                <td>Purchase Return</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturn, 2) }}</td>
                <td>{{ $month }} {{ $year }}</td>
                <td>Other Expenses</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($otherExpenses, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ $month }} {{ $year }}</td>
                <td>Drawings</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($drawings, 2) }}</td>
            </tr>

            @if($sum<$sum1) <tr>
                <td>{{ $month }} {{ $year }}</td>
                <td>C/D</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($cd, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
                </tr>
                @else
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $month }} {{ $year }}</td>
                    <td>C/D</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($cd, 2) }}</td>
                </tr>
                @endif

                <!-- Carried Down (C/D) -->
                <tr class="table-primary">
                    <td></td>
                    <td></td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($sum, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($sum, 2) }}</td>
                </tr>

                <!-- Brought Down (B/D) -->
                @if($sum>$sum1) <tr>
                    <td>{{ $nextMonth }} {{ $nextYear }}</td>
                    <td>B/D</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($bd, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @else
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $nextMonth }} {{ $nextYear }}</td>
                    <td>B/D</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($bd, 2) }}</td>

                </tr>
                @endif
        </tbody>
    </table>
    <h1>Daily Turnover for {{ $month }} {{ $year }}</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Days</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dailyTurnover as $day => $price)
            <tr>
                <td>{{ $price->date }}</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($price->total,2) }}</td>
            </tr>
            @endforeach
            <tr class="table-primary">
                <td>Total</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($total,2) }}</td>
            </tr>
        </tbody>
    </table>
    <h1>Daily Profit for {{ $month }} {{ $year }}</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Days</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dailyProfit as $day => $price)
            <tr>
                <td>{{ $price->date }}</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($price->profit,2) }}</td>
            </tr>
            @endforeach
            <tr class="table-primary">
                <td>Total</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($dailyProfitSum,2) }}</td>
            </tr>
        </tbody>
    </table>
    <h1>Top 5 Highest Selling Products</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topProducts as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->total_quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <h1>Top 5 Lowest Selling Products</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bottomProducts as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->total_quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


@endsection