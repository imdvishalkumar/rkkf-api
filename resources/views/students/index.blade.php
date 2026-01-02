@extends('layouts.app')

@section('title', 'RKKF | View Students')

@section('content-header')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Students</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">View Students</li>
                </ol>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Student Details</h3>
                <div class="card-tools">
                    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Student
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="studentsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>GR No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Branch</th>
                            <th>Belt</th>
                            <th>DOJ</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->branch->name ?? 'N/A' }}</td>
                            <td>{{ $student->belt->name ?? 'N/A' }}</td>
                            <td>{{ $student->doj ? $student->doj->format('Y-m-d') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('students.deactivate', $student) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                <form action="{{ route('students.destroy', $student) }}" method="POST" style="display: inline;">
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
        $("#studentsTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });
    });
</script>
@endpush

