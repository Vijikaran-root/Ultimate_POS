@extends('layouts.admin')

@section('content-header', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Log on to codeastro.com for more projects -->


        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($monthly_sales, 2)}}</h3>
                    <p>Monthly Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{route('orders.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($monthly_profit, 2)}}</h3>
                    <p>Monthly Profit</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dolly-flatbed"></i>
                </div>
                <a href="{{route('orders.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{$monthly_orders}}</h3>
                    <p>Monthly Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{route('orders.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($total_pending_due,2)}}</h3>
                    <p>Total Pending Due</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dolly-flatbed"></i>
                </div>
                <a href="{{route('due.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

    </div>

    <div class="row">
        <!-- Log on to codeastro.com for more projects -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($daily_sales, 2)}}</h3>

                    <p>Daily Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <a href="{{route('orders.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($daily_profit, 2)}}</h3>
                    <p>Daily Profit</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{route('orders.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$daily_orders}}</h3>

                    <p>Daily Orders</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('orders.index') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{$supplier_count}}</h3>
                    <p>Total Suppliers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dolly-flatbed"></i>
                </div>
                <a href="{{route('suppliers.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <div class="row">
        <div class="col-lg-12 col-6">
            <!-- small box -->
            <div class="small-box bg-teal">
                <div class="inner text-center">
                    <h2>Other Details</h2>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <!-- Log on to codeastro.com for more projects -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{$inventory_balance}}</h3>

                    <p>Inventory Balance</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <a href="{{route('inventory.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{config('settings.currency_symbol')}} {{number_format($inventory_value, 2)}}</h3>
                    <p>Inventory Value</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{route('inventory.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-blue">
                <div class="inner">
                    <h3>{{$customers_count}}</h3>

                    <p>Total Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('customers.index') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{$products_count}}</h3>
                    <p>Total Products</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dolly-flatbed"></i>
                </div>
                <a href="{{route('products.index')}}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
</div><!-- Log on to codeastro.com for more projects -->
@endsection