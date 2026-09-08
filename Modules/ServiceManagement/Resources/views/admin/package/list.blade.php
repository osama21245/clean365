@extends('adminmodule::layouts.master')

@section('title', translate('package_list'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h2 class="page-title">{{translate('package_list')}}</h2>
                        <a href="{{route('admin.package.create')}}" class="btn btn--primary">
                            <span class="material-icons">add</span>
                            {{translate('add_new_package')}}
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="{{$search}}" name="search" placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>

                                <div class="d-flex gap-2 fw-medium align-items-center">
                                    <span class="opacity-75">{{translate('Total_Packages')}}:</span>
                                    <span class="title-color">{{$packages->total()}}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('Sl')}}</th>
                                            <th>{{translate('image')}}</th>
                                            <th>{{translate('name')}}</th>
                                            <th>{{translate('property')}}</th>
                                            <th>{{translate('package_price')}}</th>
                                            <th>{{translate('billing_period')}}</th>
                                            <th>{{translate('status')}}</th>
                                            <th>{{translate('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($packages as $key => $package)
                                            <tr>
                                                <td>{{$packages->firstItem() + $key}}</td>
                                                <td>
                                                    <img width="50" class="rounded" src="{{$package->thumbnail_full_path}}"
                                                        alt="{{$package->name}}">
                                                </td>
                                                <td>{{$package->name}}</td>
                                                <td>{{$package->property?->name ?? translate('N/A')}}</td>
                                                <td>{{with_currency_symbol($package->price)}}</td>
                                                <td>{{translate($package->billing_period)}}</td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            {{$package->is_active ? 'checked' : ''}}
                                                            data-url="{{route('admin.package.status-update', [$package->id])}}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-danger btn-sm form-alert"
                                                            data-id="package-{{$package->id}}"
                                                            data-message="{{translate('want_to_delete_this_package')}}?">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form action="{{route('admin.package.delete', [$package->id])}}"
                                                            method="post" id="package-{{$package->id}}" class="hidden">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    @include('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {!! $packages->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection