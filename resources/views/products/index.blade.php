@extends('layouts.app')

@section('title', 'RKKF | View Products')

@section('content-header')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Products</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">View Products</li>
                </ol>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-check"></i> Success!</h5>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-ban"></i> Error!</h5>
    {{ session('error') }}
</div>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Products Details</h3>
                <div class="card-tools">
                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="productsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Variations</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Images</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>
                                @if($product->variations)
                                    @foreach($product->variations as $variation)
                                        {{ $variation->variation }}<br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($product->variations)
                                    @foreach($product->variations as $variation)
                                        ₹{{ number_format($variation->price, 2) }}<br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($product->variations)
                                    @foreach($product->variations as $variation)
                                        {{ $variation->qty }}<br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($product->image1)
                                    <img src="{{ url('/') }}/images/products/{{ $product->image1 }}" alt="Image 1" style="max-width: 50px; max-height: 50px;">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $("#productsTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@endpush

