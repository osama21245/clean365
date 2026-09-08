@extends('adminmodule::layouts.master')

@section('title', translate('Before After'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Before After') }}</h2>
                    </div>

                    @can('before_after_add')
                        <div class="card mb-30">
                            <div class="card-body p-30">
                                <form action="{{ route('admin.before-after.store') }}"
                                      method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-4">
                                        <div class="col-lg-6">
                                            <label class="fs-14 fw-medium mb-2">{{ translate('Title') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title"
                                                   value="{{ old('title') }}"
                                                   placeholder="{{ translate('e.g. Kitchens') }}" required maxlength="190">
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="fs-14 fw-medium mb-2">{{ translate('Sort Order') }}</label>
                                            <input type="number" class="form-control" name="sort_order"
                                                   value="{{ old('sort_order', 0) }}" min="0" step="1">
                                        </div>
                                        <div class="col-lg-6">
                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                'name'             => 'before_image',
                                                'id'               => 'beforeImage',
                                                'title'            => translate('Before Image'),
                                                'subtitle'         => translate('Upload before image'),
                                                'required'         => true,
                                                'image'            => null,
                                                'ratio'            => 'ratio-1-1',
                                                'instructionRatio' => '1:1'])
                                        </div>
                                        <div class="col-lg-6">
                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                'name'             => 'after_image',
                                                'id'               => 'afterImage',
                                                'title'            => translate('After Image'),
                                                'subtitle'         => translate('Upload after image'),
                                                'required'         => true,
                                                'image'            => null,
                                                'ratio'            => 'ratio-1-1',
                                                'instructionRatio' => '1:1'])
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-4 flex-wrap justify-content-end">
                                                <button type="reset" class="btn btn--secondary">{{ translate('reset') }}</button>
                                                <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'all' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?status=all">{{ translate('all') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'active' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?status=active">{{ translate('active') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'inactive' ? 'active' : '' }}"
                                   href="{{ url()->current() }}?status=inactive">{{ translate('inactive') }}</a>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{ translate('Total') }}:</span>
                            <span class="title-color">{{ $items->total() }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{ url()->current() }}?status={{ $status }}"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{ $search }}" name="search"
                                               placeholder="{{ translate('search_here') }}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{ translate('sl') }}</th>
                                        <th>{{ translate('Title') }}</th>
                                        <th>{{ translate('Before') }}</th>
                                        <th>{{ translate('After') }}</th>
                                        <th>{{ translate('Sort Order') }}</th>
                                        @can('before_after_manage_status')
                                            <th>{{ translate('status') }}</th>
                                        @endcan
                                        @canany(['before_after_delete', 'before_after_update'])
                                            <th>{{ translate('action') }}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($items as $key => $item)
                                        <tr>
                                            <td>{{ $key + $items->firstItem() }}</td>
                                            <td>{{ $item->title }}</td>
                                            <td>
                                                <img src="{{ $item->before_image_full_path }}"
                                                     alt="before" width="56" height="56"
                                                     class="rounded object-fit-cover">
                                            </td>
                                            <td>
                                                <img src="{{ $item->after_image_full_path }}"
                                                     alt="after" width="56" height="56"
                                                     class="rounded object-fit-cover">
                                            </td>
                                            <td>{{ $item->sort_order }}</td>
                                            @can('before_after_manage_status')
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input"
                                                               data-status="{{ $item->id }}"
                                                               type="checkbox" {{ $item->is_active ? 'checked' : '' }}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            @endcan
                                            @canany(['before_after_delete', 'before_after_update'])
                                                <td>
                                                    <div class="table-actions">
                                                        @can('before_after_update')
                                                            <a href="{{ route('admin.before-after.edit', [$item->id]) }}"
                                                               class="action-btn btn--light-primary">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        @endcan
                                                        @can('before_after_delete')
                                                            <button type="button"
                                                                    data-id="{{ $item->id }}"
                                                                    class="action-btn btn--danger delete_section">
                                                                <span class="material-icons">delete</span>
                                                            </button>
                                                            <form action="{{ route('admin.before-after.delete', [$item->id]) }}"
                                                                  method="post" id="delete-{{ $item->id }}"
                                                                  class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">{{ translate('No data found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $items->links() !!}
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
        "use Strict";

        $('.switcher_input').on('click', function () {
            let itemId = $(this).data('status');
            let route = '{{ route('admin.before-after.status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert(route, '{{ translate('want_to_update_status') }}');
        });

        $('.delete_section').on('click', function () {
            let itemId = $(this).data('id');
            form_alert('delete-' + itemId, '{{ translate('want_to_delete_this') }}');
        });
    </script>
@endpush
