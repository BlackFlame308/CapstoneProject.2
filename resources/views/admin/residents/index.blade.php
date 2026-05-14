@extends('layouts.admin')

@section('page_title', 'Residents')
@section('page_icon')
    <i class="fas fa-users"></i>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Residents</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.residents.create', ['household' => 1]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Resident
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Household</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>PWD</th>
                                <th>Senior</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($residents as $resident)
                            <tr>
                                <td>{{ $resident->full_name }}</td>
                                <td>{{ $resident->household->household_name ?? 'N/A' }}</td>
                                <td>{{ $resident->age ?? 'N/A' }}</td>
                                <td>{{ ucfirst($resident->gender) }}</td>
                                <td>
                                    @if($resident->is_pwd)
                                        <span class="badge badge-warning">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($resident->is_senior)
                                        <span class="badge badge-info">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.residents.edit', $resident) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->role?->name === 'head')
                                    <form action="{{ route('admin.residents.destroy', $resident) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No residents found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($residents->hasPages())
                <div class="card-footer">
                    {{ $residents->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
