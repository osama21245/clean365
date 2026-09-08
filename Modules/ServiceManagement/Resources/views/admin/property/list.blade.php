@extends('adminmodule::layouts.master')

@section('title', translate('properties'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('properties')}}</h2>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('add_new_property')}}</h4>
                            <form action="{{route('admin.property.store')}}" method="post">
                                @csrf
                                <div class="mb-30">
                                    <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('name')}} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}"
                                        placeholder="{{translate('Enter property name')}}">
                                </div>
                                <div class="mb-30">
                                    <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('description')}}</label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="{{translate('Enter property description')}}">{{ old('description') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn--primary w-100">{{translate('Submit')}}</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
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
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('Sl')}}</th>
                                            <th>{{translate('name')}}</th>
                                            <th>{{translate('packages')}}</th>
                                            <th>{{translate('status')}}</th>
                                            <th>{{translate('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($properties as $key => $property)
                                            <tr>
                                                <td>{{$properties->firstItem() + $key}}</td>
                                                <td>
                                                    <div class="fw-medium">{{$property->name}}</div>
                                                    @if($property->description)
                                                        <div class="fs-12 text-muted">
                                                            {{ \Illuminate\Support\Str::limit($property->description, 60) }}</div>
                                                    @endif
                                                </td>
                                                <td>{{$property->packages_count}}</td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            {{$property->is_active ? 'checked' : ''}}
                                                            data-url="{{route('admin.property.status-update', [$property->id])}}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editProperty-{{$property->id}}">
                                                            <span class="material-icons">edit</span>
                                                        </button>
                                                        <button type="button" data-id="property-{{$property->id}}"
                                                            data-message="{{translate('want_to_delete_this_property')}}?"
                                                            class="btn btn-outline-danger btn-sm form-alert">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form action="{{route('admin.property.delete', [$property->id])}}"
                                                            method="post" id="property-{{$property->id}}" class="hidden">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>

                                                    <div class="modal fade" id="editProperty-{{$property->id}}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form
                                                                    action="{{route('admin.property.update', [$property->id])}}"
                                                                    method="post">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">{{translate('edit_property')}}
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="mb-30">
                                                                            <label
                                                                                class="mb-2 lh-1 fs-14 fw-medium">{{translate('name')}}
                                                                                <span class="text-danger">*</span></label>
                                                                            <input type="text" name="name" class="form-control"
                                                                                required value="{{$property->name}}">
                                                                        </div>
                                                                        <div class="mb-30">
                                                                            <label
                                                                                class="mb-2 lh-1 fs-14 fw-medium">{{translate('description')}}</label>
                                                                            <textarea name="description" class="form-control"
                                                                                rows="3">{{$property->description}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn--secondary"
                                                                            data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                                                                        <button type="submit"
                                                                            class="btn btn--primary">{{translate('Update')}}</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    @include('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'])
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {!! $properties->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection