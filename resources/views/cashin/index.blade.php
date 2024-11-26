@extends('layouts.admin')

@section('title', 'Cash In')
@section('content-header', 'Cash In')
@section('content-actions')
<a href="{{route('cashin.create')}}" class="btn btn-success"><i class="fas fa-plus"></i> Add New Cashin</a>
@endsection

@section('content')

<div class="card">
    <!-- Log on to codeastro.com for more projects -->
    <div class="card-body">
        <div class="row">
            <!-- <div class="col-md-3"></div> -->
            <div class="col-md-12">
                <form action="{{route('cashin.index')}}">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="date" name="start_date" class="form-control"
                                value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-5">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Order ID</th>
                    <th>Cash In</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cashin as $cash)
                <tr>
                    <td>{{$cash->id}}</td>
                    <td>{{$cash->customer->name}}</td>
                    <td>{{$cash->order_id}}</td>
                    <td>{{$cash->amount}}</td>
                    <td>{{$cash->created_at}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection