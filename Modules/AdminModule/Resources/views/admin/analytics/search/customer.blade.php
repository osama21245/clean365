@extends('adminmodule::layouts.master')

@section('title',translate('Customer_Search_Analytics'))

@push('css_or_js')

@endpush

@section('content')
        <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Customer_Search_Analytics')}}</h2>
            </div>
            @php
                $hasTopCustomerData = count($graph_data['search_volume']) > 0 && count($graph_data['top_customers']) > 0;
                $topServicesWithService = collect($topServices)->filter(fn($item) => $item->service);
                $hasTopServicesData = $total > 0 && $topServicesWithService->isNotEmpty();
                $analyticsDateRangeOptions = [
                    'all_time'               => translate('All Time'),
                    'this_week'              => translate('This Week'),
                    'last_week'              => translate('Last Week'),
                    'this_month'             => translate('This Month'),
                    'last_month'             => translate('Last Month'),
                    'last_15_days'           => translate('Last 15 Days'),
                    'this_year'              => translate('This Year'),
                    'last_year'              => translate('Last Year'),
                    'last_6_month'           => translate('Last 6 Months'),
                    'this_year_1st_quarter'  => translate('This Year 1st Quarter'),
                    'this_year_2nd_quarter'  => translate('This Year 2nd Quarter'),
                    'this_year_3rd_quarter'  => translate('This Year 3rd Quarter'),
                    'this_year_4th_quarter'  => translate('This Year 4th Quarter')];
            @endphp

            <div class="row gy-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                                <div class="">
                                    <h4 class="mb-1">{{translate('Top 5 Customer')}}</h4>
                                    <p class="fs-12">{{translate('According to search volume')}}</p>
                                </div>
                                <div class="select-wrap d-flex flex-wrap gap-10">
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'date_range',
                                        'id'          => 'top_customers_date_range',
                                        'selectClass' => 'js-select min-w180 h-30 top-customers__select',
                                        'optionNull'  => translate('Select Date Range'),
                                        'options'     => $analyticsDateRangeOptions,
                                        'value'       => $queryParams['date_range'] ?? null,
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>
                            <div class="text-center">
                                @if($hasTopCustomerData)
                                    <div id="apex_donut-chart"></div>
                                @else
                                    <div class="text-center my-5">
                                        <img src="{{ asset('public/assets/admin-module/img/icons/customer-no-data.png') }}" alt="">
                                        <span class="mt-1 d-block">{{translate('No data available')}}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-3">
                                <div class="">
                                    <h4 class="mb-1">{{translate('Top Services')}}</h4>
                                    <p class="fs-12">{{translate('According to search volume')}}</p>
                                </div>
                                <div class="select-wrap d-flex flex-wrap gap-10">
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'date_range_2',
                                        'id'          => 'top_services_date_range',
                                        'selectClass' => 'js-select min-w180 h-30 top-services__select',
                                        'optionNull'  => translate('Select Date Range'),
                                        'options'     => $analyticsDateRangeOptions,
                                        'value'       => $queryParams['date_range_2'] ?? null,
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>

                            <div class="mt-4">
                                @if($hasTopServicesData)
                                    <div class="">
                                        <ul class="common-list after-none gap-10 d-flex flex-column">
                                            @foreach($topServicesWithService as $item)
                                                <li>
                                                    <div
                                                        class="mb-2 d-flex align-items-center justify-content-between gap-10 flex-wrap">
                                                        <span class="zone-name">{{$item->service->name}}</span>
                                                        <span class="booking-count">{{with_decimal_point(($item['total_volume']*100)/$total)}}% {{translate('search volume')}}</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar"
                                                             style="width: {{with_decimal_point(($item['total_volume']*100)/$total)}}%"
                                                             aria-valuenow="50" aria-valuemin="0"
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="text-center my-5">
                                        <img src="{{ asset('public/assets/admin-module/img/icons/empty-state-document.png') }}" alt="">
                                        <span class="mt-1 d-block">{{translate('No data available')}}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <span class="material-icons">search</span>
                                </span>
                                <input type="search" class="theme-input-style search-form__input"
                                       value="{{$search??''}}" name="search"
                                       placeholder="{{translate('search_by_Customer')}}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-nowrap">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Customer')}}</th>
                                <th>{{translate('Search')}} <br> {{translate('Volume')}}</th>
                                <th>{{translate('Related')}} <br> {{translate('Services')}}</th>
                                <th>{{translate('Times Service')}} <br> {{translate('Visited')}}</th>
                                <th>{{translate('Times Added')}} <br> {{translate('to Cart')}}</th>
                                <th>{{translate('Total')}} <br> {{translate('Booking')}}</th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($customers as $key=>$customer)
                                <tr>
                                    <td>{{$customers->firstitem()+$key}}</td>
                                    <td>
                                        <div class="media align-items-center gap-3">
                                            <div class="avatar avatar-lg">
                                                <a href="{{route('admin.customer.detail',[$customer->id, 'web_page'=>'overview'])}}">
                                                    <img class="avatar-img radius-5 onerror-image"
                                                         src="{{$customer->profile_image_full_path}}"
                                                         alt="{{ translate('profile_image') }}">
                                                </a>
                                            </div>
                                            <div class="media-body">
                                                <h5>
                                                    <a href="{{route('admin.customer.detail',[$customer->id, 'web_page'=>'overview'])}}">
                                                        {{$customer['name']}}
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{$customer->total_volume??0}}</td>
                                    <td>{{$customer->total_response_data_count??0}}</td>
                                    <td>{{$customer->total_visited_service_count??0}}</td>
                                    <td>{{$customer->total_added_to_cart_count??0}}</td>
                                    <td>{{$customer->bookings_count??0}}</td>
                                    <td>
                                        <div class="table-actions d-flex justify-content-center">
                                            <a href="{{route('admin.customer.detail',[$customer->id, 'web_page'=>'overview'])}}"
                                               type="button" class="action-btn btn--light-primary" style="--size: 30px">
                                                <span class="material-icons">visibility</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 8,
                                    'variant' => (
                                        filled($search ?? null)
                                        || (($queryParams['date_range'] ?? 'all_time') !== 'all_time')
                                        || (($queryParams['date_range_2'] ?? 'all_time') !== 'all_time')
                                    ) ? 'search' : 'list'])
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $customers->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/apex/apexcharts.min.js"></script>
    <script>
        "use strict";
        var options = {
            series: @json($graph_data['search_volume']),
            chart: {
                type: 'donut',
                width: "100%",
                height: 400
            },
            labels: @json(count($graph_data['top_customers']) > 0 ? $graph_data['top_customers'] : ''),
            legend: {
                show: true,
                floating: false,
                fontSize: '14px',
                position: 'right',
                horizontalAlign: 'center',
                offsetY: -10,
                itemMargin: {
                    horizontal: 5,
                    vertical: 10
                },
            },
            responsive: [{
                breakpoint: 1400,
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        const donutChartElement = document.querySelector("#apex_donut-chart");

        if (donutChartElement) {
            const chart = new ApexCharts(donutChartElement, options);
            chart.render();
        }


        $(".top-customers__select").on('change', function () {
            if (this.value !== "") location.href = "{{route('admin.analytics.search.customer')}}" + '?date_range=' + this.value + '&date_range_2=' + '{{$queryParams['date_range_2']??'all_time'}}';
        });
        $(".top-services__select").on('change', function () {
            if (this.value !== "") location.href = "{{route('admin.analytics.search.customer')}}" + '?date_range=' + '{{$queryParams['date_range']??'all_time'}}' + '&date_range_2=' + this.value;
        });
    </script>
@endpush
