@extends('adminmodule::layouts.master')

@section('title', translate('Update Before After'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Update Before After') }}</h2>
                    </div>

                    @can('before_after_update')
                        <div class="card mb-30">
                            <div class="card-body p-30">
                                <form action="{{ route('admin.before-after.update', [$item->id]) }}"
                                      method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-4">
                                        <div class="col-lg-6">
                                            <label class="fs-14 fw-medium mb-2">{{ translate('Title') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title"
                                                   value="{{ old('title', $item->title) }}"
                                                   placeholder="{{ translate('e.g. Kitchens') }}" required maxlength="190">
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="fs-14 fw-medium mb-2">{{ translate('Sort Order') }}</label>
                                            <input type="number" class="form-control" name="sort_order"
                                                   value="{{ old('sort_order', $item->sort_order) }}" min="0" step="1">
                                        </div>
                                        <div class="col-lg-6">
                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                'name'             => 'before_image',
                                                'id'               => 'beforeImage',
                                                'title'            => translate('Before Image'),
                                                'subtitle'         => translate('Upload before image'),
                                                'required'         => false,
                                                'image'            => $item->before_image_full_path,
                                                'ratio'            => 'ratio-1-1',
                                                'instructionRatio' => '1:1'])
                                        </div>
                                        <div class="col-lg-6">
                                            @include('adminmodule::admin.partials._single-image-upload', [
                                                'name'             => 'after_image',
                                                'id'               => 'afterImage',
                                                'title'            => translate('After Image'),
                                                'subtitle'         => translate('Upload after image'),
                                                'required'         => false,
                                                'image'            => $item->after_image_full_path,
                                                'ratio'            => 'ratio-1-1',
                                                'instructionRatio' => '1:1'])
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-4 flex-wrap justify-content-end">
                                                <a href="{{ route('admin.before-after.create') }}" class="btn btn--secondary">{{ translate('back') }}</a>
                                                <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
