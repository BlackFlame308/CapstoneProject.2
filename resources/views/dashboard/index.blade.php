@extends('layouts.app')

@section('title', 'Dashboard - SafeTrack')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">SafeTrack Dashboard</h1>

    <!-- Key Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalHouseholds }}</div>
                <div class="stat-label">Total Households</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalMembers }}</div>
                <div class="stat-label">Total Population</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalPWD }}</div>
                <div class="stat-label">PWD Count</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalSeniors }}</div>
                <div class="stat-label">Senior Citizens</div>
            </div>
        </div>
    </div>

    @can('manage_accounts')
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="stat-card" style="background: #4a5568;">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: #2d3748;">
                <div class="stat-value">{{ $totalCaptains }}</div>
                <div class="stat-label">Captain Accounts</div>
            </div>
        </div>
    </div>
    @endcan

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Population Distribution by Age</h5>
                </div>
                <div class="card-body">
                    <canvas id="ageChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Population by Barangay</h5>
                </div>
                <div class="card-body">
                    <canvas id="barangayChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        @can('view_reports')
        <div class="col-md-12 mb-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Subsystem Reports</h5>
                    <p class="card-text">View household, population, PWD, and senior citizen analytics from the dashboard.</p>
                    <p class="card-text"><small class="text-muted">You can refresh analytics to ensure the report data is up to date.</small></p>
                </div>
            </div>
        </div>
        @endcan

        <!-- Analytics Update -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Update Analytics</h5>
                    <p class="card-text">Refresh analytics data for all barangays based on current household and member data.</p>
                    <form action="{{ route('analytics.update') }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-primary" type="submit">Refresh Analytics</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Barangay Statistics -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistics by Barangay</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Barangay</th>
                                    <th>Households</th>
                                    <th>Population</th>
                                    <th>PWD</th>
                                    <th>Seniors</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($barangayStats as $stat)
                                <tr>
                                    <td>{{ $stat->name }}</td>
                                    <td>{{ $stat->addresses_count }}</td>
                                    <td>
                                        @php
                                        $analytics = $stat->analytics->first();
                                        @endphp
                                        {{ $analytics->total_population ?? 0 }}
                                    </td>
                                    <td>{{ $analytics->total_pwd ?? 0 }}</td>
                                    <td>{{ $analytics->total_seniors ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No barangays available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Households -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recently Added Households</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Household Code</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>Members</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentHouseholds as $household)
                                <tr>
                                    <td>{{ $household->household_code }}</td>
                                    <td>
                                        {{ $household->address->street ?? '' }} {{ $household->address->purok ?? '' }},
                                        {{ $household->address->barangay->name }}
                                    </td>
                                    <td>{{ $household->contact_number }}</td>
                                    <td>{{ $household->members->count() }}</td>
                                    <td>{{ $household->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No households yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Age Distribution Pie Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: ['Children (0-17)', 'Adults (18-59)', 'Seniors (60+)'],
            datasets: [{
                data: [{{ $childrenCount }}, {{ $adultsCount }}, {{ $seniorsCount }}],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56'
                ],
                hoverBackgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Population Age Distribution'
                }
            }
        }
    });

    // Barangay Population Bar Chart
    const barangayCtx = document.getElementById('barangayChart').getContext('2d');
    const barangayLabels = [];
    const barangayData = [];

    @foreach($membersByBarangay as $data)
        barangayLabels.push('{{ $data->name }}');
        barangayData.push({{ $data->count }});
    @endforeach

    const barangayChart = new Chart(barangayCtx, {
        type: 'bar',
        data: {
            labels: barangayLabels,
            datasets: [{
                label: 'Population',
                data: barangayData,
                backgroundColor: '#667eea',
                borderColor: '#764ba2',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Population by Barangay'
                }
            }
        }
    });
});
</script>
@endsection
