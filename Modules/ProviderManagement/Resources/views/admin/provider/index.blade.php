@extends('adminmodule::layouts.master')

@section('title',translate('provider_list'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-30">
                <h2 class="page-title">{{translate('Provider_List')}}</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="row mb-4 g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__total_provider">
                                <h2>{{$topCards['total_providers']}}</h2>
                                <h3>{{translate('Total_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/subscribed-providers.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__newly_joined">
                                <h2>{{$topCards['total_active_providers']}}</h2>
                                <h3>{{translate('Active_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/newly-joined.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__not_served">
                                <h2>{{$topCards['total_inactive_providers']}}</h2>
                                <h3>{{translate('Inactive_Providers')}}</h3>
                                <img src="{{asset('public/assets/admin-module/img/icons/not-served.png')}}"
                                     class="absolute-img" alt="{{ translate('providers') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{$status=='all'?'active':''}}"
                           href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}">
                            {{translate('all')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status=='active'?'active':''}}"
                           href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}">
                            {{translate('active')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status=='inactive'?'active':''}}"
                           href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}">
                            {{translate('inactive')}}
                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2 fw-medium">
                    <span class="opacity-75">{{ translate('Total_Providers:') }}</span>
                    <span class="title-color">{{$providers->total()}}</span>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-tab-pane">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <h4 class="m-0">Provider List</h4>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <form action="{{ url()->current() }}" class="d-flex align-items-center gap-0 border rounded" method="GET">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <input type="search" class="theme-input-style border-0 rounded block-size-36" name="search" value="{{$search}}" placeholder="{{translate('search by provider name, email, phone')}}">
                                        <button type="submit" class="bg-light border-0 px-2 block-size-36 rounded-end d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-outlined fz-20 opacity-75">
                                                search
                                            </span>
                                        </button>
                                    </form>
                                    @can('provider_export')
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn rounded btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> {{translate('download')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.provider.download')}}?search={{$search}}&status={{ $status }}">
                                                    {{translate('excel')}}
                                                </a>
                                            </ul>
                                        </div>
                                    @endcan

                                </div>
                            </div>

                            <div class="table-responsive">
                                @php
                                    $providerIndexTableColspan = 5;
                                @endphp
                                @can('provider_manage_status')
                                    @php
                                        $providerIndexTableColspan += 2;
                                    @endphp
                                @endcan
                                @canany(['provider_delete', 'provider_update'])
                                    @php
                                        $providerIndexTableColspan++;
                                    @endphp
                                @endcanany
                                <table id="example" class="table align-middle">
                                    <thead class="align-middle">
                                    <tr>
                                        <th>{{translate('Sl')}}</th>
                                        <th>{{translate('Provider')}}</th>
                                        <th class="min-w-120">{{translate('Contact_Info')}}</th>
                                        <th class="min-w-120">{{translate('Total_Subscribed_Sub_Categories')}}</th>
                                        <th class="min-w-120">{{translate('Total_Booking_Served')}}</th>
                                        @can('provider_manage_status')
                                            <th>{{translate('Service Availability')}}</th>
                                            <th>{{translate('Status')}}</th>
                                        @endcan
                                        @canany(['provider_delete', 'provider_update'])
                                            <th>{{translate('Action')}}</th>
                                        @endcan
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $ongoingBookings = 0;
                                        $acceptedBookings = 0;
                                    @endphp
                                    @forelse($providers as $key => $provider)
                                        <tr>
                                            <td>{{$key+$providers->firstItem()}}</td>
                                            <td>
                                                <div class="media align-items-center gap-3 min-w-200">
                                                    <div class="avatar avatar-lg">
                                                        <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}">
                                                            <img class="avatar-img radius-5" src="{{ $provider->logo_full_path }}" alt="{{ translate('provider-logo') }}">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="mb-1">
                                                            <a href="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}&provider={{ $provider->id}}">
                                                                {{$provider->company_name}}
                                                                @if($provider?->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)
                                                                    <span
                                                                        class="text-danger fz-12">{{('(' . translate('Suspended') . ')')}}</span>
                                                                @endif

                                                            </a>
                                                        </h5>
                                                        <span
                                                            class="common-list_rating d-flex align-items-center gap-1">
                                                            <span class="material-icons">star</span>
                                                            {{$provider->avg_rating}}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <h5 class="mb-1">{{Str::limit($provider->contact_person_name, 30)}}</h5>
                                                    <a class="fz-12"
                                                       href="mobileto:{{$provider->contact_person_phone}}">{{$provider->contact_person_phone}}</a>
                                                    <a class="fz-12"
                                                       href="mobileto:{{$provider->contact_person_email}}">{{$provider->contact_person_email}}</a>
                                                </div>
                                            </td>
                                            <td>
                                                <p>{{$provider->subscribed_services_count}}</p>
                                            </td>
                                            <td>{{$provider->bookings_count}}</td>
                                            @can('provider_manage_status')
                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input provider-service-availability-update"
                                                               id="provider-service-availability-{{$provider->id}}"
                                                               data-route="{{route('admin.provider.service_availability', [$provider->id])}}"
                                                               data-message="{{translate('want_to_update_status')}}"
                                                               data-provider-id="{{$provider->id}}"
                                                               type="checkbox" {{$provider->service_availability?'checked':''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>


                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input provider-status-update"
                                                               id="provider-status-{{$provider->id}}"
                                                               data-route="{{route('admin.provider.status_update', [$provider->id])}}"
                                                               data-message="{{translate('want_to_update_status')}}"
                                                               data-provider-id="{{$provider->id}}"
                                                               type="checkbox" {{$provider?->owner?->is_active == 1 ? 'checked' : ''}}>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            @endcan
                                            @canany(['provider_delete', 'provider_update'])
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        @can('provider_update')
                                                            <a href="{{route('admin.provider.edit',[$provider->id])}}"
                                                               class="action-btn btn--light-primary"
                                                               style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        @endcan
                                                        @php
                                                            $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values; @endphp
                                                        @can('provider_delete')
                                                            <button type="button"
                                                                    class="action-btn btn--danger provider-delete"
                                                                    style="--size: 30px"
                                                                    data-provider="delete-{{$provider->id}}"
                                                                    data-ongoing="{{$provider->bookings->where('booking_status', 'ongoing')->count() ?? 0}}"
                                                                    data-payable="{{(float) ($provider?->owner?->account?->account_payable ?? 0)}}"
                                                                    data-receivable="{{(float) ($provider?->owner?->account?->account_receivable ?? 0)}}"
                                                                    data-accepted="{{$provider->bookings->where('booking_status', 'accepted')
                                                            ->where('provider_id', $provider->id)
                                                            ->count() ?? 0}}"
                                                                    data-url="{{route('admin.provider.details',[$provider->id, 'web_page'=>'bookings'])}}"
                                                                    data-account-url="{{route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])}}">
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <form
                                                                action="{{route('admin.provider.delete',[$provider->id])}}"
                                                                method="post" id="delete-{{$provider->id}}"
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
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $providerIndexTableColspan,
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                            'titleKey' => 'No Provider Found'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $providers->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{ asset('/public/assets/provider-module/img/profile-delete.png') }}" alt="">
                        <h3>{{ translate('Sorry you can’t delete this provider account!') }}</h3>
                        <p class="fw-medium">{{ translate('Provider must have to complete the ongoing and accepted bookings.') }}</p>
                        <a href="#" id="bookingRequestLink">
                            <button type="reset" class="btn btn--primary">{{ translate('Booking Request') }}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{ asset('/public/assets/provider-module/img/profile-delete.png') }}" alt="">
                        <h3>{{ translate('Sorry you can’t delete this provider account!') }}</h3>
                        <p class="fw-medium">{{ translate('Provider payable, withdrawable and other transaction balances must be cleared before deleting this account.') }}</p>
                        <a href="#" id="providerAccountLink">
                            <button type="reset" class="btn btn--primary">{{ translate('View Account Overview') }}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict";

        $('.provider-service-availability-update, .provider-status-update').on('click', function () {
            let $this = $(this);
            let providerId = $this.data('provider-id');
            let initialState = $this.prop('checked');
            let route = $this.data('route');
            let message = $this.data('message');

            route_alert_reload(route, message, true, initialState ? 1 : 0, $this.attr('id'));
        });

        $('.provider-delete').on('click', function () {
            let provider = $(this).data('provider');
            let url = $(this).data('url');
            let accountUrl = $(this).data('account-url');
            let accepted = $(this).data('accepted');
            let ongoing = $(this).data('ongoing');
            let payable = Number($(this).data('payable')) || 0;
            let receivable = Number($(this).data('receivable')) || 0;
            let message = "{{ translate('want_to_delete_your_account') }}";

            if ('{{ env('APP_ENV') == 'demo' }}') {
                toastr.info('This function is disabled for demo mode', {
                    closeButton: true,
                    progressBar: true
                });
            } else {
                if (accepted !== 0 || ongoing !== 0) {
                    $('#exampleModal').data('url', url);
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exampleModal'));
                    modal.show();
                } else if (payable !== 0 || receivable !== 0) {
                    $('#accountModal').data('url', accountUrl);
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('accountModal'));
                    modal.show();
                } else {
                    form_alert(provider, message);
                }
            }
        });

        $('#exampleModal').on('show.bs.modal', function (event) {
            let url = $(this).data('url');
            $('#bookingRequestLink').attr('href', url);
        });

        $('#accountModal').on('show.bs.modal', function () {
            let url = $(this).data('url');
            $('#providerAccountLink').attr('href', url);
        });

    </script>
@endpush
