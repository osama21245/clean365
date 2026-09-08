@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@push('css_or_js')


@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            @include('providermanagement::admin.provider.detail._tabs')

            <div class="tab-content">
                <div class="tab-pane fade show active" id="boookings-tab-pane">
                    <div class="d-flex justify-content-end border-bottom mb-10">
                        <div class="d-flex gap-2 fw-medium pe--4">
                            <span class="opacity-75">{{translate('Total_Bookings')}}:</span>
                            <span class="title-color">{{$bookings->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?web_page=bookings"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search??''}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('Booking_ID')}}</th>
                                        <th>{{translate('Customer_Info')}}</th>
                                        <th>{{translate('Total_Amount')}}</th>
                                        <th>{{translate('Schedule_Date')}}</th>
                                        <th>{{translate('Booking_Date')}}</th>
                                        <th>{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($bookings as $key=>$booking)
                                        <tr>
                                            <td>
                                                <a href="{{route('admin.booking.details', [$booking->id,'web_page'=>'details'])}}">
                                                    {{$booking->readable_id}}</a>
                                            </td>
                                            <td>
                                                @if(isset($booking->customer))
                                                    <div>
                                                        <a href="{{route('admin.customer.detail',[$booking->customer->id, 'web_page'=>'overview'])}}">
                                                            {{Str::limit($booking->customer->first_name, 30)}}
                                                        </a>
                                                    </div>
                                                    <a href="tel:{{$booking->customer->phone ?? ''}}">
                                                        {{$booking->customer->phone ?? ''}}
                                                    </a>
                                                @else
                                                    <span class="opacity-50">{{translate('Customer_not_available')}}</span>
                                                @endif
                                            </td>
                                            <td>{{$booking->total_booking_amount}}</td>
                                            <td>{{ format_time_by_business_settings($booking->service_schedule, 'd-M-Y') }}</td>
                                            <td>{{ format_time_by_business_settings($booking->created_at, 'd-M-Y') }}</td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="{{route('admin.booking.details', [$booking->id,'web_page'=>'details'])}}"
                                                       type="button"
                                                       class="table-actions_view btn btn--light-primary fw-medium text-capitalize fz-14">
                                                        <span class="material-icons">visibility</span>
                                                        {{translate('View_Details')}}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => filled($search ?? null) ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $bookings->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
