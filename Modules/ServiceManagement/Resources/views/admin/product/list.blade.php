@extends('adminmodule::layouts.master')

@section('title', translate('product_list'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h2 class="page-title">{{translate('product_list')}}</h2>
                        <a href="{{route('admin.product.create')}}" class="btn btn--primary">
                            <span class="material-icons">add</span>
                            {{translate('add_new_product')}}
                        </a>
                    </div>

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

                                <div class="d-flex gap-2 fw-medium align-items-center">
                                    <span class="opacity-75">{{translate('Total_Products')}}:</span>
                                    <span class="title-color">{{$products->total()}}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{translate('Sl')}}</th>
                                            <th>{{translate('image')}}</th>
                                            <th>{{translate('name')}}</th>
                                            <th>{{translate('category')}}</th>
                                            <th>{{translate('price')}}</th>
                                            <th>{{translate('badge')}}</th>
                                            <th>{{translate('status')}}</th>
                                            <th>{{translate('action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($products as $key => $product)
                                            <tr>
                                                <td>{{$products->firstItem() + $key}}</td>
                                                <td>
                                                    <img width="50" class="rounded" src="{{$product->thumbnail_full_path}}"
                                                        alt="{{$product->name}}">
                                                </td>
                                                <td>{{$product->name}}</td>
                                                <td>{{$product->category?->name ?? translate('N/A')}}</td>
                                                <td>
                                                    @if($product->sale_price)
                                                        <span
                                                            class="text-decoration-line-through text-muted me-1">{{with_currency_symbol($product->price)}}</span>
                                                        <span class="fw-bold">{{with_currency_symbol($product->sale_price)}}</span>
                                                    @else
                                                        {{with_currency_symbol($product->price)}}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($product->badge)
                                                        <span
                                                            class="badge bg-{{ $product->badge == 'sale' ? 'danger' : 'success' }}">
                                                            {{translate($product->badge)}}
                                                        </span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <label class="switcher">
                                                        <input class="switcher_input status-change" type="checkbox"
                                                            {{$product->is_active ? 'checked' : ''}}
                                                            data-url="{{route('admin.product.status-update', [$product->id])}}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{route('admin.product.edit', [$product->id])}}"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <span class="material-icons">edit</span>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger btn-sm form-alert"
                                                            data-id="product-{{$product->id}}"
                                                            data-message="{{translate('want_to_delete_this_product')}}?">
                                                            <span class="material-icons">delete</span>
                                                        </button>
                                                        <form action="{{route('admin.product.delete', [$product->id])}}"
                                                            method="post" id="product-{{$product->id}}" class="hidden">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>
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
                                {!! $products->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection