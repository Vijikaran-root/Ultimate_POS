@extends('layouts.admin')

@section('title', 'Cash In/Out')
@section('content-header', 'Cash In/Out')
@section('content-actions')
<a href="{{route('cash.create')}}" class="btn btn-success"><i class="fas fa-plus"></i> Cash In/Out</a>
@endsection

@section('content')

<div class="card">
    <!-- Log on to codeastro.com for more projects -->
    <div class="card-body">
        <div class="row">
            <!-- <div class="col-md-3"></div> -->
            <div class="col-md-12">
                <form action="{{route('cash.index')}}">
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
                    <th>Name</th>
                    <th>Description</th>
                    <th>Value</th>
                    <th>Cash In/Out</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cash as $cash)
                <tr>
                    <td>{{$cash->id}}</td>
                    <td>{{$cash->name}}</td>
                    <td>{{$cash->description}}</td>
                    <td>{{$cash->value}}</td>
                    <td>
                        @if ($cash->type == 'in')
                        <span class="badge badge-success">Cash In</span>
                        @else
                        <span class="badge badge-danger">Cash Out</span>
                        @endif
                    </td>
                    <td>{{$cash->created_at}}</td>
                    <td>
                        <a href="{{ route('due.edit', $cash->id) }}" class="btn btn-primary"><i
                                class="fas fa-edit"></i></a>
                        <button class="btn btn-danger btn-delete" data-url="{{route('due.destroy', $cash)}}"><i
                                class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection