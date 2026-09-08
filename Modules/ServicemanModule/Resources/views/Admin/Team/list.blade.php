@extends('adminmodule::layouts.master')

@section('title', translate('Team_List'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <h2 class="page-title mb-0">{{ translate('Team_List') }}</h2>
                        @can('team_add')
                            <a href="{{ route('admin.team.create') }}" class="btn btn--primary">
                                <span class="material-icons">add</span>
                                {{ translate('Add_New_Team') }}
                            </a>
                        @endcan
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'all' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">{{ translate('All') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'active' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">{{ translate('Active') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'inactive' ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">{{ translate('Inactive') }}</a>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{ translate('Total_Teams') }}:</span>
                            <span class="title-color">{{ $teams->total() }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="GET">
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <select name="provider_id" class="theme-input-style js-select" style="min-width: 200px">
                                            <option value="">{{ translate('All_Supervisors') }}</option>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" {{ ($providerId ?? '') == $provider->id ? 'selected' : '' }}>
                                                    {{ $provider->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group search-form__input_group">
                                            <span class="search-form__icon"><span class="material-icons">search</span></span>
                                            <input type="search" class="theme-input-style search-form__input"
                                                   value="{{ $search }}" name="search"
                                                   placeholder="{{ translate('Search Here') }}">
                                        </div>
                                        <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('Team_Name') }}</th>
                                        <th>{{ translate('Supervisor') }}</th>
                                        <th>{{ translate('Members') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($teams as $key => $team)
                                        <tr>
                                            <td>{{ $teams->firstItem() + $key }}</td>
                                            <td>
                                                <a href="{{ route('admin.team.show', $team->id) }}">{{ $team->name }}</a>
                                            </td>
                                            <td>{{ $team->provider?->company_name ?? '-' }}</td>
                                            <td>{{ $team->servicemen_count }}</td>
                                            <td>
                                                @can('team_manage_status')
                                                    <label class="switcher">
                                                        <input class="switcher_input route-alert"
                                                               data-route="{{ route('admin.team.status-update', $team->id) }}"
                                                               data-message="{{ translate('want_to_update_status') }}"
                                                               type="checkbox" {{ $team->is_active ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                @else
                                                    <span class="badge {{ $team->is_active ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $team->is_active ? translate('Active') : translate('Inactive') }}
                                                    </span>
                                                @endcan
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="{{ route('admin.team.show', $team->id) }}"
                                                       class="action-btn btn--light-primary" style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
                                                    @can('team_update')
                                                        <a href="{{ route('admin.team.edit', $team->id) }}"
                                                           class="action-btn btn--light-primary" style="--size: 30px">
                                                            <span class="material-icons">edit</span>
                                                        </a>
                                                    @endcan
                                                    @can('team_delete')
                                                        <button type="button"
                                                                data-id="delete-team-{{ $team->id }}"
                                                                data-title="{{ translate('want_to_delete_this_team') }}?"
                                                                class="action-btn btn--danger form-alert" style="--size: 30px">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </button>
                                                        <form action="{{ route('admin.team.delete', $team->id) }}"
                                                              method="post" id="delete-team-{{ $team->id }}" class="hidden">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all') || filled($providerId)) ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $teams->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            $('.js-select').select2({ width: '200px' });
        });
    </script>
@endpush
