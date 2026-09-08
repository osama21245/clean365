@php($maxCount = $maxCount ?? 2)
@php($showView = $showView ?? true)
@php($showDelete = $showDelete ?? true)
@php($images = $images ?? [])
@php($imageNames = $imageNames ?? [])
@php($imageUrls = $imageUrls ?? [])
@php($ratio = $ratio ?? 'ratio-2-1')
@php($height = $height ?? 'h-120')
@php($wrapperId = $wrapperId ?? $id . '_wrapper')
@php($validationField = $validationField ?? $id . '_validation')
@php($disableInstruction = $disableInstruction ?? false)

<div class="d-flex flex-column align-items-start gap-3">
    <div class="d-flex flex-column gap-1">
        <label class="text-dark fs-16 mb-1">{{ $title }} @if($required ?? false) <span class="text-danger">*</span> @endif</label>
        @if(!empty($subtitle))
            <p class="text-muted fs-12 mb-0">{{ $subtitle }}</p>
        @endif
        @if(!$disableInstruction)
            @include('adminmodule::admin.partials._image-upload-instruction', [
            'instruction' => $instruction ?? null,
            'instructionRatio' => $instructionRatio ?? null])
        @endif
    </div>

    <div id="{{ $wrapperId }}" class="global-multi-image-upload">
        <input type="hidden"
               name="{{ $validationField }}"
               value="{{ count($images) > 0 ? 1 : '' }}"
               class="multi-image-validation-proxy"
               data-upload-wrapper="#{{ $wrapperId }}">
        <div class="global-multi-image-upload-grid w-100 d-flex flex-wrap gap-3">
        @foreach($images as $key => $image)
            @php($rawImage = $imageNames[$key] ?? null)
            @php($imageName = is_array($rawImage) ? ($rawImage['image'] ?? basename($image)) : ($rawImage ?: basename($image)))
            <div class="global-image-upload global-multi-image-item position-relative overflow-hidden bg-white border-dashed rounded-2 {{ $ratio }} {{ $height }} d-center has-image"
                 id="{{ $id }}-image-{{ $key }}"
                 data-existing-image-item="true">
                <img class="global-image-preview global-multi-image-preview" src="{{ $image }}" alt="Preview" />
                <div class="overlay-icons">
                    @if($showView)
                        <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover view-icon d-flex align-items-center justify-content-center" title="{{ translate('View') }}" data-bs-toggle="modal" data-bs-target="#imageShowingMOdal" data-file-name="{{ $imageName }}">
                            <span class="material-icons">visibility</span>
                        </button>
                    @endif
                    @if($showDelete)
                        <button type="button" class="action-btn btn--danger remove-existing-image bg-white outline-danger-hover d-flex align-items-center justify-content-center"
                                data-image="{{ $imageName }}"
                                data-id="{{ $id }}-image-{{ $key }}"
                                data-upload-wrapper="#{{ $wrapperId }}"
                                title="{{ translate('Remove') }}">
                            <i class="material-symbols-outlined">delete</i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach

            <div id="{{ $id }}_picker" class="global-multi-image-picker {{ count($images) >= $maxCount ? 'd-none' : '' }}">
                <!-- Spartan picker will inject items here -->
            </div>
        </div>
        <div class="global-multi-image-warning"></div>
    </div>
</div>
