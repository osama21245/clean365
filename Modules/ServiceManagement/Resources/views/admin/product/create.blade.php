@extends('adminmodule::layouts.master')

@section('title', translate('add_new_product'))

@push('css_or_js')
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('add_new_product')}}</h2>
                    </div>
                    <div class="card-wrap">
                        <div class="card-body-inner">
                            <form action="{{route('admin.product.store')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row service-description-wrapper">
                                    <div class="col-xxl-9 col-lg-8 mb-5 mb-lg-0">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="mb-20">
                                                    <h3 class="mb-1 text-dark">{{ translate('Basic Setup') }}</h3>
                                                    <p class="fs-12 text-color">{{ translate('Provide essential product details') }}</p>
                                                </div>
                                                <div class="bg-light p-xxl-20 p-12px rounded">
                                                    @php($language= Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','system_language')->first())
                                                    @if($language)
                                                        <ul class="nav nav--tabs text-nowrap overflow-auto flex-nowrap border-color-primary mb-4">
                                                            <li class="nav-item">
                                                                <a class="nav-link lang_link active" href="#" id="default-link">{{translate('default')}}</a>
                                                            </li>
                                                            @foreach ($language?->live_values as $lang)
                                                                <li class="nav-item">
                                                                    <a class="nav-link lang_link" href="#" id="{{ $lang['code'] }}-link">{{ get_language_name($lang['code']) }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif

                                                    @if($language)
                                                        <div class="lang-form" id="default-form">
                                                            <div class="mb-30">
                                                                <label class="mb-2 lh-1 fs-14 fw-medium">{{ translate('Product Name (:label)', ['label' => translate('default')]) }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="name[]" class="form-control" required placeholder="{{translate('Enter Product Name')}}" value="{{ old('name.0') }}">
                                                            </div>
                                                            <div class="mb-30">
                                                                <label class="mb-2 lh-1 fs-14 fw-medium">{{ translate('Short Description (:label)', ['label' => translate('default')]) }}</label>
                                                                <textarea name="short_description[]" class="form-control" rows="3" placeholder="{{translate('Enter short description')}}">{{ old('short_description.0') }}</textarea>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="default">

                                                        @foreach ($language?->live_values as $lang)
                                                            <div class="d-none lang-form" id="{{$lang['code']}}-form">
                                                                <div class="mb-30">
                                                                    <label class="mb-2 lh-1 fs-14 fw-medium">{{ translate('Product Name (:code)', ['code' => strtoupper($lang['code'])]) }}</label>
                                                                    <input type="text" name="name[]" class="form-control" placeholder="{{translate('Enter Product Name')}}">
                                                                </div>
                                                                <div class="mb-30">
                                                                    <label class="mb-2 lh-1 fs-14 fw-medium">{{ translate('Short Description (:code)', ['code' => strtoupper($lang['code'])]) }}</label>
                                                                    <textarea name="short_description[]" class="form-control" rows="3" placeholder="{{translate('Enter short description')}}"></textarea>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                                        @endforeach
                                                    @endif

                                                    <hr class="my-4">

                                                    <div class="row">
                                                        <div class="col-md-6 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('category')}} <span class="text-danger">*</span></label>
                                                            <select name="product_category_id" class="select2 form-control" required>
                                                                <option value="">{{translate('select_category')}}</option>
                                                                @foreach($categories as $category)
                                                                    <option value="{{$category->id}}" {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                                                        {{$category->name}}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @if($categories->isEmpty())
                                                                <p class="fs-12 text-danger mt-2 mb-0">
                                                                    {{translate('Please add a product category first')}} —
                                                                    <a href="{{route('admin.product-category.list')}}">{{translate('product_categories')}}</a>
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-6 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('badge')}}</label>
                                                            <select name="badge" class="select2 form-control">
                                                                <option value="">{{translate('none')}}</option>
                                                                <option value="new" {{ old('badge') == 'new' ? 'selected' : '' }}>{{translate('new')}}</option>
                                                                <option value="sale" {{ old('badge') == 'sale' ? 'selected' : '' }}>{{translate('sale')}}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('price')}} ({{ currency_symbol() }}) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price') }}" placeholder="49.00">
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('sale_price')}} ({{ currency_symbol() }})</label>
                                                            <input type="number" step="0.01" min="0" name="sale_price" class="form-control" value="{{ old('sale_price') }}" placeholder="39.00">
                                                        </div>
                                                        <div class="col-md-4 mb-30">
                                                            <label class="mb-2 lh-1 fs-14 fw-medium">{{translate('rating')}}</label>
                                                            <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ old('rating', 0) }}" placeholder="4.5">
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-3">
                                                        <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                                        <button type="submit" class="btn btn--primary">{{translate('Submit')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-3 col-lg-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="bg-light rounded w-100 mb-30 p-3">
                                                    @include('adminmodule::admin.partials._single-image-upload', [
                                                        'name' => 'thumbnail',
                                                        'id' => 'productThumbnail',
                                                        'title' => translate('product_image'),
                                                        'subtitle' => translate('Upload your product Image'),
                                                        'required' => true,
                                                        'image' => null,
                                                        'ratio' => 'ratio-1-1',
                                                        'height' => 'h-120',
                                                        'instructionRatio' => '1:1',
                                                        'showView' => false,
                                                        'showEdit' => true,
                                                        'showDelete' => true])
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module')}}/plugins/select2/select2.min.js"></script>
    <script>
        $(".lang_link").click(function (e) {
            e.preventDefault();
            $('.lang_link').removeClass('active');
            $(this).addClass('active');
            let lang = $(this).attr('id').split('-')[0];
            $('.lang-form').addClass('d-none');
            $("#" + lang + "-form").removeClass('d-none');
        });
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});
        });
    </script>
@endpush
