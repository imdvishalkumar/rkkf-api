@extends('layouts.app')

@section('title', 'RKKF | Belts')

@section('content-header')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Belts</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Belts</li>
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
                <h3 class="card-title">Belt Details</h3>
            </div>
            <form action="{{ route('belts.update-exam-fees') }}" method="POST">
                @csrf
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Exam Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($belts as $belt)
                            <tr>
                                <td>{{ $belt->belt_id }}</td>
                                <td>{{ $belt->name }}</td>
                                <td>{{ $belt->code ?? 'N/A' }}</td>
                                <td>
                                    <input type="hidden" name="belt_id[]" value="{{ $belt->belt_id }}">
                                    <input type="number" class="form-control" name="exam_fees[]" value="{{ $belt->exam_fees }}" step="0.01" min="0" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Update Exam Fees</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

