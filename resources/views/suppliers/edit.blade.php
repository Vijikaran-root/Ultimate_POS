@extends('layouts.admin')

@section('title', 'Update Supplier')
@section('content-header', 'Update Supplier')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('suppliers.update', $supplier) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                    id="first_name" placeholder="First Name" value="{{ old('first_name', $supplier->first_name) }}">
                @error('first_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                    id="last_name" placeholder="Last Name" value="{{ old('last_name', $supplier->last_name) }}">
                @error('last_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" name="last_name" class="form-control @error('company_name') is-invalid @enderror"
                    id="company_name" placeholder="Company Name"
                    value="{{ old('company_name', $supplier->company_name) }}">
                @error('company_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    placeholder="Email" value="{{ old('email', $supplier->email) }}">
                @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone"
                    placeholder="Phone" value="{{ old('phone', $supplier->phone) }}">
                @error('phone')
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