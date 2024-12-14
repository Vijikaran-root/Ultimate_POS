@extends('layouts.admin')

@section('title', "Annual Report {$year}")
@section('content-header', "Annual Report {$year}")


@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="container">
    <h1>Ratios</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Current Ratio</td>
                <td>Current Assets / Current Liability</td>
                <td>{{ number_format($totalAssets/$totalLiabilities,2) }} : 1
                </td>
            </tr>
            <tr>
                <td>Quick Ratio</td>
                <td>Current Assets - (Closing Stock) / Current Liability</td>
                <td>{{ number_format($cashinhand/$totalLiabilities,2) }} : 1
                </td>
            </tr>
            <tr>
                <td>Gross Profit Margin</td>
                <td>(Gross Profit / Revenue) x 100</td>
                <td>{{ number_format($grossProfit/$total*100,2) }}%
                </td>
            </tr>
            <tr>
                <td>Net Profit Margin</td>
                <td>(Net Profit / Revenue) x 100</td>
                <td>{{ number_format($netprofit/$total*100,2) }}%
                </td>
            </tr>
            <tr>
                <td>Return On Assets (ROA)</td>
                <td>(Net Profit / Total Assets) x 100</td>
                <td>{{ number_format($netprofit/$totalAssets*100,2) }}%
                </td>
            </tr>
            <tr>
                <td>Return On Equity (ROE)</td>
                <td>(Net Profit / Capital) x 100</td>
                <td>{{ number_format($netprofit/$capital*100, 2) }}%
                </td>
            </tr>
            <tr>
                <td>Invenntory Turnover Ratio</td>
                <td>Cost Of Goods Sold / Average Inventory</td>
                <td>{{ number_format($cogs/($inventoryValue-$cogs/$cogs), 2) }}%
                </td>
            </tr>
            <tr>
                <td>Invenntory Turnover Period</td>
                <td>Average Inventory / Cost Of Goods Sold</td>
                <td>{{ number_format(($inventoryValue-$cogs/$cogs)/$cogs, 2) }} Days
                </td>
            </tr>
            <tr>
                <td>Debt Ratio</td>
                <td>Total Liabilities / Total Assets</td>
                <td>{{ number_format($totalLiabilities/$totalAssets, 2) }}%
                </td>
            </tr>
            <tr>
                <td>Debt Equity Ratio</td>
                <td>Total Liabilities / Capital</td>
                <td>{{ number_format($totalLiabilities/$capital, 2) }}%
                </td>
            </tr>
            </tr>
        </tbody>
    </table>
    <h1>Statement of Financial Position</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-lg text-bold">
                <td colspan="3">Assets</td>
            </tr>
            <tr>
                <td>Current Assets</td>
                <td>Cash in Hand</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($cashinhand, 2) }}</td>
            </tr>
            <tr>
                <td>Current Assets</td>
                <td>Closing Stock</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($inventoryValue, 2) }}</td>
            </tr>
            <tr>
                <td>Total Assets</td>
                <td></td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($totalAssets, 2) }}</td>
            </tr>
            <tr class="text-lg text-bold">
                <td colspan="3">Equity</td>
            </tr>
            <tr>
                <td>Capital</td>
                <td>Capital</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($capital, 2) }}</td>
            </tr>
            <tr>
                <td>Retained Earnings</td>
                <td>Net Profit</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($netprofit,2)}}</td>
            </tr>
            <tr>
                <td>Drawings</td>
                <td></td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($drawings, 2) }}</td>
            </tr>
            <tr>
                <td>Total Equity</td>
                <td></td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($totalEquity, 2) }}</td>
            </tr>
            <tr class="text-lg text-bold">
                <td colspan="3">Liabilities</td>
            </tr>
            <tr>
                <td>Current Liabilities</td>
                <td>Suppliers Balance</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($supplierBalance, 2) }}</td>
            </tr>
            <tr>
                <td>Total Liabilities</td>
                <td></td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($totalLiabilities, 2) }}</td>
            </tr>
            <tr>
                <td>Total Equity & Liabilities</td>
                <td>Total Equity + Total Liabilities</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($totalEquity + $totalLiabilities, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
    <h1>P/L Statement</h1>
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
    <h1>Cash Statement</h1>
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
                <td> {{ $previousYear }}</td>
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
                <td> {{ $previousYear }}</td>
                <td>B/F</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($bf1, 2) }}</td>
            </tr>
            @endif

            <!-- Credit and Debit Entries -->
            <tr>
                <td> {{ $year }}</td>
                <td>Total Sales</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</td>
                <td> {{ $year }}</td>
                <td>Cash Purchases</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($cashPurchases, 2) }}</td>
            </tr>
            <tr>
                <td> {{ $year }}</td>
                <td>Additional Capital</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($newCapital, 2) }}</td>
                <td> {{ $year }}</td>
                <td>Paid Suppliers</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($paidSuppliers, 2) }}</td>
            </tr>
            <tr>
                <td> {{ $year }}</td>
                <td>Purchase Return</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturn, 2) }}</td>
                <td> {{ $year }}</td>
                <td>Other Expenses</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($otherExpenses, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td> {{ $year }}</td>
                <td>Drawings</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($drawings, 2) }}</td>
            </tr>

            @if($sum<$sum1) <tr>
                <td> {{ $year }}</td>
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
                    <td> {{ $year }}</td>
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
                    <td>{{ $nextYear }}</td>
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
                    <td>{{ $nextYear }}</td>
                    <td>B/D</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($bd, 2) }}</td>

                </tr>
                @endif
        </tbody>
    </table>
    <h1>Monthly Turnover</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Months</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthlyTurnover as $months => $month)
            <tr>
                <td>{{ $month->month }}</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($month->total,2) }}</td>
            </tr>
            @endforeach
            <tr class="table-primary">
                <td>Total</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($total,2) }}</td>
            </tr>
        </tbody>
    </table>
    <h1>Monthly Profit</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Months</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthlyProfit as $months => $month)
            <tr>
                <td>{{ $month->month }}</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($month->profit,2) }}</td>
            </tr>
            @endforeach
            <tr class="table-primary">
                <td>Total</td>
                <td>{{ config('settings.currency_symbol') }} {{ number_format($monthlyProfitSum,2) }}</td>
            </tr>
        </tbody>
    </table>
    <h1>Daily Turnover</h1>
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
    <h1>Daily Profit</h1>
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
    <h1>Top 5 Highest Monthly Turnover</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Month</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($top5MonthlyTurnover as $top)
            <tr>
                <td>{{ $top->month }}</td>
                <td>{{config('settings.currency_symbol')}}{{ number_format($top->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <h1>Top 5 Lowest Monthly Turnover</h1>
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Month</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lowest5MonthlyTurnover as $low)
            <tr>
                <td>{{ $low->month }}</td>
                <td>{{config('settings.currency_symbol')}}{{ number_format($low->total, 2) }}</td>
            </tr>
            @endforeach
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