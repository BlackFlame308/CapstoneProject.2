@extends('layouts.admin')

@section('page_title', 'Search Results')
@section('page_icon')
    <i class="fas fa-search"></i>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.search.form') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> New Search
        </a>
    </div>
</div>

<div class="alert alert-info">
    <strong>Results for:</strong> "{{ $query }}" 
    ({{ count($households) }} households, {{ count($members) }} members found)
</div>

<!-- Households Results -->
@if(count($households) > 0)
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-header" style="background-color: #f8f9fa; padding: 15px 20px; border-bottom: 2px solid #dee2e6;">
        <h5 style="margin: 0; color: #333; font-weight: 600;">
            <i class="fas fa-home"></i> Households ({{ count($households) }})
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table" style="margin: 0;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 15px;">Code</th>
                    <th style="padding: 15px;">Name</th>
                    <th style="padding: 15px;">Contact</th>
                    <th style="padding: 15px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($households as $household)
                <tr style="border-bottom: 1px solid #f1f1f1;">
                    <td style="padding: 15px; vertical-align: middle;">
                        <code style="background: #f1f1f1; padding: 4px 8px; border-radius: 4px;">{{ $household['code'] }}</code>
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        <strong style="color: #333;">{{ $household['name'] }}</strong>
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        {{ $household['contact'] ?? 'N/A' }}
                    </td>
                    <td style="padding: 15px; vertical-align: middle; text-align: center;">
                        <a href="{{ $household['url'] }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Members Results -->
@if(count($members) > 0)
<div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: none;">
    <div class="card-header" style="background-color: #f8f9fa; padding: 15px 20px; border-bottom: 2px solid #dee2e6;">
        <h5 style="margin: 0; color: #333; font-weight: 600;">
            <i class="fas fa-users"></i> Members ({{ count($members) }})
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table" style="margin: 0;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 15px;">Name</th>
                    <th style="padding: 15px;">Age</th>
                    <th style="padding: 15px;">Household</th>
                    <th style="padding: 15px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                <tr style="border-bottom: 1px solid #f1f1f1;">
                    <td style="padding: 15px; vertical-align: middle;">
                        <strong style="color: #333;">{{ $member['name'] }}</strong>
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        {{ $member['age'] ?? 'N/A' }}
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        <code style="background: #f1f1f1; padding: 4px 8px; border-radius: 4px;">{{ $member['household'] }}</code>
                    </td>
                    <td style="padding: 15px; vertical-align: middle; text-align: center;">
                        <a href="{{ $member['url'] }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- No Results -->
@if(count($households) == 0 && count($members) == 0)
<div style="padding: 60px 20px; text-align: center;">
    <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 20px; display: block;"></i>
    <h5 style="color: #999; margin-bottom: 10px;">No results found</h5>
    <p style="color: #bbb; margin-bottom: 20px;">Try adjusting your search query or filters.</p>
    <a href="{{ route('admin.search.form') }}" class="btn btn-primary">
        <i class="fas fa-search"></i> New Search
    </a>
</div>
@endif

@endsection
