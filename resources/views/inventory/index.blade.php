@extends('layouts.admin')

@section('title', 'Inventory Management')
@section('content-header', 'Inventory Management')
@section('content-actions')
<a href="{{route('inventory.create')}}" class="btn btn-success"><i class="fas fa-plus"></i> Add New Inventory</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Cost</th>
                    <th>Reorder Level</th>
                    <th>Quantity On Hand</th>
                    <th>Stock Value</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inventory as $invent)
                <tr>

                    <td>{{$invent->id}}</td>
                    <td>{{$invent->product->name}}</td>
                    <td>{{config('settings.currency_symbol')}} {{number_format($invent->cost,2)}}</td>
                    <td>{{number_format($invent->reorder_level,0)}}</td>
                    <td>{{number_format($invent->quantity_on_hand,0)}}</td>
                    <td>{{config('settings.currency_symbol')}}
                        {{number_format($invent->cost * $invent->quantity_on_hand,2)}}
                    </td>
                    <td>{{$invent->created_at}}</td>
                    <td>
                        <a href="{{ route('inventory.edit', $invent) }}" class="btn btn-primary"><i
                                class="fas fa-edit"></i></a>
                        <button class="btn btn-danger btn-delete" data-url="{{route('inventory.destroy', $invent)}}"><i
                                class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $inventory->render() }}
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-delete', function() {
            $this = $(this);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this customer?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post($this.data('url'), {
                        _method: 'DELETE',
                        _token: '{{csrf_token()}}'
                    }, function(res) {
                        $this.closest('tr').fadeOut(500, function() {
                            $(this).remove();
                        })
                    })
                }
            })
        })
    })
</script>
@endsection