@extends('adminmodule::layouts.master')

@section('title', translate('order_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h2 class="page-title">{{translate('order_details')}}</h2>
                <a href="{{route('admin.product-order.list')}}" class="btn btn--secondary">{{translate('back')}}</a>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                                <div>
                                    <h4 class="mb-1">#{{ $order->id }}</h4>
                                    <p class="mb-0 text-muted">{{ $order->created_at?->format('Y-m-d H:i') }}</p>
                                </div>
                                <span class="badge
                                    @if($order->order_status=='pending') bg-warning
                                    @elseif($order->order_status=='canceled') bg-danger
                                    @elseif($order->order_status=='delivered') bg-success
                                    @else bg-primary @endif fs-14">
                                    {{translate($order->order_status)}}
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>{{translate('product')}}</th>
                                        <th>{{translate('price')}}</th>
                                        <th>{{translate('quantity')}}</th>
                                        <th>{{translate('total')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($order->details as $detail)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img width="45" class="rounded" src="{{$detail->thumbnail_full_path}}" alt="">
                                                    <span>{{$detail->product_name}}</span>
                                                </div>
                                            </td>
                                            <td>{{with_currency_symbol($detail->unit_price)}}</td>
                                            <td>{{$detail->quantity}}</td>
                                            <td>{{with_currency_symbol($detail->total_price)}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('delivery_details')}}</h4>
                            <p class="mb-2"><strong>{{translate('address')}}:</strong> {{$order->delivery_address}}</p>
                            <p class="mb-0"><strong>{{translate('location')}}:</strong> {{$order->delivery_lat}}, {{$order->delivery_lng}}</p>
                            @if($order->note)
                                <hr>
                                <p class="mb-0"><strong>{{translate('note')}}:</strong> {{$order->note}}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('customer')}}</h4>
                            @if($order->customer)
                                <p class="mb-1">{{ trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? '')) }}</p>
                                <p class="mb-1">{{$order->customer->phone}}</p>
                                <p class="mb-0">{{$order->customer->email}}</p>
                            @else
                                <p class="mb-0">{{translate('guest')}}</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('order_summary')}}</h4>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{translate('subtotal')}} ({{$order->total_quantity}})</span>
                                <span>{{with_currency_symbol($order->subtotal)}}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{translate('discount')}}</span>
                                <span class="text-success">-{{with_currency_symbol($order->discount_amount)}}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{translate('delivery_fee')}}</span>
                                <span>{{with_currency_symbol($order->delivery_fee)}}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>{{translate('total')}}</span>
                                <span>{{with_currency_symbol($order->total)}}</span>
                            </div>
                            <div class="mt-2 fs-12 text-muted">
                                {{translate('payment_method')}}: {{translate($order->payment_method)}}
                                @if($order->coupon_code)
                                    <br>{{translate('coupon')}}: {{$order->coupon_code}}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">{{translate('update_order_status')}}</h4>
                            <form action="{{route('admin.product-order.status-update', [$order->id])}}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <select name="order_status" class="form-control" required>
                                        @foreach(['pending','confirmed','processing','out_for_delivery','delivered','canceled'] as $status)
                                            <option value="{{$status}}" {{$order->order_status == $status ? 'selected' : ''}}>
                                                {{translate($status)}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn--primary w-100">{{translate('Update')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
