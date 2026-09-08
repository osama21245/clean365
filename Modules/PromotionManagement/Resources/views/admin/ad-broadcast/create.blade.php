@extends('adminmodule::layouts.new-master')

@section('title', translate('Compose Ad Broadcast'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module') }}/plugins/select2/select2.min.css"/>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{ translate('Compose Ad Broadcast') }}</h2>
            </div>

            <div class="card">
                <div class="card-body p-20">
                    <form action="{{ route('admin.ad-broadcast.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Title (Arabic)') }}</label>
                                <input type="text" name="title[ar]" class="form-control theme-input-style" value="{{ old('title.ar') }}" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Title (English)') }}</label>
                                <input type="text" name="title[en]" class="form-control theme-input-style" value="{{ old('title.en') }}" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Description (Arabic)') }}</label>
                                <textarea name="description[ar]" rows="2" class="form-control theme-input-style">{{ old('description.ar') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Description (English)') }}</label>
                                <textarea name="description[en]" rows="2" class="form-control theme-input-style">{{ old('description.en') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Audiences') }} *</label>
                                @foreach(['customers' => translate('Customers'), 'providers' => translate('Providers'), 'servicemen' => translate('Servicemen'), 'guests' => translate('Guests')] as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="audiences[]" value="{{ $value }}"
                                               id="audience_{{ $value }}"
                                            {{ in_array($value, old('audiences', ['customers']), true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="audience_{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Zones') }} ({{ translate('optional — empty = all / token multicast') }})</label>
                                <select name="zone_ids[]" class="form-select theme-input-style select-zone" multiple>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Content target') }}</label>
                                <select name="content_mode" class="form-select theme-input-style">
                                    <option value="none">{{ translate('None') }}</option>
                                    <option value="category">{{ translate('Category') }}</option>
                                    <option value="service">{{ translate('Service') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Category') }}</label>
                                <select name="parent_category_id" class="form-select theme-input-style">
                                    <option value="">{{ translate('Select') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ translate('Service') }}</label>
                                <select name="service_id" class="form-select theme-input-style">
                                    <option value="">{{ translate('Select') }}</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ translate('Cover image') }}</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn--primary">{{ translate('Send broadcast') }}</button>
                                <a href="{{ route('admin.ad-broadcast.index') }}" class="btn btn--secondary">{{ translate('Cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/admin-module') }}/plugins/select2/select2.min.js"></script>
    <script>
        $(function () {
            $('.select-zone').select2({placeholder: '{{ translate('Zones') }}', width: '100%'});
        });
    </script>
@endpush
