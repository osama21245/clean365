@php($showView = $showView ?? true)
@php($showEdit = $showEdit ?? true)
@php($showDelete = $showDelete ?? true)
@php($image = $image ?? null)
@php($placeholder = $placeholder ?? asset('assets/admin-module/img/media/photo_camera.svg'))
@php($disableInstruction = $disableInstruction ?? false)

<div class="d-flex flex-column align-items-center gap-3">
    @if($title || $subtitle)
        <div class="text-center">
            <div class="text-dark fs-16 mb-1">{{ $title }} @if($required ?? false)
                    <span class="text-danger">*</span>
                @endif</div>
            <div class="text-muted fs-12">{{ $subtitle ?? '' }}</div>
        </div>
    @endif

    <div class="global-image-upload position-relative max-w-100 overflow-hidden bg-white border-dashed rounded-2 mx-auto {{ $ratio ?? 'ratio-1-1' }} {{ $height ?? 'h-120' }} d-center {{ $image ? 'has-image' : '' }}">
        <input type="file" name="{{ $name }}" id="{{ $id }}" class="global-image-upload-input"
            accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}"
            data-maxFileSize="{{ readableUploadMaxFileSize('image') }}" {{ $required ? 'required' : '' }}>

        <div class="global-upload-box {{ $image ? 'global-upload-box-hidden' : '' }}">
            <div class="upload-content text-center">
                <span class="material-symbols-outlined placeholder-icon mb-1 text-primary">photo_camera</span>
                <span class="fz-10 d-block">{{ translate('Add image') }}</span>
            </div>
        </div>
        <img class="global-image-preview {{ $image ? '' : 'd-none' }}" src="{{ $image }}" alt="Preview" />

        <div class="overlay-icons {{ $image ? '' : 'd-none' }}">
            @if($showView)
                <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover view-icon d-flex align-items-center justify-content-center" title="{{ translate('View') }}" data-bs-toggle="modal" data-bs-target="#imageShowingMOdal" data-file-name="{{ $image ? basename($image) : '' }}">
                    <span class="material-icons">visibility</span>
                </button>
            @endif
            @if($showEdit)
                <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover edit-icon d-flex align-items-center justify-content-center" title="{{ translate('Edit') }}">
                    <span class="material-icons">edit</span>
                </button>
            @endif
            @if($showDelete)
                <button type="button" class="action-btn btn--danger delete_section bg-white outline-danger-hover remove-icon d-flex align-items-center justify-content-center" title="{{ translate('Remove') }}">
                    <i class="material-symbols-outlined">delete</i>
                </button>
            @endif
        </div>
    </div>
    @if(!$disableInstruction)
        @include('adminmodule::admin.partials._image-upload-instruction', [
        'instruction' => $instruction ?? null,
        'instructionRatio' => $instructionRatio ?? null,
        'class' => 'text-muted fs-12 mb-0 text-center'])
    @endif
</div>
