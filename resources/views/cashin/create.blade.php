@extends('layouts.admin')

@section('title', 'Add New Cashin')
@section('content-header', 'Add New Cashin')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('cashin.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- dropdown menue for the orders with remaining amount -->
            <div class="form-group">
                <label for="order_id">Order</label>
                <select name="order_id" class="form-control @error('order_id') is-invalid @enderror" id="order_id">
                    <option value="">Select Order</option>
                    @foreach ($orders as $order)
                    <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                        Order ID = {{ $order->id }} - Customer ID{{ $order->customer_id }} - Order Value =
                        {{ number_format($order->price,2) }} -
                        Balance Due = {{ $order->price - $order->amount }}
                    </option>
                    @endforeach
                </select>
                @error('order_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount"
                    placeholder="Amount" value="{{ old('amount') }}">
                @error('amount')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>


            <button class="btn btn-success btn-block btn-lg" type="submit">Submit</button>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(document).ready(function() {
        bsCustomFileInput.init();
    });
</script>
@endsection