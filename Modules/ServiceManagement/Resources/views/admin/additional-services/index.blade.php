@extends('adminmodule::layouts.master')

@section('title', translate('الخدمات الإضافية'))

@push('css_or_js')
    <style>
        .service-card {
            border: 1px solid #e0e6ed;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }
        .service-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.07);
        }
        .feature-item {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .feature-item img {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }
    </style>
@endpush

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                    <h2 class="page-title">{{ translate('الخدمات الإضافية') }}</h2>
                </div>

                {{-- Create Form Card --}}
                <div class="card mb-30">
                    <div class="card-body p-30">
                        <form action="{{ route('admin.additional-service.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <h4 class="mb-3">{{ translate('إضافة خدمة إضافية جديدة') }}</h4>

                            <div class="row g-4 align-items-center">
                                <div class="col-lg-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="form-label">{{ translate('الاسم الرئيسي') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name" class="form-control" placeholder="{{ translate('مثال: الخدمات المتخصصة') }}" required maxlength="191">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="price" class="form-label">{{ translate('السعر') }}</label>
                                                <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="0.00" min="0">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="sort_order" class="form-label">{{ translate('الترتيب') }}</label>
                                                <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
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
                                        'image' => null,
                                        'instructionRatio' => '1:1'
                                    ])
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0 fw-semibold">{{ translate('الفيتشرز (الميزات مع السعر والأيقونة)') }}</label>
                                            <button type="button" class="btn btn-sm btn--primary-light d-flex align-items-center gap-1" id="add-feature-btn">
                                                <span class="material-icons fs-16">add</span> {{ translate('إضافة ميزة أخرى') }}
                                            </button>
                                        </div>

                                        <div id="features-wrapper">
                                            <div class="row g-2 align-items-center mb-2 feature-row-input">
                                                <div class="col-md-5">
                                                    <textarea name="feature_titles[0]" class="form-control" rows="2" placeholder="{{ translate('اسم الميزة (أضف أسطر جديدة لإضافة أكثر من ميزة)') }}"></textarea>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" step="0.01" name="feature_prices[0]" class="form-control" placeholder="{{ translate('السعر (اختياري)') }}" min="0">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="file" name="feature_icons[0]" class="form-control" accept="image/*" title="{{ translate('أيقونة الميزة') }}">
                                                </div>
                                                <div class="col-md-1 text-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-feature-btn" style="min-width: 38px; height: 38px;">
                                                        <span class="material-icons">delete</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-secondary">{{ translate('إلغاء') }}</button>
                                <button type="submit" class="btn btn--primary">{{ translate('حفظ الخدمة') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Search & Filter Section --}}
                <div class="card mb-30">
                    <div class="card-body p-20">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <form action="{{ route('admin.additional-service.index') }}" method="get" class="search-form d-flex gap-2">
                                    <input type="search" name="search" class="form-control" placeholder="{{ translate('ابحث باسم الخدمة أو الميزات...') }}" value="{{ $search }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('بحث') }}</button>
                                </form>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-soft-info text-info fs-14 p-2">{{ translate('إجمالي الخدمات:') }} {{ $services->total() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Display Services Grid --}}
                <div class="row g-4">
                    @forelse($services as $service)
                        <div class="col-lg-4 col-md-6">
                            <div class="card service-card h-100">
                                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($service->icon)
                                            <img src="{{ $service->icon_full_path }}" alt="{{ $service->name }}" style="width: 36px; height: 36px; object-fit: contain;">
                                        @else
                                            <span class="material-icons text-primary fs-24">grid_view</span>
                                        @endif
                                        <div>
                                            <h5 class="mb-0 fw-bold">{{ $service->name }}</h5>
                                            @if($service->price > 0)
                                                <small class="text-success fw-bold">{{ $service->price }} {{ translate('ر.س') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="switcher" title="{{ translate('الحالة') }}">
                                            <input class="switcher_input route-alert"
                                                   type="checkbox"
                                                   {{ $service->is_active ? 'checked' : '' }}
                                                   data-url="{{ route('admin.additional-service.status-update', [$service->id]) }}"
                                                   data-message="{{ translate('هل تريد تغيير حالة هذه الخدمة؟') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                        <a href="{{ route('admin.additional-service.edit', [$service->id]) }}" class="action-btn btn--primary-light" title="{{ translate('تعديل') }}">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        <form action="{{ route('admin.additional-service.delete', [$service->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ translate('هل أنت تأكد من الحذف؟') }}')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="action-btn btn--danger-light border-0" title="{{ translate('حذف') }}">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <div class="mb-3 d-flex justify-content-between align-items-center text-muted fs-12">
                                        <span><i class="material-icons text-muted fs-14 align-middle">sort</i> {{ translate('الترتيب:') }} {{ $service->sort_order }}</span>
                                    </div>

                                    <h6 class="text-muted fw-semibold mb-2 fs-13">{{ translate('الفيتشرز (الميزات):') }}</h6>
                                    @if(is_array($service->features) && count($service->features) > 0)
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($service->features as $feature)
                                                <div class="feature-item">
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if(!empty($feature['icon_full_path']))
                                                            <img src="{{ $feature['icon_full_path'] }}" alt="{{ $feature['title'] }}">
                                                        @else
                                                            <span class="material-icons text-primary fs-18">check_circle</span>
                                                        @endif
                                                        <span>{{ $feature['title'] }}</span>
                                                    </div>
                                                    @if(isset($feature['price']) && $feature['price'] > 0)
                                                        <span class="badge bg-soft-success text-success fs-12">{{ $feature['price'] }} {{ translate('ر.س') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted fs-13 text-center my-3">{{ translate('لا توجد فتشيرز مضافة لهذه الخدمة.') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <img src="{{ asset('public/assets/admin-module/img/media/empty-state.png') }}" alt="" style="width: 120px;" class="mb-3 onerror-image">
                            <h5 class="text-muted">{{ translate('لم يتم العثور على خدمات إضافية.') }}</h5>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {!! $services->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        var rowIndex = 1;

        $('#add-feature-btn').on('click', function() {
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

        $(document).on('click', '.remove-feature-btn', function() {
            if ($('#features-wrapper .feature-row-input').length > 1) {
                $(this).closest('.feature-row-input').remove();
            } else {
                $(this).closest('.feature-row-input').find('input').val('');
            }
        });
    });
</script>
@endpush
