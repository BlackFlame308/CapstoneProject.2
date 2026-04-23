@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Households</h1>
    <a href="{{ route('households.create') }}" class="btn btn-primary">Create Household</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Code</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($households as $household)
            <tr>
                <td>{{ $household->household_code }}</td>
                <td>
                    {{ $household->address->street ?? '' }} {{ $household->address->purok ?? '' }},
                    {{ $household->address->barangay->name }},
                    {{ $household->address->barangay->city->name }},
                    {{ $household->address->barangay->city->province->name }}
                </td>
                <td>{{ $household->contact_number }}</td>
                <td>{{ $household->members->count() }}</td>
                <td>
                    <a href="{{ route('households.show', $household) }}" class="btn btn-sm btn-info">View</a>
                    @if($household->user && $household->user->must_change_password)
                        <span class="badge bg-warning">Password Change Required</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $households->links() }}
</div>
@endsection