@extends('layouts.admin')

@section('title', 'Add Cash In/Out')
@section('content-header', 'Add Cash In/Out')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('cash.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- dropdown menue for the orders with remaining amount -->
            <div class="form-group">
                <label for="name">Cash In/Out Name</label>
                <select name="name" class="form-control @error('name') is-invalid @enderror" id="name">
                    <option value="">Select Cash In/Out Name</option>
                    <option value="New Capital">New Capital</option>
                    <option value="Cash Purchases">Cash Purchases</option>
                    <option value="Paid Suppliers">Paid Suppliers</option>
                    <option value="Drawings">Drawings</option>
                    <option value="Other Expenses">Other Expenses</option>
                </select>
                @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                    id="description" placeholder="Description" value="{{ old('description') }}">
                @error('description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="value">Amount</label>
                <input type="number" name="value" class="form-control @error('value') is-invalid @enderror" id="value"
                    placeholder="Value" value="{{ old('value') }}">
                @error('value')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <!-- in/out -->
            <div class="form-group">
                <label for="type">Cash In/Out</label>
                <select name="type" class="form-control @error('type') is-invalid @enderror" id="type">
                    <option value="">Select In/Out</option>
                    <option value="in">In</option>
                    <option value="out">Out</option>
                </select>
                @error('type')
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