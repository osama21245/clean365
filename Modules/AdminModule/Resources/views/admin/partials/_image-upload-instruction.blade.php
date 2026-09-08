@php($instruction = $instruction ?? null)
@php($instructionRatio = $instructionRatio ?? null)
@php($formats = $formats ?? implode(', ', array_column(IMAGEEXTENSION, 'key')))
@php($maxFileSize = $maxFileSize ?? readableUploadMaxFileSize('image'))
@php($class = $class ?? 'text-muted fs-12 mb-0')

@if($instruction || $instructionRatio)
    <p class="{{ $class }}">
        @if($instruction)
            {{ $instruction }}
        @else
            <span>{{ translate('Image format - :formats', ['formats' => $formats]) }}</span>
            <span class="mx-1">|</span>
            <span>{{ translate('Image Size - maximum size :size', ['size' => $maxFileSize]) }}</span>
            @if($instructionRatio)
                <span class="mx-1">|</span>
                <span>{{ translate('Image Ratio - :ratio', ['ratio' => $instructionRatio]) }}</span>
            @endif
        @endif
    </p>
@endif
