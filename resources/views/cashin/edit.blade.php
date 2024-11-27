@extends('layouts.admin')

@section('title', 'Update Cashin')
@section('content-header', 'Update Cashin')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('cashin.update', $cashin) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="order_id">Order</label>
                <select disabled name="order_id" class="form-control @error('order_id') is-invalid @enderror"
                    id="order_id">
                    <option value="{{ $cashin->order_id }}"
                        {{ old('order_id') == $cashin->order_id ? 'selected' : '' }}>
                        Order ID = {{ $cashin->order_id }}
                    </option>
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
                    placeholder="Amount" value="{{ $cashin->amount }}">
                @error('amount')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>


            <button class="btn btn-success btn-block btn-lg" type="submit">Save Changes</button>
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