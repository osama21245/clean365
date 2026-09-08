@extends('adminmodule::layouts.new-master')

@section('title',translate('Send Notification'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/dataTables/select.dataTables.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Send Notification')}}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 fs-12 p-12 text-dark rounded mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_9562_195)">
                                <path d="M7 14C8.38447 14 9.73785 13.5895 10.889 12.8203C12.0401 12.0511 12.9373 10.9579 13.4672 9.67879C13.997 8.3997 14.1356 6.99224 13.8655 5.63437C13.5954 4.2765 12.9287 3.02922 11.9497 2.05026C10.9708 1.07129 9.7235 0.404603 8.36563 0.134506C7.00777 -0.13559 5.6003 0.003033 4.32122 0.532846C3.04213 1.06266 1.94888 1.95987 1.17971 3.11101C0.410543 4.26216 0 5.61553 0 7C0.0020073 8.8559 0.74015 10.6352 2.05247 11.9475C3.36479 13.2599 5.1441 13.998 7 14ZM7 2.91667C7.17306 2.91667 7.34223 2.96799 7.48612 3.06413C7.63002 3.16028 7.74217 3.29694 7.8084 3.45682C7.87462 3.61671 7.89195 3.79264 7.85819 3.96237C7.82443 4.13211 7.74109 4.28802 7.61872 4.41039C7.49635 4.53276 7.34044 4.6161 7.1707 4.64986C7.00097 4.68362 6.82504 4.66629 6.66515 4.60006C6.50527 4.53384 6.36861 4.42169 6.27246 4.27779C6.17632 4.1339 6.125 3.96473 6.125 3.79167C6.125 3.55961 6.21719 3.33705 6.38128 3.17295C6.54538 3.00886 6.76794 2.91667 7 2.91667ZM6.41667 5.83334H7C7.30942 5.83334 7.60617 5.95625 7.82496 6.17505C8.04375 6.39384 8.16667 6.69058 8.16667 7V10.5C8.16667 10.6547 8.10521 10.8031 7.99581 10.9125C7.88642 11.0219 7.73804 11.0833 7.58333 11.0833C7.42862 11.0833 7.28025 11.0219 7.17086 10.9125C7.06146 10.8031 7 10.6547 7 10.5V7H6.41667C6.26196 7 6.11358 6.93855 6.00419 6.82915C5.89479 6.71975 5.83333 6.57138 5.83333 6.41667C5.83333 6.26196 5.89479 6.11359 6.00419 6.00419C6.11358 5.8948 6.26196 5.83334 6.41667 5.83334Z" fill="#FFBB38"></path>
                                </g>
                                <defs>
                                <clipPath id="clip0_9562_195">
                                <rect width="14" height="14" fill="white"></rect>
                                </clipPath>
                                </defs>
                            </svg>
                            @php($firebaseConfigurationLink = auth()->user()->can('firebase_view')
                                ? '<a href="' . route('admin.configuration.third-party', 'firebase-configuration') . '" target="_blank" class="text-primary text-decoration-underline fw-medium">' . translate('Firebase Configuration') . '</a>'
                                : translate('Firebase Configuration'))
                            <p class="fz-12 mb-20">{{ translate('Set up push notification messages for customers.') }} {!! translate('The :firebaseConfigurationLink page must be configured for notifications to work.', ['firebaseConfigurationLink' => $firebaseConfigurationLink]) !!}</p>
                        </div>
                    </div>

                    @can('push_notification_add')
                        <div class="card mb-30">
                            <div class="card-body p-20">
                                <div class="mb-20">
                                    <p class="fz-12 mb-20">{{ translate('From here admin can send notification to the users') }}</p>
                                </div>
                                <form action="{{route('admin.push-notification.store')}}" method="POST" enctype="multipart/form-data" id="send-notification-form" data-ff-validate novalidate>
                                    @csrf
                                    <div class="row g-sm-4 g-3">
                                        <div class="col-lg-8 order-lg-1">
                                            <div class="bg-light rounded p-20 h-100 d-flex flex-column gap-3">
                                                @include('partials._form-field', [
                                                    'type'        => 'textarea',
                                                    'name'        => 'title',
                                                    'label'       => translate('Title'),
                                                    'placeholder' => translate('Type title'),
                                                    'rows'        => 1,
                                                    'required'    => true,
                                                    'maxlength'   => 100,
                                                    'charCount'   => true,
                                                    'extraAttrs'  => 'data-maxlength="100"',
                                                    'wrapClass'   => 'mb-0'])
                                                @include('partials._form-field', [
                                                    'type'        => 'textarea',
                                                    'name'        => 'description',
                                                    'label'       => translate('Description'),
                                                    'placeholder' => translate('Type about the description'),
                                                    'rows'        => 2,
                                                    'required'    => true,
                                                    'maxlength'   => 200,
                                                    'charCount'   => true,
                                                    'extraAttrs'  => 'data-maxlength="200"',
                                                    'wrapClass'   => 'mb-0'])
                                                <div class="row g-sm-4 g-3">
                                                    <div class="col-lg-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'zone_ids[]',
                                                            'id'          => 'zone_selector__select4',
                                                            'label'       => translate('Zones'),
                                                            'multiple'    => true,
                                                            'required'    => true,
                                                            'selectClass' => 'select-zone theme-input-style w-100',
                                                            'prependOptions' => [
                                                                ['value' => 'all', 'label' => translate('Select All')]],
                                                            'options'     => $zones->pluck('name', 'id')->toArray(),
                                                            'value'       => old('zone_ids', []),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                    <div class="col-lg-6">
                                                        @include('partials._form-field', [
                                                            'type'        => 'select',
                                                            'name'        => 'to_users[]',
                                                            'id'          => 'user_selector__select',
                                                            'label'       => translate('Targeted User'),
                                                            'multiple'    => true,
                                                            'required'    => true,
                                                            'selectClass' => 'select-user theme-input-style w-100',
                                                            'options'     => [
                                                                'all'                 => translate('all'),
                                                                'customer'            => translate('customer'),
                                                                'provider-admin'      => translate('provider'),
                                                                'provider-serviceman' => translate('serviceman')],
                                                            'value'       => old('to_users', []),
                                                            'wrapClass'   => 'mb-0'])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 order-lg-2">
                                            <div class="bg-light rounded p-20 h-100 d-flex align-items-center justify-content-center">
                                                <div class="w-100" style="max-width: 260px;">
                                                    @include('adminmodule::admin.partials._single-image-upload', [
                                                        'name'             => 'cover_image',
                                                        'id'               => 'imageUpload',
                                                        'title'            => translate('Cover Image'),
                                                        'required'         => true,
                                                        'image'            => null,
                                                        'ratio'            => 'ratio-2-1',
                                                        'instructionRatio' => '2:1'])
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 order-last">
                                            <div class="d-flex justify-content-end gap-20">
                                                <button type="reset" class="btn btn--secondary">{{translate('reset')}}</button>
                                                @can('push_notification_add')
                                                    <button type="submit" class="btn btn--primary demo_check send-notification-btn">{{translate('Save & Send')}}</button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-1 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$toUserType=='all'?'active':''}}"
                                   href="{{url()->current()}}?to_user_type=all">
                                    {{translate('all')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$toUserType=='customer'?'active':''}}"
                                   href="{{url()->current()}}?to_user_type=customer">
                                    {{translate('customer')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$toUserType=='provider-admin'?'active':''}}"
                                   href="{{url()->current()}}?to_user_type=provider-admin">
                                    {{translate('provider')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$toUserType=='provider-serviceman'?'active':''}}"
                                   href="{{url()->current()}}?to_user_type=provider-serviceman">
                                    {{translate('serviceman')}}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex align-items-center flex-wrap gap-10 justify-content-between">
                                        <h4>{{ translate('Notification History') }}</h4>
                                        <div class="d-flex align-items-center gap-sm-3 gap-2 flex-sm-nowrap flex-wrap">
                                            <form action="{{url()->current()}}?to_user_type={{$toUserType}}" class="d-flex align-items-center gap-0 border rounded" method="POST">
                                                @csrf
                                                <input type="search" class="theme-input-style border-0 rounded block-size-36" value="{{$search}}" name="search" placeholder="{{translate('search by title')}}">
                                                <button type="submit" class="bg-light border-0 px-2 block-size-36 rounded-end d-flex align-items-center justify-content-center">
                                                    <span class="material-symbols-outlined fz-20 opacity-75">
                                                        search
                                                    </span>
                                                </button>
                                            </form>
                                            <div class="d-flex flex-wrap align-items-center gap-3 h-full">
                                                @can('push_notification_export')
                                                    <div class="dropdown h-100">
                                                        <button type="button" class="btn btn--secondary block-size-36 rounded text-capitalize dropdown-toggle" data-bs-toggle="dropdown">
                                                            <span class="material-symbols-outlined">cloud_download</span> {{translate('download')}}
                                                        </button>
                                                        @can('push_notification_export')
                                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                                <a class="dropdown-item"
                                                                   href="{{route('admin.push-notification.download')}}?search={{$search}}&&to_user_type={{$toUserType}}">
                                                                    {{translate('excel')}}
                                                                </a>
                                                            </ul>
                                                        @endcan
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive table-custom-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                                <tr>
                                                    <th class="text-dark fw-bold bg-light">{{translate('Sl')}}</th>
                                                    <th class="text-dark fw-bold bg-light">{{translate('cover image')}}</th>
                                                    <th class="text-dark fw-bold bg-light">{{translate('title')}}</th>
                                                    <th class="text-dark fw-bold bg-light">{{translate('description')}}</th>
                                                    <th class="text-dark fw-bold bg-light">{{translate('send to')}}</th>
                                                    <th class="text-dark fw-bold bg-light">{{translate('zones')}}</th>
                                                    @can('push_notification_manage_status')
                                                        <th class="text-dark fw-bold bg-light">{{translate('status')}}</th>
                                                    @endcan
                                                    @canany(['push_notification_delete', 'push_notification_update'])
                                                        <th class="text-dark fw-bold bg-light">{{translate('action')}}</th>
                                                    @endcan
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($pushNotification as $key => $item)
                                                <tr>
                                                    <td>{{$key+$pushNotification->firstItem()}}</td>
                                                    <td>
                                                        <img src="{{$item->cover_image_full_path}}" class="table-cover-img" alt="">
                                                    </td>
                                                    <td>
                                                        <div class="line-limit-2 min-w-80" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $item->title }}">
                                                            {{$item->title}}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="line-limit-2 min-w-150" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $item->description }}">
                                                            {{$item->description}}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="min-w180 position-relative max-w-293">
                                                            {{ $item->formatted_users }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="min-w180">
                                                            @foreach($item->zone_ids as $key=>$zone)
                                                                {{$zone['name']}}{{$key+1==count($item->zone_ids)?'':','}}
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    @can('push_notification_manage_status')
                                                        <td>
                                                            <label class="switcher">
                                                                <input class="switcher_input status-update"
                                                                       data-status="{{$item->id}}"
                                                                       data-id="{{ $item->id }}"
                                                                       type="checkbox" {{$item->is_active?'checked':''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        </td>
                                                    @endcan
                                                    @canany(['push_notification_delete', 'push_notification_update'])
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                    <button class="action-btn icon-hover btn--light-primary show-notification-details"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#notificationDetailsModal"
                                                                            data-id="{{ $item->id }}"
                                                                            data-title="{{ $item->title }}"
                                                                            data-description="{{ $item->description }}"
                                                                            data-image="{{ $item->cover_image_full_path }}"
                                                                            data-zone_ids='@json($item->zone_ids)'
                                                                            data-to_users='@json($item->to_users)'
                                                                            data-resend="{{ route('admin.push-notification.resend', [$item->id]) }}">
                                                                    <span class="material-symbols-outlined">visibility</span>
                                                                    </button>
                                                                @can('push_notification_update')

                                                                    <button class="action-btn btn--light-primary edit-notification-btn"
                                                                            data-bs-target="#edit__NotifiCation"
                                                                            data-bs-toggle="offcanvas"
                                                                            data-id="{{ $item->id }}"
                                                                            data-title="{{ $item->title }}"
                                                                            data-description="{{ $item->description }}"
                                                                            data-zone_ids='@json($item->zone_ids)'
                                                                            data-to_users='@json($item->to_users)'
                                                                            data-image="{{ $item->cover_image_full_path }}"
                                                                            data-action="{{ route('admin.push-notification.update', [$item->id]) }}"
                                                                            data-resend="{{ route('admin.push-notification.resend', [$item->id]) }}">
                                                                    <span class="material-icons">edit</span>
                                                                    </button>
                                                                @endcan
                                                                @can('push_notification_delete')
                                                                    <button type="button"
                                                                            data-id="{{$item->id}}"
                                                                            class="delete-content action-btn btn--danger"
                                                                            data-url="{{ route('admin.push-notification.delete', [$item->id]) }}"
                                                                            data-title="{{ translate('Are you sure') }}?"
                                                                            data-description="{{ translate('You will not be able to revert this!') }}"
                                                                            data-image="{{ asset('public/assets/admin-module/img/modal/delete-icon.svg') }}">
                                                                        <span class="material-icons">delete</span>
                                                                    </button>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    @endcan
                                                </tr>
                                                @empty
                                                    @include('adminmodule::layouts.partials.components._empty-state', [
                                                        'colspan' => 6
                                                            + (auth()->user()->can('push_notification_manage_status') ? 1 : 0)
                                                            + (auth()->user()->canAny(['push_notification_delete', 'push_notification_update']) ? 1 : 0),
                                                        'variant' => $search || $toUserType != 'all' ? 'search' : 'list'])
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $pushNotification->links() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--Short View Modal-->
        <div class="modal modal-scrolling-customize fade custom-confirmation-modal" id="notificationDetailsModal" tabindex="-1" aria-labelledby="notificationDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center justify-content-between px-3 pt-3">
                        <h3>{{ translate('Push Notification Short View') }}</h3>
                        <button type="button" class="btn-close bg-light rounded-full" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-30">
                        <div class="">
                            <div class="text-center mx-auto">
                                <img class="mb-20 notification-details-image-view" src="{{asset('public/assets/admin-module')}}/img/short-thumb.png" alt="">
                            </div>
                            <div class="bg-light rounded p-10 mb-15">
                                <div class="bg-white cus-shadow rounded">
                                    <div class="p-12 border-bottom">
                                        <h3 class="mb-2 fz-14 fw-medium">{{ translate('Title') }}</h3>
                                        <p class="fz-12 notif-title">—</p>
                                    </div>
                                    <div class="p-12">
                                        <h3 class="mb-2 fz-14 fw-medium">{{ translate('Description') }}</h3>
                                        <p class="fz-12 notif-description">—</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-light rounded p-10">
                                <div class="bg-white cus-shadow p-12 rounded">
                                    <div class="row g-lg-4 g-3">
                                        <div class="col-lg-6 border-end">
                                            <h3 class="mb-2 fz-14 fw-medium">{{ translate('Zones') }}</h3>
                                            <p class="fz-12 notif-zones">—</p>
                                        </div>
                                        <div class="col-lg-6">
                                            <h3 class="mb-2 fz-14 fw-medium">{{ translate('Targeted User') }}</h3>
                                            <p class="fz-12 notif-users">—</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <div class="choose-option">
                            <div class="d-flex gap-3 justify-content-end flex-wrap">
                                <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                                <a href="#" class="btn btn--primary px-3 d-flex align-items-center gap-0 resend-action-btn">
                                    <span class="material-symbols-outlined">refresh</span>
                                    {{ translate('Resend') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-end offcanvas-cus-sm" tabindex="-1" id="edit__NotifiCation" aria-labelledby="edit__NotifiCationLabel">
                <div class="offcanvas-header bg-light d-flex align-items-center justify-content-between">
                    <h2 class="mb-0">{{ translate('Edit Push Notification') }}</h2>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" class="btn btn--primary px-3 d-flex align-items-center gap-0 resend-action-btn">
                            <span class="material-symbols-outlined">refresh</span>
                            {{ translate('Resend') }}
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                </div>

                <form action="#" method="POST" id="update-form-submit" enctype="multipart/form-data" data-ff-validate novalidate>
                    @csrf
                    @method('PUT')
                    <div class="offcanvas-body">
                        <div class="edit-push-notification d-flex flex-column gap-3">
                            <div class="bg-light rounded p-20">
                                @include('adminmodule::admin.partials._single-image-upload', [
                                    'name'             => 'cover_image',
                                    'id'               => 'imageUpload02',
                                    'title'            => translate('Cover Image'),
                                    'required'         => false,
                                    'image'            => null,
                                    'ratio'            => 'ratio-2-1',
                                    'instructionRatio' => '2:1',
                                    'showDelete'       => false])
                            </div>

                            <div class="bg-light rounded p-20">
                                <div class="d-flex flex-column gap-3">
                                    @include('partials._form-field', [
                                        'type'        => 'textarea',
                                        'name'        => 'title',
                                        'id'          => 'edit_notification_title',
                                        'label'       => translate('Title'),
                                        'placeholder' => translate('Type title'),
                                        'rows'        => 1,
                                        'required'    => true,
                                        'maxlength'   => 100,
                                        'charCount'   => true,
                                        'extraAttrs'  => 'data-maxlength="100"',
                                        'wrapClass'   => 'mb-0'])
                                    @include('partials._form-field', [
                                        'type'        => 'textarea',
                                        'name'        => 'description',
                                        'id'          => 'edit_notification_description',
                                        'label'       => translate('Description'),
                                        'placeholder' => translate('Type about the description'),
                                        'rows'        => 2,
                                        'required'    => true,
                                        'maxlength'   => 200,
                                        'charCount'   => true,
                                        'extraAttrs'  => 'data-maxlength="200"',
                                        'wrapClass'   => 'mb-0'])
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'zone_ids[]',
                                        'id'          => 'edit_zone_ids',
                                        'label'       => translate('Zones'),
                                        'multiple'    => true,
                                        'required'    => true,
                                        'selectClass' => 'select-zone theme-input-style w-100',
                                        'options'     => $zones->pluck('name', 'id')->toArray(),
                                        'wrapClass'   => 'mb-0'])
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'to_users[]',
                                        'id'          => 'edit_to_users',
                                        'label'       => translate('Targeted User'),
                                        'multiple'    => true,
                                        'required'    => true,
                                        'selectClass' => 'select-users theme-input-style w-100',
                                        'options'     => [
                                            'all'                 => translate('all'),
                                            'customer'            => translate('customer'),
                                            'provider-admin'      => translate('provider'),
                                            'provider-serviceman' => translate('serviceman')],
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="offcanvas-footer d-flex gap-3 justify-content-end border-top py-3 px-3">
                        <button type="reset" class="btn btn--secondary">{{ translate('reset') }}</button>
                        @can('push_notification_update')
                            <button type="submit" class="btn btn--primary demo_check">{{ translate('Update') }}</button>
                        @endcan
                    </div>

                </form>
            </div>
    </div>

@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script>
        "use strict";

        function setGlobalImagePreview($wrapper, imageSrc) {
            if (!$wrapper || !$wrapper.length) return;
            if (imageSrc) {
                $wrapper.addClass('has-image');
                $wrapper.find('.global-image-preview').attr('src', imageSrc).removeClass('d-none');
                $wrapper.find('.global-upload-box').addClass('global-upload-box-hidden');
                $wrapper.find('.overlay-icons').removeClass('d-none');
            } else {
                $wrapper.removeClass('has-image');
                $wrapper.find('.global-image-preview').attr('src', '').addClass('d-none');
                $wrapper.find('.global-upload-box').removeClass('global-upload-box-hidden');
                $wrapper.find('.overlay-icons').addClass('d-none');
                $wrapper.find('input[type="file"]').val('');
            }
        }

        $(document).ready(function () {
            $('#zone_selector__select4').select2({
                placeholder: "{{ translate('select zones') }}",
                width: '100%'
            });

            $('#user_selector__select').select2({
                placeholder: "{{ translate('select users') }}",
                width: '100%'
            });

            $('#user_selector__select').on('change', function () {
                var selectedValues = $(this).val();
                if (selectedValues && selectedValues.includes('all')) {
                    var allOptions = $(this).find('option[value!="all"]').map(function () {
                        return this.value;
                    }).get();
                    $(this).val(allOptions).trigger('change');
                }
            });
            $('#zone_selector__select4').on('change', function () {
                var selectedValues = $(this).val();
                if (selectedValues && selectedValues.includes('all')) {
                    var allOptions = $(this).find('option[value!="all"]').map(function () {
                        return this.value;
                    }).get();
                    $(this).val(allOptions).trigger('change');
                }
            });
        });


        $(document).on('click', '.show-notification-details', function () {
            const button = $(this);
            const modal = $('#notificationDetailsModal');

            const title = button.data('title');
            const description = button.data('description');
            const zones = button.data('zone_ids');
            const users = button.data('to_users');
            const image = button.data('image');
            const resendUrl = button.data('resend');

            modal.find('.modal-body .notif-title').text(title);
            modal.find('.modal-body .notif-description').text(description);
            modal.find('.notification-details-image-view').attr('src', image).show();
            modal.find('.resend-action-btn').attr('href', resendUrl);

            let zoneText = '';
            try {
                const parsedZones = typeof zones === 'string' ? JSON.parse(zones) : zones;
                zoneText = parsedZones.map(z => z.name).join(', ');
            } catch (e) { zoneText = '—'; }

            let userText = '';
            try {
                const parsedUsers = typeof users === 'string' ? JSON.parse(users) : users;
                userText = parsedUsers.join(', ');
            } catch (e) { userText = '—'; }

            modal.find('.modal-body .notif-zones').text(zoneText);
            modal.find('.modal-body .notif-users').text(userText);
        });

        $(document).on('click', '.edit-notification-btn', function () {
            const button = $(this);
            const title = button.data('title');
            const description = button.data('description');
            const toUsers = button.data('to_users');
            const image = button.data('image');
            const zoneIds = (button.data('zone_ids') || []).map(z => z.id);
            const actionUrl = button.data('action');
            const resendUrl = button.data('resend');

            const offcanvas = $('#edit__NotifiCation');

            offcanvas.data('original-title',       title);
            offcanvas.data('original-description', description);
            offcanvas.data('original-zones',       zoneIds);
            offcanvas.data('original-users',       toUsers || []);
            offcanvas.data('original-image',       image || '');

            offcanvas.find('form').attr('action', actionUrl);
            offcanvas.find('.resend-action-btn').attr('href', resendUrl);

            offcanvas.find('textarea[name="title"]').val(title).trigger('input');
            offcanvas.find('textarea[name="description"]').val(description).trigger('input');

            $('#edit_zone_ids').val(zoneIds).trigger('change');
            $('#edit_to_users').val(toUsers).trigger('change');

            setGlobalImagePreview(offcanvas.find('.global-image-upload').first(), image);
        });

        function updateLetterCount(textarea) {
            const max = textarea.data('maxlength');
            const len = textarea.val().length;
            textarea.closest('.message-textarea').find('.letter-count').text(len + '/' + max);
        }
        $(document).on('input', 'textarea[data-maxlength]', function () {
            updateLetterCount($(this));
        });


        $('#edit_zone_ids, #edit_to_users').select2({
            dropdownParent: $('#edit__NotifiCation'),
            width: '100%'
        });

        function resetScope($scope) {
            setTimeout(function () {
                $scope.find('input, textarea').each(function () {
                    var $i = $(this);
                    var type = ($i.attr('type') || '').toLowerCase();
                    if (type === 'checkbox' || type === 'radio') {
                        $i.prop('checked', $i.prop('defaultChecked'));
                    } else if (type === 'file') {
                        $i.val('');
                    } else {
                        $i.val($i.prop('defaultValue') || '');
                    }
                });
                $scope.find('select').each(function () {
                    var $s = $(this);
                    $s.find('option').each(function () {
                        this.selected = this.defaultSelected;
                    });
                    $s.trigger('change.select2');
                });

                if (window.FormCharCount) {
                    $scope.find('[data-char-count]').each(function () {
                        window.FormCharCount.update(this);
                    });
                }

                $scope.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
            }, 0);
        }

        $(document).ready(function () {
            $('#send-notification-form').on('reset', function () {
                const $form = $(this);
                resetScope($form);
                setTimeout(function () {
                    setGlobalImagePreview($form.find('.global-image-upload'), '');
                }, 0);
            });

            $('#update-form-submit').on('reset', function () {
                const $form = $(this);
                const $offcanvas = $('#edit__NotifiCation');
                setTimeout(function () {
                    $form.find('textarea[name="title"]')
                        .val($offcanvas.data('original-title') || '')
                        .trigger('input');
                    $form.find('textarea[name="description"]')
                        .val($offcanvas.data('original-description') || '')
                        .trigger('input');

                    $('#edit_zone_ids').val($offcanvas.data('original-zones') || []).trigger('change');
                    $('#edit_to_users').val($offcanvas.data('original-users') || []).trigger('change');

                    setGlobalImagePreview(
                        $form.find('.global-image-upload').first(),
                        $offcanvas.data('original-image') || ''
                    );

                    $form.find('.ff-field__messages .error, .ff-field__messages label.error').remove();
                }, 0);
            });
        });

        let selectedNotificationItem;
        let selectedStatusRoute;
        let notificationInitialState;

        $(document).on('change', '.status-update', function (e) {
            e.preventDefault();

            selectedNotificationItem = $(this);
            notificationInitialState = selectedNotificationItem.prop('checked');

            selectedNotificationItem.prop('checked', !notificationInitialState);

            let itemId = selectedNotificationItem.data('id');
            selectedStatusRoute = '{{ route('admin.push-notification.status-update', ['id' => ':itemId']) }}'.replace(':itemId', itemId);

            let confirmationTitleText = notificationInitialState
                ? '{{ translate('Are you sure') }}?'
                : '{{ translate('Are you sure') }}?';

            $('.confirmation-title-text').text(confirmationTitleText);

            let confirmationDescriptionText = notificationInitialState
                ? '{{ translate('You want to Turn On the Push Notification Status') }}?'
                : '{{ translate('You want to Turn Off the Push Notification Status') }}?';

            $('.confirmation-description-text').text(confirmationDescriptionText);

            let imgSrc = notificationInitialState
                ? "{{ asset('public/assets/admin-module/img/icons/status-on.png') }}"
                : "{{ asset('public/assets/admin-module/img/icons/status-off.png') }}";

            $('#confirmChangeModal img').attr('src', imgSrc);

            showModal();
        });

        $('#confirmChange').on('click', function () {
            updateStatus(selectedStatusRoute);
        });

        $('.cancel-change').on('click', function () {
            resetCheckboxState();
            hideModal();
        });

        $('#confirmChangeModal').on('hidden.bs.modal', function () {
            resetCheckboxState();
        });

        function showModal() { $('#confirmChangeModal').modal('show'); }
        function hideModal() { $('#confirmChangeModal').modal('hide'); }

        function resetCheckboxState() {
            if (selectedNotificationItem) {
                selectedNotificationItem.prop('checked', !notificationInitialState);
            }
        }

        $(document).ready(function () {
            if (!window.FormValidator) return;

            FormValidator.register('#send-notification-form', {
                submitHandler: function (form) {
                    const $btn = $(form).find('.send-notification-btn');
                    if ($btn.prop('disabled')) return false;
                    if (!$btn.data('ffOriginalHtml')) {
                        $btn.data('ffOriginalHtml', $btn.html());
                    }
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        '{{ translate("Sending...") }}'
                    );
                    form.submit();
                }
            });

            FormValidator.register('#update-form-submit', {
                submitHandler: function (form) {
                    const $btn = $(form).find('button[type="submit"]');
                    if ($btn.prop('disabled')) return false;
                    if (!$btn.data('ffOriginalHtml')) {
                        $btn.data('ffOriginalHtml', $btn.html());
                    }
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>' +
                        '{{ translate("Updating...") }}'
                    );
                    form.submit();
                }
            });
        });


    </script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/jquery.dataTables.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/dataTables/dataTables.select.min.js"></script>
@endpush
