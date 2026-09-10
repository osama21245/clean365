@extends('adminmodule::layouts.master')

@section('title', translate('Edit Additional Service'))

@push('css_or_js')
    <style>
        .feature-row-input {
            transition: all 0.2s ease;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                        <h2 class="page-title">{{ translate('Edit Additional Service') }}</h2>
                        <a href="{{ route('admin.additional-service.index') }}" class="btn btn--primary">
                            <span class="material-icons">arrow_back</span> {{ translate('Back to List') }}
                        </a>
                    </div>

                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form action="{{ route('admin.additional-service.update', [$service->id]) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('put')

                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-8">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-label">{{ translate('الاسم الرئيسي') }}
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" id="name" class="form-control"
                                                        value="{{ old('name', $service->name) }}" required maxlength="191">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="price" class="form-label">{{ translate('السعر') }}</label>
                                                    <input type="number" step="0.01" name="price" id="price"
                                                        class="form-control" value="{{ old('price', $service->price) }}"
                                                        min="0">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="sort_order"
                                                        class="form-label">{{ translate('الترتيب') }}</label>
                                                    <input type="number" name="sort_order" id="sort_order"
                                                        class="form-control"
                                                        value="{{ old('sort_order', $service->sort_order) }}" min="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 text-center">
                                        @include('adminmodule::admin.partials._single-image-upload', [
                                            'name' => 'icon',
                                            'id' => 'additionalServiceIcon',
                                            'title' => translate('صورة الخدمة'),
                                            'subtitle' => translate('رفع صورة الخدمة الإضافية'),
                                            'required' => false,
                                            'image' => $service->icon ? $service->icon_full_path : null,
                                            'instructionRatio' => '1:1'
                                        ])
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label
                                                    class="form-label mb-0 fw-semibold">{{ translate('الفيتشرز (الميزات مع السعر والأيقونة)') }}</label>
                                                <button type="button"
                                                    class="btn btn-sm btn--primary-light d-flex align-items-center gap-1"
                                                    id="add-feature-btn">
                                                    <span class="material-icons fs-16">add</span>
                                                    {{ translate('إضافة ميزة أخرى') }}
                                                </button>
                                            </div>

                                            <div id="features-wrapper">
                                                @php
                                                    $features = is_array($service->features) && count($service->features) > 0 ? $service->features : [['title' => '', 'price' => 0, 'icon' => null, 'icon_full_path' => null]];
                                                @endphp
                                                @foreach($features as $index => $feature)
                                                    <div class="row g-2 align-items-center mb-2 feature-row-input">
                                                        <div class="col-md-5">
                                                            <textarea name="feature_titles[{{ $index }}]" class="form-control"
                                                                rows="2"
                                                                placeholder="{{ translate('اسم الميزة (أضف أسطر جديدة لإضافة أكثر من ميزة)') }}">{{ $feature['title'] ?? '' }}</textarea>
                                                            <input type="hidden" name="feature_existing_icons[{{ $index }}]"
                                                                value="{{ $feature['icon'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" step="0.01" name="feature_prices[{{ $index }}]"
                                                                class="form-control" value="{{ $feature['price'] ?? 0 }}"
                                                                placeholder="{{ translate('السعر (اختياري)') }}" min="0">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="file" name="feature_icons[{{ $index }}]"
                                                                class="form-control" accept="image/*">
                                                            @if(!empty($feature['icon_full_path']))
                                                                <div class="mt-1">
                                                                    <img src="{{ $feature['icon_full_path'] }}" alt=""
                                                                        style="width: 28px; height: 28px; object-fit: contain;"
                                                                        class="border rounded p-1">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-1 text-end">
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm remove-feature-btn"
                                                                style="min-width: 38px; height: 38px;">
                                                                <span class="material-icons">delete</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('admin.additional-service.index') }}"
                                        class="btn btn-secondary">{{ translate('إلغاء') }}</a>
                                    <button type="submit"
                                        class="btn btn--primary">{{ translate('تحديث البيانات') }}</button>
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
    <script>
        $(document).ready(function () {
            var rowIndex = {{ count($features) }};

            $('#add-feature-btn').on('click', function () {
                var newRow = `
                                        <div class="row g-2 align-items-center mb-2 feature-row-input">
                                            <div class="col-md-5">
                                                <textarea name="feature_titles[${rowIndex}]" class="form-control" rows="2" placeholder="{{ translate('اسم الميزة (أضف أسطر جديدة لإضافة أكثر من ميزة)') }}"></textarea>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" step="0.01" name="feature_prices[${rowIndex}]" class="form-control" placeholder="{{ translate('السعر (اختياري)') }}" min="0">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="file" name="feature_icons[${rowIndex}]" class="form-control" accept="image/*">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-feature-btn" style="min-width: 38px; height: 38px;">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </div>`;
                $('#features-wrapper').append(newRow);
                rowIndex++;
            });

            $(document).on('click', '.remove-feature-btn', function () {
                if ($('#features-wrapper .feature-row-input').length > 1) {
                    $(this).closest('.feature-row-input').remove();
                } else {
                    $(this).closest('.feature-row-input').find('input').val('');
                }
            });
        });
    </script>
@endpush