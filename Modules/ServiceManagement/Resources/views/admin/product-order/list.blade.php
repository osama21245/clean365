@extends('adminmodule::layouts.master')

@section('title', translate('store_orders'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('store_orders')}}</h2>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            @foreach(['all','pending','confirmed','processing','out_for_delivery','delivered','canceled'] as $status)
                                <li class="nav-item">
                                    <a class="nav-link {{$orderStatus==$status?'active':''}}"
                                       href="{{ route('admin.product-order.list', ['order_status' => $status, 'search' => $search]) }}">
                                        {{translate($status)}}
                                        <span class="badge bg-light text-dark ms-1">{{$statusCounts[$status] ?? 0}}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between mb-3">
                                <form action="{{url()->current()}}" class="search-form search-form_style-two" method="GET">
                                    <input type="hidden" name="order_status" value="{{$orderStatus}}">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                    <tr>
                                        <th>{{translate('Sl')}}</th>
                                        <th>{{translate('order_id')}}</th>
                                        <th>{{translate('customer')}}</th>
                                        <th>{{translate('items')}}</th>
                                        <th>{{translate('total')}}</th>
                                        <th>{{translate('order_status')}}</th>
                                        <th>{{translate('date')}}</th>
                                        <th>{{translate('action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($orders as $key => $order)
                                        <tr>
                                            <td>{{$orders->firstItem() + $key}}</td>
                                            <td>
                                                <span class="fw-medium">#{{ \Illuminate\Support\Str::limit($order->id, 8, '') }}</span>
                                            </td>
                                            <td>
                                                @if($order->customer)
                                                    {{ trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? '')) ?: ($order->customer->phone ?? translate('N/A')) }}
                                                    <div class="fs-12 text-muted">{{$order->customer->phone}}</div>
                                                @else
                                                    {{translate('guest')}}
                                                @endif
                                            </td>
                                            <td>{{$order->total_quantity}}</td>
                                            <td>{{with_currency_symbol($order->total)}}</td>
                                            <td>
                                                <span class="badge
                                                    @if($order->order_status=='pending') bg-warning
                                                    @elseif($order->order_status=='canceled') bg-danger
                                                    @elseif($order->order_status=='delivered') bg-success
                                                    @else bg-primary @endif">
                                                    {{translate($order->order_status)}}
                                                </span>
                                            </td>
                                            <td>{{$order->created_at?->format('Y-m-d H:i')}}</td>
                                            <td>
                                                <a href="{{route('admin.product-order.details', [$order->id])}}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <span class="material-icons">visibility</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                @include('adminmodule::layouts.partials.components._empty-state', ['text' => 'no_data_found'])
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {!! $orders->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
