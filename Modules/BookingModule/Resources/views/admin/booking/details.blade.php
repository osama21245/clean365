@extends('adminmodule::layouts.master')

@section('title', translate('Booking_Details'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="page-title-wrap mb-3">
            <h2 class="page-title">{{ translate('Booking_Details') }} </h2>
        </div>

        <div class="pb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <h3 class="c1 fw-bold">{{ translate('Booking') }} # {{ $booking['readable_id'] }}</h3>
                    <span class="badge badge-{{
    $booking->booking_status == 'ongoing' ? 'warning' :
    ($booking->booking_status == 'completed' ? 'success' :
        ($booking->booking_status == 'canceled' ? 'danger' : 'info'))
                        }}">
                        {{ translate(ucwords($booking->booking_status)) }}
                    </span>
                </div>
                <p class="opacity-75 fz-12">{{ translate('Booking_Placed') }}
                    : {{ date('d-M-Y h:ia', strtotime($booking->created_at)) }}</p>
            </div>
            <div class="d-flex flex-wrap flex-xxl-nowrap gap-3">
                <div class="d-flex flex-wrap gap-3">
@if (
                            (in_array($booking['booking_status'], ['accepted', 'ongoing']) &&
                                $booking->booking_partial_payments->isEmpty() &&
                                empty($booking->customizeBooking)) &&
                            ($booking->is_guest == 1 && $booking->payment_method != "cash_after_service" ? false : true)
                        )
                        @can('booking_edit')
                            <button class="btn btn--primary" data-bs-toggle="modal"
                                data-bs-target="#serviceUpdateModal--{{ $booking['id'] }}" data-toggle="tooltip"
                                title="{{ translate('Add or remove services') }}">
                                <span class="material-symbols-outlined">edit</span>{{ translate('Edit Services') }}
                            </button>
                        @endcan
                    @endif
                    <a href="{{ route('admin.booking.invoice', [$booking->id]) }}" class="btn btn-primary"
                        target="_blank">
                        <span class="material-icons">description</span>{{ translate('Invoice') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center flex-xxl-nowrap gap-3 mb-4">
            <ul class="nav nav--tabs nav--tabs__style2">
                <li class="nav-item">
                    <a class="nav-link {{ $webPage == 'details' ? 'active' : '' }}"
                        href="{{ url()->current() }}?web_page=details">{{ translate('details') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $webPage == 'status' ? 'active' : '' }}"
                        href="{{ url()->current() }}?web_page=status">{{ translate('status') }}</a>
                </li>
            </ul>
</div>

        <div class="row gy-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body pb-5">
                        <div class="border-bottom pb-3 mb-3">
                            <div
                                class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-3 flex-wrap mb-40">
                                <div>
                                    <h4 class="mb-2">{{ translate('Payment_Method') }}</h4>
                                    <h5 class="c1 mb-2 fw-bold"><span
                                            class="text-capitalize">{{ str_replace(['_', '-'], ' ', $booking->payment_method) }}
                                        </span>
                                    </h5>
                                    <p>
                                        <span>{{ translate('Amount') }} : </span>
                                        <span
                                            class="c1">{{ with_currency_symbol($booking->total_booking_amount) }}</span>
                                    </p>
                                </div>
                                <div class="text-start text-sm-end">
<h5 class="d-flex gap-1 flex-wrap align-items-center">
                                        <div>{{ translate('Schedule_Date') }} :</div>
                                        <div id="service_schedule__span">
                                            <div>{{ date('d-M-Y h:ia', strtotime($booking->service_schedule)) }} <span
                                                    class="text-secondary">{{ $booking?->schedule_histories->count() > 1 ? translate('(Edited)') : '' }}</span>
                                            </div>

                                            <div class="timeline-container">
                                                <ul class="timeline-sessions">
                                                    <p class="fs-14">{{ translate('Schedule Change Log') }}</p>
                                                    @foreach ($booking?->schedule_histories()->orderBy('created_at', 'desc')->get() as $history)
                                                        <li
                                                            class="{{ $booking->service_schedule == $history->schedule ? 'active' : '' }}">
                                                            <div class="timeline-user">
                                                                {{ isset($history->user) ? Str::limit($history->user->first_name.' '.$history->user->last_name, 30) : '' }}
                                                            </div>
                                                            <div class="timeline-date">
                                                                {{ \Carbon\Carbon::parse($history->schedule)->format('d-M-Y') }}
                                                            </div>
                                                            <div class="timeline-time">
                                                                {{ \Carbon\Carbon::parse($history->schedule)->format('h:i A') }}
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-start gap-2">
                            <h3 class="mb-3">{{ translate('Booking_Summary') }}</h3>
                        </div>

                        <div class="table-responsive border-bottom">
                            <table class="table text-nowrap align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-lg-3">{{ translate('Service') }}</th>
                                        <th>{{ translate('Price') }}</th>
                                        <th>{{ translate('Qty') }}</th>
                                        <th>{{ translate('Discount') }}</th>
                                                                                <th class="text--end">{{ translate('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($subTotal = 0)
                                    @forelse ($booking->detail as $detail)
                                    <tr>
                                        <td class="text-wrap ps-lg-3">
                                            @if (isset($detail->service))
                                                <div class="d-flex flex-column">
                                                    <a href="{{ route('admin.service.detail', [$detail->service->id]) }}"
                                                        class="fw-bold">{{ Str::limit($detail->service->name, 30) }}</a>
                                                    <div class="text-capitalize">
                                                        {{ Str::limit($detail ? $detail->variant_key : '', 50) }}
                                                    </div>
                                                    @if ($detail->overall_coupon_discount_amount > 0)
                                                        <small class="fz-10 text-capitalize">{{ translate('coupon_discount') }}
                                                            :
                                                            -{{ with_currency_symbol($detail->overall_coupon_discount_amount) }}</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span
                                                    class="badge badge-pill badge-danger">{{ translate('Service_unavailable') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ with_currency_symbol($detail->service_cost) }}</td>
                                        <td>
                                            <span>{{ $detail->quantity }}</span>
                                        </td>
                                        <td>
                                            @if ($detail?->discount_amount > 0)
                                                {{ with_currency_symbol($detail->discount_amount) }}
                                            @else
                                                {{ with_currency_symbol(0) }}
                                            @endif

                                        </td>
                                        <td class="text--end">{{ with_currency_symbol($detail->total_cost) }}</td>
                                    </tr>
                                    @php($subTotal += $detail->service_cost * $detail->quantity)
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => 5,
                                            'variant' => 'list'])
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row justify-content-end mt-3">
                            <div class="col-sm-10 col-md-6 col-xl-5">
                                <div class="table-responsive">
                                    <table class="table-md title-color align-right w-100">
                                        <tbody>
                                            <tr>
                                                <td class="text-capitalize">{{ translate('service_amount') }}</td>
                                                <td class="text--end pe--4">{{ with_currency_symbol($subTotal) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-capitalize">
                                                    {{ translate('service_discount') }}
                                                    @if($booking->total_discount_amount > 0)
                                                        @include('bookingmodule::partials.discount-tooltip-icon', [
                                                            'admin' => $booking->details_amounts->sum('discount_by_admin'),
                                                            'provider' => $booking->details_amounts->sum('discount_by_provider')])
                                                    @endif
                                                </td>
                                                <td class="text--end pe--4">
                                                    {{ with_currency_symbol($booking->total_discount_amount) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-capitalize">
                                                    {{ translate('coupon_discount') }}
                                                    @if($booking->total_coupon_discount_amount > 0)
                                                        @include('bookingmodule::partials.discount-tooltip-icon', [
                                                            'admin' => $booking->details_amounts->sum('coupon_discount_by_admin'),
                                                            'provider' => $booking->details_amounts->sum('coupon_discount_by_provider')])
                                                    @endif
                                                </td>
                                                <td class="text--end pe--4">
                                                    {{ with_currency_symbol($booking->total_coupon_discount_amount) }}
                                                </td>
                                            </tr>
                                            @if($booking->total_referral_discount_amount > 0)
                                                <tr>
                                                    <td class="text-capitalize">{{ translate('Referral Discount') }}</td>
                                                    <td class="text--end pe--4">
                                                        {{ with_currency_symbol($booking->total_referral_discount_amount) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            @if ($booking->extra_fee > 0)
                                            @php($additional_charge_label_name = business_config('additional_charge_label_name', 'booking_setup')->live_values ?? 'Fee')
                                            <tr>
                                                <td class="text-capitalize">{{ translate($additional_charge_label_name) }}
                                                </td>
                                                <td class="text--end pe--4">
                                                    {{ with_currency_symbol($booking->extra_fee) }}
                                                </td>
                                            </tr>
                                            @endif

                                            <tr>
                                                <td><strong>{{ translate('Grand_Total') }}</strong></td>
                                                <td class="text--end pe--4">
                                                    <strong>{{ with_currency_symbol($booking->total_booking_amount) }}</strong>
                                                </td>
                                            </tr>

                                            @if ($booking->booking_partial_payments->isNotEmpty())
                                                @foreach ($booking->booking_partial_payments as $partial)
                                                    <tr>
                                                        <td>{{ translate('Paid by :method', ['method' => str_replace('_', ' ', $partial->paid_with)]) }}
                                                        </td>
                                                        <td class="text--end pe--4">
                                                            {{ with_currency_symbol($partial->paid_amount) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            <?php
$dueAmount = 0;

if (!$booking->is_paid && $booking?->booking_partial_payments?->count() == 1) {
    $dueAmount = $booking->booking_partial_payments->first()?->due_amount;
}

if (in_array($booking->booking_status, ['pending', 'accepted', 'ongoing']) && $booking->payment_method != 'cash_after_service' && $booking->additional_charge > 0) {
    $dueAmount += $booking->additional_charge;
}

if (!$booking->is_paid && $booking->payment_method == 'cash_after_service') {
    $dueAmount = $booking->total_booking_amount;
}
                                                ?>

                                            @if ($dueAmount > 0)
                                                <tr>
                                                    <td>{{ translate('Due_Amount') }}</td>
                                                    <td class="text--end pe--4">
                                                        {{ with_currency_symbol($dueAmount) }}
                                                    </td>
                                                </tr>
                                            @endif

                                            @if ($booking->payment_method != 'cash_after_service' && $booking->additional_charge < 0)
                                                <tr>
                                                    <td>{{ translate('Refund') }}</td>
                                                    <td class="text--end pe--4">
                                                        {{ with_currency_symbol(abs($booking->additional_charge)) }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="c1">{{ translate('Booking Setup') }}</h3>
                        <hr>
                        @can('booking_can_manage_status')
                            <div class="mt-3">
                                @if ($booking->booking_status != 'pending')
                                    <select class="js-select without-search" id="booking_status">
                                        <option value="0" disabled {{ $booking['booking_status'] == 'accepted' ? 'selected' : '' }}>
                                            {{ translate('Booking_Status: :status', ['status' => translate('Accepted')]) }}
                                        </option>
                                        <option value="ongoing" {{ $booking['booking_status'] == 'ongoing' ? 'selected' : '' }}>
                                            {{ translate('Booking_Status: :status', ['status' => translate('Ongoing')]) }}
                                        </option>
                                        <option value="completed" {{ $booking['booking_status'] == 'completed' ? 'selected' : '' }}>
                                            {{ translate('Booking_Status: :status', ['status' => translate('Completed')]) }}
                                        </option>
                                        @if ($booking->booking_status != 'completed')
                                            <option value="canceled" {{ $booking['booking_status'] == 'canceled' ? 'selected' : '' }}>
                                                {{ translate('Booking_Status: :status', ['status' => translate('Canceled')]) }}
                                            </option>
                                        @endif
                                    </select>
                                @endif
                            </div>
                        @endcan

                        <div class="mt-3">
                            @if (!in_array($booking->booking_status, ['ongoing', 'completed']))
                                @can('booking_can_manage_status')
                                    <input type="datetime-local" class="form-control h-45" name="service_schedule"
                                        value="{{ $booking->service_schedule }}" id="service_schedule"
                                        data-original="{{ $booking->service_schedule }}" min="<?php        echo date('Y-m-d\TH:i'); ?>"
                                        onchange="service_schedule_update()">
                                @endcan
                            @endif
                        </div>


                        <div class="py-3 d-flex flex-column gap-3 mb-2">
                            @if ($booking->evidence_photos)
                                <div class="c1-light-bg radius-10 py-3 px-4">
                                    <div class="d-flex justify-content-start gap-2">
                                        <h4 class="mb-2">{{ translate('uploaded_Images') }}</h4>
                                    </div>

                                    <div class="py-3 px-4">
                                        <div class="d-flex flex-wrap gap-3 justify-content-lg-start">
                                            @foreach ($booking->evidence_photos_full_path ?? [] as $key => $img)
                                                <img width="100" class="max-height-100" src="{{ $img }}"
                                            alt="{{ translate('evidence-photo') }}" @endforeach </div>
                                        </div>
                                    </div>
                            @endif

                                @php($serviceAtProviderPlace = (int) ((business_config('service_at_provider_place', 'provider_config'))->live_values ?? 0))
                                <div class="c1-light-bg radius-10">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">map</span>
                                            {{ translate('Service_location') }}
                                        </h4>
                                        @if($serviceAtProviderPlace == 1)
                                        @if($booking->provider_id)
                                        @php($serviceLocation = getProviderSettings(providerId: $booking->provider_id, key: 'service_location', type: 'provider_config'))
                                            @if(in_array('customer', $serviceLocation) && in_array('provider', $serviceLocation))
                                                <div class="btn-group">
                                                    @can('booking_edit')
                                                        <div data-bs-toggle="modal"
                                                            data-bs-target="#serviceLocationModal--{{ $booking['id'] }}"
                                                            data-toggle="tooltip" data-placement="top">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="material-symbols-outlined">edit_square</span>
                                                            </div>
                                                        </div>
                                                    @endcan
                                                </div>
                                            @endif
                                        @else
                                        <div class="btn-group">
                                            @can('booking_edit')
                                                <div data-bs-toggle="modal"
                                                    data-bs-target="#serviceLocationModal--{{ $booking['id'] }}"
                                                    data-toggle="tooltip" data-placement="top">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="material-symbols-outlined">edit_square</span>
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                        @endif
                                        @endif
                                    </div>

                                    <div class="py-3 px-4">
                                        @if($booking->service_location == 'provider')
                                            <div class="bg-warning p-3 rounded">
                                                <h5>{{ translate('Customer has to go to the Provider Location to receive the service') }}
                                                </h5>
                                            </div>
                                            <div class="mt-3">
                                                @if($booking->provider_id != null)
                                                    @if($booking->provider)
                                                        @php($mapLat = $booking->provider->coordinates['latitude'] ?? null)
                                                        @php($mapLng = $booking->provider->coordinates['longitude'] ?? null)
                                                        <h5 class="mb-1">{{ translate('Service Location') }}:</h5>
                                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                                            <p class="mb-0">{{ Str::limit($booking?->provider?->company_address ?? translate('not_available'), 100) }}
                                                            </p>
                                                            @if($mapLat && $mapLng)
                                                                <a href="https://www.google.com/maps?q={{ $mapLat }},{{ $mapLng }}"
                                                                   target="_blank" rel="noopener noreferrer"
                                                                   class="btn btn-sm btn--primary text-nowrap d-inline-flex align-items-center gap-1">
                                                                    <span class="material-icons" style="font-size:16px">map</span>
                                                                    {{ translate('View_on_Map') }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p>{{ translate('Provider Unavailable') }}</p>
                                                    @endif
                                                @else
                                                    <h5 class="mb-1">{{ translate('Service Location') }}:</h5>
                                                    <p>{{ translate('The Service Location will be available after this booking accepts or assign to a provider') }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="bg-warning p-3 rounded">
                                                <h5>{{ translate('Provider has to go to the Customer Location to provide the service') }}
                                                </h5>
                                            </div>
                                            <div class="mt-3">
                                                @php($mapLat = $booking?->service_address?->lat)
                                                @php($mapLng = $booking?->service_address?->lon)
                                                <h5 class="mb-1">{{ translate('Service Location') }}:</h5>
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <p class="mb-0">{{ Str::limit($booking?->service_address?->address ?? translate('not_available'), 100) }}
                                                    </p>
                                                    @if($mapLat && $mapLng)
                                                        <a href="https://www.google.com/maps?q={{ $mapLat }},{{ $mapLng }}"
                                                           target="_blank" rel="noopener noreferrer"
                                                           class="btn btn-sm btn--primary text-nowrap d-inline-flex align-items-center gap-1">
                                                            <span class="material-icons" style="font-size:16px">map</span>
                                                            {{ translate('View_on_Map') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="c1-light-bg radius-10">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{ translate('Customer_Information') }}
                                        </h4>

                                        <div class="btn-group">
                                            @if (in_array($booking->booking_status, ['completed', 'cancelled']))
                                                @if (!$booking?->is_guest && $booking?->customer)
                                                    <div class="d-flex align-items-center gap-2 cursor-pointer customer-chat">
                                                        <span class="material-symbols-outlined">chat</span>
                                                        <form action="{{ route('admin.chat.create-channel') }}" method="post"
                                                            id="chatForm-{{ $booking->id }}">
                                                            @csrf
                                                            <input type="hidden" name="customer_id"
                                                                value="{{ $booking?->customer?->id }}">
                                                            <input type="hidden" name="type" value="booking">
                                                            <input type="hidden" name="user_type" value="customer">
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="material-symbols-outlined">more_vert</span>
                                                </div>
                                                <ul
                                                    class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
                                                    @can('booking_edit')
                                                        @if(!empty($booking['service_address_id']))
                                                        <li data-bs-toggle="modal"
                                                            data-bs-target="#serviceAddressModal--{{ $booking['id'] }}"
                                                            data-toggle="tooltip" data-placement="top">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="material-symbols-outlined">edit_square</span>
                                                                {{ translate('Edit_Details') }}
                                                            </div>
                                                        </li>
                                                        @endif
                                                    @endcan
                                                    @if (!$booking?->is_guest && $booking?->customer)
                                                        <li>
                                                            <div
                                                                class="d-flex align-items-center gap-2 cursor-pointer customer-chat">
                                                                <span class="material-symbols-outlined">chat</span>
                                                                {{ translate('chat_with_Customer') }}
                                                                <form action="{{ route('admin.chat.create-channel') }}"
                                                                    method="post" id="chatForm-{{ $booking->id }}">
                                                                    @csrf
                                                                    <input type="hidden" name="customer_id"
                                                                        value="{{ $booking?->customer?->id }}">
                                                                    <input type="hidden" name="type" value="booking">
                                                                    <input type="hidden" name="user_type" value="customer">
                                                                </form>
                                                            </div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="py-3 px-4">
                                        @php($customer_name = $booking?->service_address?->contact_person_name ?? $booking?->customer?->first_name . ' ' . $booking?->customer?->last_name)
                                        @php($customer_phone = $booking?->service_address?->contact_person_number ?? $booking?->customer?->phone)
                                        <div class="media gap-2 flex-wrap">
                                            @if (!$booking?->is_guest && $booking?->customer)
                                                <img width="58" height="58"
                                                    class="rounded-circle border border-white aspect-square object-fit-cover"
                                                    src="{{ $booking?->customer?->profile_image_full_path }}"
                                                    alt="{{ translate('user_image') }}">
                                            @else
                                                <img width="58" height="58"
                                                    class="rounded-circle border border-white aspect-square object-fit-cover"
                                                    src="{{ asset('public/assets/provider-module/img/user2x.png') }}"
                                                    alt="{{ translate('user_image') }}">
                                            @endif

                                            <div class="media-body">
                                                <h5 class="c1 mb-3">
                                                    @if (!$booking?->is_guest && $booking?->customer)
                                                        <a href="{{ route('admin.customer.detail', [$booking?->customer?->id, 'web_page' => 'overview']) }}"
                                                            class="c1">{{ Str::limit($customer_name ?? translate('No customer found'), 30) }}</a>
                                                    @else
                                                        <span>{{ Str::limit($customer_name ?? translate('No customer found'), 30) }}</span>
                                                    @endif
                                                </h5>
                                                <ul class="list-info">
                                                    @if ($customer_phone)
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a href="tel:{{ $customer_phone }}">{{ $customer_phone }}</a>
                                                        </li>
                                                    @endif
                                                    @if(!empty($booking?->service_address?->address))
                                                        <li>
                                                            <span class="material-icons">map</span>
                                                            <p>{{ Str::limit($booking?->service_address?->address ?? translate('not_available'), 100) }}
                                                            </p>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="c1-light-bg radius-10 provider-information">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{ translate('Provider_Information') }}
                                        </h4>
                                        @if (isset($booking->provider))
                                            <div class="btn-group">
                                                <div class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="material-symbols-outlined">more_vert</span>
                                                </div>
                                                <ul
                                                    class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
                                                    <li>
                                                        <div
                                                            class="d-flex align-items-center gap-2 cursor-pointer provider-chat">
                                                            <span class="material-symbols-outlined">chat</span>
                                                            {{ translate('chat_with_Provider') }}
                                                            <form action="{{ route('admin.chat.create-channel') }}"
                                                                method="post" id="chatForm-{{ $booking->id }}">
                                                                @csrf
                                                                <input type="hidden" name="provider_id"
                                                                    value="{{ $booking?->provider?->owner?->id }}">
                                                                <input type="hidden" name="type" value="booking">
                                                                <input type="hidden" name="user_type"
                                                                    value="provider-admin">
                                                            </form>
                                                        </div>
                                                    </li>
                                                    @if (in_array($booking->booking_status, ['ongoing', 'accepted']))
                                                        @can('booking_can_manage_status')
                                                            <li>
                                                                <div class="d-flex align-items-center gap-2"
                                                                    data-bs-target="#providerModal" data-bs-toggle="modal">
                                                                    <span class="material-symbols-outlined">manage_history</span>
                                                                    {{ translate('change_Provider') }}
                                                                </div>
                                                            </li>
                                                        @endcan
                                                    @endif
                                                    <li>
                                                        <a class="d-flex align-items-center gap-2 cursor-pointer p-0"
                                                            href="{{ route('admin.provider.details', [$booking?->provider?->id, 'web_page' => 'overview']) }}">
                                                            <span class="material-icons">person</span>
                                                            {{ translate('View_Details') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    @if(isset($booking->provider))
                                        <div class="py-3 px-4">
                                            <div class="media gap-2 flex-wrap">
                                                <img width="58" height="58"
                                                    class="rounded-circle border border-white aspect-square object-fit-cover"
                                                    src="{{ $booking?->provider?->logo_full_path }}"
                                                    alt="{{ translate('provider') }}">
                                                <div class="media-body">
                                                    <a
                                                        href="{{ route('admin.provider.details', [$booking?->provider?->id, 'web_page' => 'overview']) }}">
                                                        <h5 class="c1 mb-3">
                                                            {{ Str::limit($booking->provider->company_name ?? translate('No provider found'), 30) }}
                                                        </h5>
                                                    </a>
                                                    <ul class="list-info">
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a
                                                                href="tel:{{ $booking->provider->contact_person_phone ?? '' }}">{{ $booking->provider->contact_person_phone ?? '' }}</a>
                                                        </li>
                                                        <li>
                                                            <span class="material-icons">map</span>
                                                            <p>{{ Str::limit($booking->provider->company_address ?? '', 100) }}
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                            <span class="material-icons text-muted fs-2">account_circle</span>
                                            <p class="text-muted text-center fw-medium mb-3">
                                                {{ translate('No Provider Information') }}
                                            </p>
                                        </div>
                                        <div class="text-center pb-4">
                                                @if(count($providers) < 1)
                                                    <button class="btn btn--primary"
                                                        disabled>{{ translate('no provider available') }}</button>
                                                @else
                                                    <button class="btn btn--primary" data-bs-target="#providerModal"
                                                        data-bs-toggle="modal">{{ translate('assign provider') }}</button>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <div class="c1-light-bg radius-10">
                                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2 mb-0">
                                            <span class="material-icons title-color">groups</span>
                                            {{ translate('Team_Assignment') }}
                                        </h4>
                                    </div>
                                    <div class="py-3 px-4">
                                        @php($canReassignCrew = bookingCanReassignCrew($booking))
                                        @if(isset($booking->team))
                                            <h5 class="c1 mb-2">{{ $booking->team->name }}</h5>
                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @foreach($booking->team->servicemen as $member)
                                                    <span class="badge badge-info">
                                                        {{ $member->user?->first_name }} {{ $member->user?->last_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-3">{{ translate('No team assigned. Assign a supervisor first, then a team.') }}</p>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label mb-1">{{ translate('Supervisor_Seen') }}</label>
                                            @if($booking->supervisor_seen_at)
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <span class="badge bg-success">{{ translate('Seen') }}</span>
                                                    <span class="text-muted">
                                                        {{ $booking->supervisor_seen_at->format('Y-m-d H:i') }}
                                                        @if($booking->supervisorSeenBy)
                                                            — {{ $booking->supervisorSeenBy->first_name }} {{ $booking->supervisorSeenBy->last_name }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <span class="badge bg-warning text-dark">{{ translate('Not_Seen_Yet') }}</span>
                                            @endif
                                        </div>

                                        @if($booking->provider_id && isset($teams) && $teams->count())
                                            @if($canReassignCrew)
                                                <div class="d-flex flex-wrap gap-2 align-items-end mb-4">
                                                    <div class="flex-grow-1">
                                                        <label class="form-label">{{ translate('Assign_Team') }}</label>
                                                        <select id="admin_team_assign_select" class="theme-input-style w-100">
                                                            <option value="">{{ translate('Select_Team') }}</option>
                                                            @foreach($teams as $team)
                                                                <option value="{{ $team->id }}" {{ $booking->team_id == $team->id ? 'selected' : '' }}>
                                                                    {{ $team->name }} ({{ $team->servicemen->count() }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <button type="button" id="admin-assign-team-btn" class="btn btn--primary">
                                                        {{ translate('Assign') }}
                                                    </button>
                                                </div>
                                            @else
                                                <p class="text-muted small mb-0">
                                                    {{ translate('Team cannot be changed after the booking is on the way') }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="c1-light-bg radius-10 serviceman-information">
                                    <div
                                        class="border-bottom d-flex align-items-center justify-content-between gap-2 py-3 px-4 mb-2">
                                        <h4 class="d-flex align-items-center gap-2">
                                            <span class="material-icons title-color">person</span>
                                            {{ translate('Serviceman_Information') }}
                                        </h4>
                                        @if (isset($booking->serviceman))
                                            <div class="btn-group">
                                                @if (in_array($booking->booking_status, ['ongoing', 'accepted']))

                                                    <div class="cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="material-symbols-outlined">more_vert</span>
                                                    </div>
                                                    <ul
                                                        class="dropdown-menu dropdown-menu__custom border-none dropdown-menu-end">
                                                        <li>
                                                            <div
                                                                class="d-flex align-items-center gap-2 cursor-pointer provider-chat">
                                                                <span class="material-symbols-outlined">chat</span>
                                                                {{ translate('chat_with_Serviceman') }}
                                                                <form action="{{ route('admin.chat.create-channel') }}"
                                                                    method="post" id="chatForm-{{ $booking->id }}">
                                                                    @csrf
                                                                    <input type="hidden" name="serviceman_id"
                                                                        value="{{ $booking?->serviceman?->user?->id }}">
                                                                    <input type="hidden" name="type" value="booking">
                                                                    <input type="hidden" name="user_type"
                                                                        value="provider-serviceman">
                                                                </form>
                                                            </div>
                                                        </li>
                                                        @can('booking_can_manage_status')
                                                            @if($canReassignCrew)
                                                                <li>
                                                                    <div class="d-flex align-items-center gap-2 cursor-pointer"
                                                                        data-bs-target="#servicemanModal" data-bs-toggle="modal">
                                                                        <span class="material-symbols-outlined">manage_history</span>
                                                                        {{ translate('change serviceman') }}
                                                                    </div>
                                                                </li>
                                                            @endif
                                                        @endcan
                                                    </ul>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    @if (isset($booking->serviceman))
                                        <div class="py-3 px-4">
                                            <div class="media gap-2 flex-wrap">
                                                <img width="58" height="58"
                                                    class="rounded-circle border border-white aspect-square object-fit-cover"
                                                    src="{{ $booking?->serviceman?->user?->profile_image_full_path }}"
                                                    alt="{{ translate('serviceman') }}">
                                                <div class="media-body">
                                                    <h5 class="c1 mb-3">
                                                        {{ Str::limit($booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->first_name . ' ' . $booking->serviceman->user->last_name : translate('No serviceman found'), 30) }}
                                                    </h5>
                                                    <ul class="list-info">
                                                        <li>
                                                            <span class="material-icons">phone_iphone</span>
                                                            <a
                                                                href="tel:{{ $booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone : '' }}">
                                                                {{ $booking->serviceman && $booking->serviceman->user ? $booking->serviceman->user->phone : '' }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            @can('booking_can_manage_status')
                                                @if($canReassignCrew && isset($booking->provider))
                                                    <div class="mt-3">
                                                        <button type="button" class="btn btn--primary" data-bs-target="#servicemanModal"
                                                            data-bs-toggle="modal">
                                                            {{ translate('change serviceman') }}
                                                        </button>
                                                    </div>
                                                @elseif(!$canReassignCrew)
                                                    <p class="text-muted small mt-3 mb-0">
                                                        {{ translate('Serviceman cannot be changed after the booking is on the way') }}
                                                    </p>
                                                @endif
                                            @endcan
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-2 mt-30 align-items-center">
                                            <span class="material-icons text-muted fs-2">account_circle</span>
                                            <p class="text-muted text-center fw-medium mb-3">
                                                {{ translate('No Serviceman Information') }}
                                            </p>
                                        </div>

                                        <div class="text-center pb-4">
                                            @can('booking_can_manage_status')
                                                <button type="button" class="btn btn--primary" data-bs-target="#servicemanModal"
                                                    data-bs-toggle="modal"
                                                    @if(!$canReassignCrew || !isset($booking->provider)) disabled @endif>
                                                    {{ translate('assign Serviceman') }}
                                                </button>
                                                @if(!$canReassignCrew)
                                                    <p class="text-muted small mt-2 mb-0">
                                                        {{ translate('Serviceman cannot be changed after the booking is on the way') }}
                                                    </p>
                                                @endif
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('bookingmodule::admin.booking.partials.details._update-customer-address-modal')
    @include('bookingmodule::admin.booking.partials.details._service-address-modal')

    @include('bookingmodule::admin.booking.partials.details._service-location-modal')


    @include('bookingmodule::admin.booking.partials.details._service-modal')

    <div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-data" id="modal-data-info">
                @include('bookingmodule::admin.booking.partials.details.provider-info-modal-data')
            </div>
        </div>
    </div>
    <div class="modal fade" id="servicemanModal" tabindex="-1" aria-labelledby="servicemanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-data1" id="modal-data-info1">
                @include('bookingmodule::admin.booking.partials.details.serviceman-info-modal-data')
            </div>
        </div>
    </div>

    @endsection

    @push('script')
        <script>
            "use strict";

            $('.reassign-provider').on('click', function () {
                let id = $(this).data('provider-reassign');
                updateProvider(id)
            })

            $('.reassign-serviceman').on('click', function () {
                let id = $(this).data('serviceman-reassign');
                updateServiceman(id)
            })

            @if ($booking->booking_status == 'pending')
                $(document).ready(function () {
                    selectElementVisibility('serviceman_assign', false);
                });
            @endif

            $("#booking_status").change(function () {
                var booking_status = $("#booking_status option:selected").val();
                if (parseInt(booking_status) !== 0) {
                    var route = '{{ route('admin.booking.status_update', [$booking->id]) }}' + '?booking_status=' + booking_status;
                    update_booking_details(route, '{{ translate('want_to_update_status') }}', 'booking_status',
                        booking_status);
                } else {
                    toastr.error('{{ translate('choose_proper_status') }}');
                }
            });

            $(".change-booking-status").on('click', function () {
                var booking_status = 'canceled';
                var route = '{{ route('admin.booking.status_update', [$booking->id]) }}' + '?booking_status=' + booking_status;
                update_booking_details(route, '{{ translate('want_to_cancel_booking_status') }}', 'booking_status', booking_status);
            });

            $("#serviceman_assign").change(function () {
                var serviceman_id = $("#serviceman_assign option:selected").val();
                if (serviceman_id !== 'no_serviceman') {
                    var route = '{{ route('admin.booking.serviceman_update', [$booking->id]) }}' + '?serviceman_id=' +
                        serviceman_id;

                    update_booking_details(route, '{{ translate('want_to_assign_the_serviceman') }}?',
                        'serviceman_assign', serviceman_id);
                } else {
                    toastr.error('{{ translate('choose_proper_serviceman') }}');
                }
            });

            function service_schedule_update() {
                var $input = $("#service_schedule");
                var service_schedule = $input.val();
                var original = $input.data('original');

                if (!service_schedule) {
                    $input.val(original);
                    return;
                }

                // Normalize formats (replace space with 'T' for parsing)
                var newDate = new Date(service_schedule);
                var originalDate = new Date(original.replace(" ", "T"));
                var now = new Date();

                // Compare with current time
                if (newDate < now) {
                    toastr.error("Reschedule cannot be earlier than the current time");
                    $input.val(original);
                    return;
                }

                // Compare with original schedule
                if (newDate < originalDate) {
                    toastr.error("Reschedule cannot be earlier than the original schedule");
                    $input.val(original);
                    return;
                }

                var route = '{{ route('admin.booking.schedule_update', [$booking->id]) }}' + '?service_schedule=' + service_schedule;

                update_booking_details(route, '{{ translate('want_to_update_the_booking_schedule') }}', 'service_schedule', service_schedule);
            }

            $(".switch-to-cash-after-service").on('click', function () {
                var payment_method = 'cash_after_service';
                var route = '{{ route('admin.booking.switch-payment-method', [$booking->id]) }}' + '?payment_method=' + payment_method;
                update_booking_details(route, '{{ translate('want_to_switch_payment_method_to_cash_after_service') }}', 'payment_method', payment_method);
            });

            function update_booking_details(route, message, componentId, updatedValue) {
                Swal.fire({
                    title: "{{ translate('are_you_sure') }}?",
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'var(--bs-secondary)',
                    confirmButtonColor: 'var(--bs-primary)',
                    cancelButtonText: '{{ translate('Cancel') }}',
                    confirmButtonText: '{{ translate('Yes') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.get({
                            url: route,
                            dataType: 'json',
                            data: {},
                            beforeSend: function () { },
                            success: function (data) {
                                update_component(componentId, updatedValue);
                                toastr.success(data.message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });

                                if (componentId === 'booking_status' ||
                                    componentId === 'service_schedule' || componentId === 'serviceman_assign' || componentId === 'payment_method') {
                                    location.reload();
                                }
                            },
                            complete: function () { },
                        });
                    }
                })
            }

            function update_component(componentId, updatedValue) {

                if (componentId === 'booking_status') {
                    $("#booking_status__span").html(updatedValue);

                    selectElementVisibility('serviceman_assign', true);

                }
            }

            function selectElementVisibility(componentId, visibility) {
                if (visibility === true) {
                    $('#' + componentId).next(".select2-container").show();
                } else if (visibility === false) {
                    $('#' + componentId).next(".select2-container").hide();
                } else { }
            }
        </script>

        <script>
            $(document).ready(function () {
                $('#category_selector__select').select2({
                    dropdownParent: "#serviceUpdateModal--{{ $booking['id'] }}"
                });
                $('#sub_category_selector__select').select2({
                    dropdownParent: "#serviceUpdateModal--{{ $booking['id'] }}"
                });
                $('#service_selector__select').select2({
                    dropdownParent: "#serviceUpdateModal--{{ $booking['id'] }}"
                });
                $('#service_variation_selector__select').select2({
                    dropdownParent: "#serviceUpdateModal--{{ $booking['id'] }}"
                });
            });

            $("#service_selector__select").on('change', function () {
                $("#service_variation_selector__select").html(
                    '<option value="" selected disabled>{{ translate('Select Service Variant') }}</option>');

                const serviceId = this.value;
                const route = '{{ route('admin.booking.service.ajax-get-variant') }}' + '?service_id=' + serviceId +
                    '&zone_id=' + "{{ $booking->zone_id }}";

                $.get({
                    url: route,
                    dataType: 'json',
                    data: {},
                    beforeSend: function () {
                        $('.preloader').show();
                    },
                    success: function (response) {
                        var selectString =
                            '<option value="" selected disabled>{{ translate('Select Service Variant') }}</option>';
                        response.content.forEach((item) => {
                            selectString +=
                                `<option value="${item.variant_key}">${item.variant}</option>`;
                        });
                        $("#service_variation_selector__select").html(selectString)
                    },
                    complete: function () {
                        $('.preloader').hide();
                    },
                    error: function () {
                        toastr.error('{{ translate('Failed to load') }}')
                    }
                });
            })

            $("#serviceUpdateModal--{{ $booking['id'] }}").on('hidden.bs.modal', function () {
                $('#service_selector__select').prop('selectedIndex', 0);
                $("#service_variation_selector__select").html(
                    '<option value="" selected disabled>{{ translate('Select Service Variant') }}</option>');
                $("#service_quantity").val('');
            });

            $("#add-service").on('click', function () {
                const service_id = $("[name='service_id']").val();
                const variant_key = $("[name='variant_key']").val();
                const quantity = parseInt($("[name='service_quantity']").val());
                const zone_id = '{{ $booking->zone_id }}';


                if (service_id === '' || service_id === null) {
                    toastr.error('{{ translate('Select a service') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return;
                } else if (variant_key === '' || variant_key === null) {
                    toastr.error('{{ translate('Select a variation') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return;
                } else if (quantity < 1) {
                    toastr.error('{{ translate('Quantity must not be empty') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return;
                }

                let variant_key_array = [];
                $('input[name="variant_keys[]"]').each(function () {
                    variant_key_array.push($(this).val());
                });

                if (variant_key_array.includes(variant_key)) {
                    const decimal_point = parseInt(
                        '{{ business_config('currency_decimal_point', 'business_information')->live_values ?? 2 }}'
                    );

                    const old_qty = parseInt($(`#qty-${variant_key}`).val());
                    const updated_qty = old_qty + quantity;

                    const old_total_cost = parseFloat($(`#total-cost-${variant_key}`).text());
                    const updated_total_cost = ((old_total_cost * updated_qty) / old_qty).toFixed(decimal_point);

                    const discountAmountCell = $(`#discount-amount-${variant_key}`);
                    const old_discount_amount = parseFloat(
                        discountAmountCell.find('.discount-amount-value').text() ||
                        discountAmountCell.clone().children().remove().end().text()
                    );
                    const updated_discount_amount = ((old_discount_amount * updated_qty) / old_qty).toFixed(
                        decimal_point);


                    $(`#qty-${variant_key}`).val(updated_qty);
                    $(`#total-cost-${variant_key}`).text(updated_total_cost);
                    const discountAmountValue = discountAmountCell.find('.discount-amount-value');
                    if (discountAmountValue.length) {
                        discountAmountValue.text(updated_discount_amount);
                    } else {
                        const firstTextNode = discountAmountCell.contents().filter(function () {
                            return this.nodeType === 3 && this.textContent.trim() !== '';
                        }).first();

                        if (firstTextNode.length) {
                            firstTextNode[0].textContent = updated_discount_amount;
                        } else {
                            discountAmountCell.text(updated_discount_amount);
                        }
                    }

                    toastr.success('{{ translate('Added successfully') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return;
                }

                let query_string = 'service_id=' + service_id + '&variant_key=' + variant_key + '&quantity=' +
                    quantity + '&zone_id=' + zone_id;
                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.booking.service.ajax-get-service-info') }}" + '?' + query_string,
                    data: {},
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $('.preloader').show();
                    },
                    success: function (response) {
                        $("#service-edit-tbody").append(response.view);
                        toastr.success('{{ translate('Added successfully') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    complete: function () {
                        $('.preloader').hide();
                    },
                });
            })

            $(".remove-service-row").on('click', function () {
                let row = $(this).data('row');
                removeServiceRow(row)
            })

            function removeServiceRow(row) {
                const row_count = $('#service-edit-tbody tr').length;
                if (row_count <= 1) {
                    toastr.error('{{ translate('Can not remove the only service') }}');
                    return;
                }

                Swal.fire({
                    title: "{{ translate('are_you_sure') }}?",
                    text: '{{ translate('want to remove the service from the booking') }}',
                    type: 'warning',
                    showCloseButton: true,
                    showCancelButton: true,
                    cancelButtonColor: 'var(--bs-secondary)',
                    confirmButtonColor: 'var(--bs-primary)',
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $(`#${row}`).remove();
                    }
                })
            }
        </script>


        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ business_config('google_map', 'third_party')?->live_values['map_api_key_client'] }}&libraries=places,geometry&v=3.45.8">
            </script>
        <script src="{{asset('public/assets/admin-module/js/map/map-picker.js')}}"></script>
        <script>
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#viewer').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#customFileEg1").change(function () {
                readURL(this);
            });


            $('.__right-eye').on('click', function () {
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active')
                    $(this).find('i').removeClass('tio-invisible')
                    $(this).find('i').addClass('tio-hidden-outlined')
                    $(this).siblings('input').attr('type', 'password')
                } else {
                    $(this).addClass('active')
                    $(this).siblings('input').attr('type', 'text')


                    $(this).find('i').addClass('tio-invisible')
                    $(this).find('i').removeClass('tio-hidden-outlined')
                }
            })
        </script>

        <script>
            $(document).ready(function () {

                $(document).on('click', '.sort-by-class', function () {
                    console.log('hi')
                    const route = '{{ url('admin/provider/available-provider') }}'
                    var sortOption = document.querySelector('input[name="sort"]:checked').value;
                    var bookingId = "{{ $booking->id }}"

                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {
                            sort_by: sortOption,
                            booking_id: bookingId
                        },
                        beforeSend: function () {

                        },
                        success: function (response) {
                            $('.modal-content-data').html(response.view);
                        },
                        complete: function () { },
                        error: function () {
                            toastr.error('{{ translate('Failed to load') }}')
                        }
                    });
                })
            });

            $(document).ready(function () {
                $(document).on('keyup', '.search-form-input', function () {
                    const route = '{{ url('admin/provider/available-provider') }}';
                    let sortOption = document.querySelector('input[name="sort"]:checked').value;
                    let bookingId = "{{ $booking->id }}";
                    let searchTerm = $('.search-form-input').val();

                    $.get({
                        url: route,
                        dataType: 'json',
                        data: {
                            sort_by: sortOption,
                            booking_id: bookingId,
                            search: searchTerm,
                        },
                        beforeSend: function () { },
                        success: function (response) {
                            $('.modal-content-data').html(response.view);


                            var cursorPosition = searchTerm.lastIndexOf(searchTerm.charAt(searchTerm
                                .length - 1)) + 1;
                            $('.search-form-input').focus().get(0).setSelectionRange(cursorPosition,
                                cursorPosition);
                        },
                        complete: function () { },
                        error: function () {
                            toastr.error('{{ translate('Failed to load') }}');
                        }
                    });
                });
            });

            function updateProvider(providerId) {
                const bookingId = "{{ $booking->id }}";
                const route = '{{ url('admin/provider/reassign-provider') }}' + '/' + bookingId;
                const sortOption = document.querySelector('input[name="sort"]:checked').value;
                const searchTerm = $('.search-form-input').val();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: route,
                    type: 'PUT',
                    dataType: 'json',
                    data: {
                        sort_by: sortOption,
                        booking_id: bookingId,
                        search: searchTerm,
                        provider_id: providerId
                    },
                    beforeSend: function () {
                        toastr.info('{{ translate('Processing request...') }}');
                    },
                    success: function (response) {
                        $('.modal-content-data').html(response.view);
                        toastr.success('{{ translate('Successfully reassign provider') }}');
                        setTimeout(function () {
                            location.reload()
                        }, 600);
                    },
                    complete: function () { },
                    error: function () {
                        toastr.error('{{ translate('Failed to load') }}');
                    }
                });
            }

            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $(document).on('keyup', '.search-form-input1', function () {
                    const route = '{{ url('admin/booking/serviceman-update', $booking->id) }}';
                    let searchTerm = $('.search-form-input1').val();

                    $.ajax({
                        url: route,
                        type: 'PUT',
                        dataType: 'json',
                        data: {
                            booking_id: "{{ $booking->id }}",
                            search: searchTerm,
                        },
                        beforeSend: function () { },
                        success: function (response) {
                            $('.modal-content-data1').html(response.view);
                        },
                        complete: function () { },
                        error: function (xhr) {
                            if (xhr.status === 419) {
                                toastr.error('{{ translate('Session expired, please refresh the page.') }}');
                            } else {
                                toastr.error('{{ translate('Failed to load') }}');
                            }
                        }
                    });
                });
            });


            function updateServiceman(servicemanId) {
                const bookingId = "{{ $booking->id }}";
                const route = '{{ url('admin/booking/serviceman-update') }}' + '/' + bookingId;
                const searchTerm = $('.search-form-input1').val();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: route,
                    type: 'PUT',
                    dataType: 'json',
                    data: {
                        booking_id: bookingId,
                        search: searchTerm,
                        serviceman_id: servicemanId
                    },
                    beforeSend: function () {
                        toastr.info('{{ translate('Processing request...') }}');
                    },
                    success: function (response) {
                        $('.modal-content-data').html(response.view);
                        toastr.success('{{ translate('Successfully reassign provider') }}');
                        setTimeout(function () {
                            location.reload()
                        }, 600);
                    },
                    complete: function () { },
                    error: function () {
                        toastr.error('{{ translate('Failed to load') }}');
                    }
                });
            }

            $('.customer-chat').on('click', function () {
                $(this).find('form').submit();
            });

            $('.provider-chat').on('click', function () {
                $(this).find('form').submit();
            });

            $(document).ready(function () {
                // Get booking ID dynamically
                var bookingId = "{{ $booking['id'] }}";

                function toggleServiceLocation() {
                    if ($('#customer_location').is(':checked')) {
                        $('.customer-details').show();
                        $('.provider-details').hide();
                    } else {
                        $('.customer-details').hide();
                        $('.provider-details').show();
                    }
                }

                // Run toggle function on radio button change
                $('input[name="service_location"]').on('change', function () {
                    toggleServiceLocation();
                });

                // Run toggle function when the modal is opened
                $('#serviceLocationModal--' + bookingId).on('shown.bs.modal', function () {
                    toggleServiceLocation();
                });

                // When the address modal opens, hide the first modal
                $('#customerAddressModal--' + bookingId).on('show.bs.modal', function () {
                    $('#serviceLocationModal--' + bookingId).modal('hide'); // Hide the first modal
                });

                // When the address modal closes, reopen the service location modal and update the address
                $('#customerAddressModal--' + bookingId).on('hidden.bs.modal', function () {
                    $('#serviceLocationModal--' + bookingId).modal('show'); // Show the first modal again
                });
            });

            $(document).ready(function () {
                $("#customerAddressModalSubmit").on("submit", function (e) {
                    e.preventDefault(); // Prevent form submission

                    var bookingId = "{{ $booking['id'] }}";

                    let customerAddressModal = $("#customerAddressModal--" + bookingId);
                    let serviceLocationModal = $("#serviceLocationModal--" + bookingId);

                    // Copy updated data from customerAddressModal inputs
                    let contactPersonName = customerAddressModal.find("input[name='contact_person_name']").val();
                    let contactPersonNumber = customerAddressModal.find("input[name='contact_person_number']").val();
                    let addressLabel = customerAddressModal.find("select[name='address_label']").val();
                    let address = customerAddressModal.find("input[name='address']").val();
                    let latitude = customerAddressModal.find("input[name='latitude']").val();
                    let longitude = customerAddressModal.find("input[name='longitude']").val();
                    let city = customerAddressModal.find("input[name='city']").val();
                    let street = customerAddressModal.find("input[name='street']").val();
                    let zipCode = customerAddressModal.find("input[name='zip_code']").val();
                    let country = customerAddressModal.find("input[name='country']").val();

                    // Update the corresponding hidden inputs in serviceLocationModal
                    serviceLocationModal.find("input[name='contact_person_name']").val(contactPersonName);
                    serviceLocationModal.find("input[name='contact_person_number']").val(contactPersonNumber);
                    serviceLocationModal.find("input[name='address_label']").val(addressLabel);
                    serviceLocationModal.find("input[name='address']").val(address);
                    serviceLocationModal.find("input[name='latitude']").val(latitude);
                    serviceLocationModal.find("input[name='longitude']").val(longitude);
                    serviceLocationModal.find("input[name='city']").val(city);
                    serviceLocationModal.find("input[name='street']").val(street);
                    serviceLocationModal.find("input[name='zip_code']").val(zipCode);
                    serviceLocationModal.find("input[name='country']").val(country);

                    $('.updated_customer_name').text(contactPersonName); // Update the customer name
                    $('#updated_customer_phone').text(contactPersonNumber); // Update the customer
                    $('#customer_service_location').removeClass('text-danger'); // Update the customer service location
                    $('#customer_service_location').text(address); // Update the customer service location
                    $('.customer-address-update-btn').removeAttr('disabled'); // Update the customer service location update button

                    // Close the customerAddressModal
                    customerAddressModal.modal("hide");

                    // Open the serviceLocationModal to show updated data
                    serviceLocationModal.modal("show");
                });
            });

            $(".customer-address-reset-btn").on("click", function (e) {
                e.preventDefault(); // prevent default behavior

                // Reset the form (visible inputs)
                $("#customerAddressModalSubmit")[0].reset();

                // Restore hidden inputs to original values from server
                $("input[name='contact_person_name']").val("{{ $booking->service_address->contact_person_name ?? '' }}");
                $("input[name='contact_person_number']").val("{{ $booking->service_address->contact_person_number ?? '' }}");
                $("input[name='address_label']").val("{{ $booking->service_address->label ?? '' }}");
                $("input[name='address']").val("{{ $booking->service_address->address ?? '' }}");
                $("input[name='latitude']").val("{{ $booking->service_address->latitude ?? '' }}");
                $("input[name='longitude']").val("{{ $booking->service_address->longitude ?? '' }}");
                $("input[name='city']").val("{{ $booking->service_address->city ?? '' }}");
                $("input[name='street']").val("{{ $booking->service_address->street ?? '' }}");
                $("input[name='zip_code']").val("{{ $booking->service_address->zip_code ?? '' }}");
                $("input[name='country']").val("{{ $booking->service_address->country ?? '' }}");

                // Update the UI
                let name = "{{ $customer_name }}";
                let phone = "{{ $customer_phone }}";
                let customerAddress = "{{ $booking?->service_address?->address }}";

                $('.updated_customer_name').text(name); // Update the customer name
                $('#updated_customer_phone').text(phone); // Update the customer phone

                if (customerAddress) {
                    $('#customer_service_location').text(customerAddress);
                    $('#customer_service_location').removeClass('text-danger');
                    $('.customer-address-update-btn').removeAttr('disabled');
                } else {
                    $('#customer_service_location').text("No address found");
                    $('#customer_service_location').addClass('text-danger');
                    $('.customer-address-update-btn').attr('disabled', true);
                }
            });


        </script>
        <script>
            $(document).ready(function () {
                $('.without-search').select2({
                    minimumResultsForSearch: Infinity
                });
            });

        </script>
        <script>
            $('#admin-assign-team-btn').on('click', function () {
                const teamId = $('#admin_team_assign_select').val();
                if (!teamId) {
                    toastr.error('{{ translate('Select_Team') }}');
                    return;
                }
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });
                $.ajax({
                    url: '{{ route('admin.team.assign-booking', $booking->id) }}',
                    type: 'PUT',
                    dataType: 'json',
                    data: { team_id: teamId },
                    success: function () {
                        toastr.success('{{ translate('Team assigned to booking') }}');
                        setTimeout(function () { location.reload(); }, 500);
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || '{{ translate('Failed to assign team') }}');
                    }
                });
            });
        </script>
    @endpush
