@extends('layouts.admin')

@section('title', 'Update Inventory')
@section('content-header', 'Update Inventory')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('inventory.update', $inventory) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="product_id">Product</label>
                <select disabled name="product_id" class="form-control @error('product_id') is-invalid @enderror"
                    id="product_id">
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                    <option value="{{ 'product_id', $inventory->product_id }}"
                        {{ old('product_id', $inventory->product_id) == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} - {{ $product->quantity }} units
                    </option>
                    @endforeach
                </select>
                @error('product_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <!-- reorder_level -->
            <div class="form-group">
                <label for="reorder_level">Reorder Level</label>
                <input type="number" name="reorder_level"
                    class="form-control @error('reorder_level') is-invalid @enderror" id="reorder_level"
                    placeholder="Reorder Level" value="{{ old('reorder_level', $inventory->reorder_level) }}">
                @error('reorder_level')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <!-- cost -->
            <div class="form-group">
                <label for="cost">Cost</label>
                <input type="number" name="cost" class="form-control @error('cost') is-invalid @enderror" id="cost"
                    placeholder="Cost" value="{{ old('cost', $inventory->cost) }}">
                @error('cost')
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