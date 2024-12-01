@extends('layouts.admin')

@section('title', 'Report Management')
@section('content-header', 'Report Management')

@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card product-list">
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <!-- Log on to codeastro.com for more projects -->
                    <th>Report Name</th>
                    <th>Month</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($monthlySales as $sale)
                <tr>
                    <td>Monthly Sales Report</td>
                    <td>{{ $sale->month }}</td>
                    <td>
                        <!-- Replace # with the actual route to view/download the report -->
                        <a href="#" class="btn btn-sm btn-primary">View</a>
                        <a href="#" class="btn btn-sm btn-success">Download</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">No data available for monthly sales.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div><!-- Log on to codeastro.com for more projects -->
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
            text: "Do you really want to delete this product?",
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