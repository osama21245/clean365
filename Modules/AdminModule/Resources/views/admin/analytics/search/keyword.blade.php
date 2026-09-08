@extends('adminmodule::layouts.master')

@section('title',translate('Keyword_Search_Analytics'))

@section('content')
        <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Keyword_Search_Analytics')}}</h2>
            </div>
            @php
                $hasTrendingKeywordsData = count($graph_data['count']) > 0 && count($graph_data['keyword']) > 0;
                $hasZoneWiseSearchVolumeData = $total > 0 && count($zoneWiseVolumes) > 0;
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
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body keyword-search-wrapper">
                            <div class="d-flex flex-wrap justify-content-between gap-3">
                                <h4>{{translate('Trending_Keywords')}}</h4>
                                <div class="select-wrap d-flex flex-wrap gap-10">
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'date_range',
                                        'id'          => 'trending_keywords_date_range',
                                        'selectClass' => 'js-select trending-keywords__select',
                                        'optionNull'  => translate('Select Date Range'),
                                        'options'     => $analyticsDateRangeOptions,
                                        'value'       => $queryParams['date_range'] ?? null,
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>
                            <div class="text-center">
                                @if($hasTrendingKeywordsData)
                                    <div id="apex_radial-bar-chart"></div>
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
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-3">
                                <h4>{{translate('Zone_Wise_Search_Volume')}}</h4>
                                <div class="select-wrap d-flex flex-wrap gap-10">
                                    @include('partials._form-field', [
                                        'type'        => 'select',
                                        'name'        => 'date_range_2',
                                        'id'          => 'date-range',
                                        'selectClass' => 'js-select w-100 zone-search-volume__select',
                                        'optionNull'  => translate('Select Date Range'),
                                        'options'     => $analyticsDateRangeOptions,
                                        'value'       => $queryParams['date_range_2'] ?? null,
                                        'wrapClass'   => 'mb-0'])
                                </div>
                            </div>

                            <div class="mt-4">
                                @if($hasZoneWiseSearchVolumeData)
                                    <div class="row gy-3">
                                        <div class="col-lg-5">
                                            <div
                                                class="bg-light h-100 rounded d-flex justify-content-center align-items-center p-3">
                                                <div class="text-center">
                                                    <img class="mb-2" width="50"
                                                         src="{{asset('public/assets/admin-module')}}/img/media/search-volume.png"
                                                         alt="">
                                                    <h2 class="mb-2">{{$total}}</h2>
                                                    <p>{{translate('Total Search Volume')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-7">
                                            <div class="max-h320-auto">
                                                <ul class="common-list after-none gap-10 d-flex flex-column">
                                                    @foreach($zoneWiseVolumes as $item)
                                                        <li>
                                                            <div
                                                                class="mb-2 d-flex align-items-center justify-content-between gap-10 flex-wrap">
                                                                <span class="zone-name">{{$item['zone']['name']}}</span>
                                                                <span class="booking-count">{{with_decimal_point(($item['count']*100)/$total)}} % {{translate('search volume')}}</span>
                                                            </div>
                                                            <div class="progress">
                                                                <div class="progress-bar" role="progressbar"
                                                                     style="width: {{with_decimal_point(($item['count']*100)/$total)}}%"
                                                                     aria-valuenow="25" aria-valuemin="0"
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
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
                                       placeholder="{{translate('search_by_Keyword')}}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-nowrap">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Keyword')}}</th>
                                <th>{{translate('Search Volume')}}</th>
                                <th>{{translate('Related Services')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($searches as $key=>$item)
                                <tr>
                                    <td>{{$searches->firstitem()+$key}}</td>
                                    <td>{{$item->keyword??''}}</td>
                                    <td>{{$item->total_volume}}</td>
                                    <td>{{$item->total_response_data_count}}</td>
                                </tr>
                            @empty
                                @include('adminmodule::layouts.partials.components._empty-state', [
                                    'colspan' => 4,
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
                        {!! $searches->links() !!}
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
            series: @json($graph_data['count']),
            chart: {
                height: 350,
                type: 'radialBar',
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        margin: 10,
                        size: '55%',
                    },
                    dataLabels: {
                        name: {
                            fontSize: '16px',
                        },
                        value: {
                            fontSize: '14px',
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function (w) {
                                return {{array_sum($graph_data['count'])}}
                            }
                        }
                    }
                }
            },
            labels: @json(count($graph_data['keyword']) > 0 ? $graph_data['keyword'] : ''),
            colors: ['#1B3A6B', '#2EAD6F', '#4A8BC4', '#7BC99A', '#F3C278'],
            legend: {
                show: true,
                floating: false,
                fontSize: '12px',
                position: 'bottom',
                horizontalAlign: 'center',
                offsetY: -10,
                itemMargin: {
                    horizontal: 5,
                    vertical: 5
                },
                labels: {
                    useSeriesColors: true,
                },
                markers: {
                    size: 0
                },
                formatter: function (seriesName, opts) {
                    return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
                },
            },
        };

        const radialChartElement = document.querySelector("#apex_radial-bar-chart");

        if (radialChartElement) {
            const chart = new ApexCharts(radialChartElement, options);
            chart.render();
        }


        $(".trending-keywords__select").on('change', function () {
            if (this.value !== "") location.href = "{{route('admin.analytics.search.keyword')}}" + '?date_range=' + this.value + '&date_range_2=' + '{{$queryParams['date_range_2']??'all_time'}}';
        });

        $(".zone-search-volume__select").on('change', function () {
            if (this.value !== "") location.href = "{{route('admin.analytics.search.keyword')}}" + '?date_range=' + '{{$queryParams['date_range']??'all_time'}}' + '&date_range_2=' + this.value;
        });
    </script>
@endpush
