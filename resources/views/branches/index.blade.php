@extends('layouts.app')

@section('title', 'RKKF | Branch')

@section('content-header')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Branch</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Branch</li>
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
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Branch Details</h3>
                <div class="card-tools">
                    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Branch
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="branchesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Days</th>
                            <th>Fees</th>
                            <th>Late Fee</th>
                            <th>Discount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->days ?? 'N/A' }}</td>
                            <td>₹{{ number_format($branch->fees, 2) }}</td>
                            <td>₹{{ number_format($branch->late, 2) }}</td>
                            <td>₹{{ number_format($branch->discount, 2) }}</td>
                            <td>
                                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('branches.destroy', $branch) }}" method="POST" style="display: inline;">
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
    
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Branch</h3>
            </div>
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" value="{{ old('branch_name') }}" required>
                        @error('branch_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Fees</label>
                        <input type="number" class="form-control" name="branch_fees" value="{{ old('branch_fees') }}" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Discount</label>
                        <input type="number" class="form-control" name="discount" value="{{ old('discount', 0) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Late Fee</label>
                        <input type="number" class="form-control" name="late" value="{{ old('late', 0) }}" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Days</label>
                        <select class="form-control select2" name="days[]" multiple required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        
        <!-- Transfer Branch -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Transfer Branch</h3>
            </div>
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <input type="hidden" name="transfer" value="1">
                <div class="card-body">
                    <div class="form-group">
                        <label>From Branch</label>
                        <select class="form-control" name="from_branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To Branch</label>
                        <select class="form-control" name="to_branch_id" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $("#branchesTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@endpush

