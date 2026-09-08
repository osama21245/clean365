@extends('adminmodule::layouts.master')

@section('title', translate('Serviceman_Details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="page-title mb-1">
                        {{ $serviceman->user?->first_name }} {{ $serviceman->user?->last_name }}
                    </h2>
                    <span class="badge {{ $serviceman->user?->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $serviceman->user?->is_active ? translate('Active') : translate('Inactive') }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @can('serviceman_update')
                        <a href="{{ route('admin.serviceman.edit', $serviceman->id) }}" class="btn btn--primary">
                            <span class="material-icons">edit</span> {{ translate('Edit') }}
                        </a>
                    @endcan
                    <a href="{{ route('admin.serviceman.list', ['status' => 'all']) }}" class="btn btn--secondary">{{ translate('Back') }}</a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="{{ $serviceman->user?->profile_image_full_path }}"
                                 alt="" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover">
                            <h4>{{ $serviceman->user?->first_name }} {{ $serviceman->user?->last_name }}</h4>
                            <p class="mb-1">{{ $serviceman->user?->email }}</p>
                            <p class="mb-0">{{ $serviceman->user?->phone }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">{{ translate('General Information') }}</h4></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>{{ translate('Supervisor') }}:</strong>
                                    <p>{{ $serviceman->provider?->company_name ?? translate('Unassigned') }}</p>
                                </div>
                                @if($serviceman->teams->isNotEmpty())
                                    <div class="col-md-6">
                                        <strong>{{ translate('Teams') }}:</strong>
                                        <p>{{ $serviceman->teams->pluck('name')->join(', ') }}</p>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <strong>{{ translate('Identity Type') }}:</strong>
                                    <p>{{ translate(ucfirst(str_replace('_', ' ', $serviceman->user?->identification_type ?? '-'))) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ translate('Identity Number') }}:</strong>
                                    <p>{{ $serviceman->user?->identification_number ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
