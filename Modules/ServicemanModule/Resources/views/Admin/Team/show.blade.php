@extends('adminmodule::layouts.master')

@section('title', translate('Team_Details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="page-title mb-1">{{ $team->name }}</h2>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge {{ $team->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $team->is_active ? translate('Active') : translate('Inactive') }}
                        </span>
                        <span class="text-muted">{{ translate('Supervisor') }}: {{ $team->provider?->company_name ?? '-' }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @can('team_update')
                        <a href="{{ route('admin.team.edit', $team->id) }}" class="btn btn--primary">
                            <span class="material-icons">edit</span> {{ translate('Edit') }}
                        </a>
                    @endcan
                    <a href="{{ route('admin.team.list') }}" class="btn btn--secondary">{{ translate('Back') }}</a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">{{ translate('Team_Members') }} ({{ $team->servicemen->count() }})</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('Name') }}</th>
                                        <th>{{ translate('Phone') }}</th>
                                        <th>{{ translate('Email') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($team->servicemen as $key => $serviceman)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $serviceman->user?->first_name }} {{ $serviceman->user?->last_name }}</td>
                                            <td>{{ $serviceman->user?->phone }}</td>
                                            <td>{{ $serviceman->user?->email }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">{{ translate('No serviceman found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">{{ translate('Assigned_Bookings') }}</h4>
                        </div>
                        <div class="card-body">
                            @forelse($team->bookings->take(10) as $booking)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <span>#{{ $booking->readable_id }}</span>
                                    <a href="{{ route('admin.booking.details', ['id' => $booking->id]) }}"
                                       class="btn btn-sm btn--light-primary">{{ translate('View') }}</a>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ translate('No bookings assigned yet') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
