@php
    $article = $article ?? null;
@endphp

<div class="col-md-6">
    <label class="form-label">{{ translate('Title (AR)') }}</label>
    <input type="text" name="title_ar" class="form-control" required
           value="{{ old('title_ar', $article?->localeField('title', 'ar')) }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Title (EN)') }}</label>
    <input type="text" name="title_en" class="form-control" required
           value="{{ old('title_en', $article?->localeField('title', 'en')) }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Slug') }}</label>
    <input type="text" name="slug" class="form-control"
           value="{{ old('slug', $article?->slug) }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Service') }}</label>
    <select name="service_id" class="form-control">
        <option value="">{{ translate('Select') }}</option>
        @foreach($services as $service)
            <option value="{{ $service->id }}" @selected(old('service_id', $article?->service_id) == $service->id)>
                {{ $service->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Category') }}</label>
    <select name="category_id" class="form-control">
        <option value="">{{ translate('Select') }}</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $article?->category_id) == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Featured image') }}</label>
    <input type="file" name="featured_image" class="form-control" accept="image/*">
    @if($article?->featured_image_full_path)
        <img src="{{ $article->featured_image_full_path }}" alt="" class="mt-2" style="max-height:80px">
    @endif
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Excerpt (AR)') }}</label>
    <textarea name="excerpt_ar" class="form-control" rows="2">{{ old('excerpt_ar', $article?->localeField('excerpt', 'ar')) }}</textarea>
</div>
<div class="col-md-6">
    <label class="form-label">{{ translate('Excerpt (EN)') }}</label>
    <textarea name="excerpt_en" class="form-control" rows="2">{{ old('excerpt_en', $article?->localeField('excerpt', 'en')) }}</textarea>
</div>
<div class="col-12">
    <label class="form-label">{{ translate('Body (AR)') }}</label>
    <textarea name="body_ar" class="form-control" rows="8" required>{{ old('body_ar', $article?->localeField('body', 'ar')) }}</textarea>
</div>
<div class="col-12">
    <label class="form-label">{{ translate('Body (EN)') }}</label>
    <textarea name="body_en" class="form-control" rows="8" required>{{ old('body_en', $article?->localeField('body', 'en')) }}</textarea>
</div>
<div class="col-12">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
               @checked(old('is_active', $article?->is_active ?? true))>
        <label class="form-check-label" for="is_active">{{ translate('Active / Published') }}</label>
    </div>
</div>
