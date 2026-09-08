@php
    $type           = $type           ?? 'text';
    $name           = $name           ?? '';
    $cleanName      = preg_replace('/\[\]$/', '', $name);
    $id             = $id             ?? $cleanName;
    $label          = $label          ?? '';
    $labelId        = $labelId        ?? null;
    $value          = $value          ?? old($cleanName);
    $placeholder    = $placeholder    ?? $label;
    $icon           = $icon           ?? null;
    $badge          = $badge          ?? null;
    $badgeVariant   = $badgeVariant   ?? 'primary';
    $required       = $required       ?? false;
    $disabled       = $disabled       ?? false;
    $readonly       = $readonly       ?? false;
    $maxlength      = $maxlength      ?? null;
    $minlength      = $minlength      ?? null;
    $min            = $min            ?? null;
    $max            = $max            ?? null;
    $step           = $step           ?? null;
    $rows           = $rows           ?? 4;
    $charCount      = $charCount      ?? false;
    $hint           = $hint           ?? null;
    $prefix         = $prefix         ?? null;
    $suffix         = $suffix         ?? null;
    $inputClass     = $inputClass     ?? '';
    $wrapClass      = $wrapClass      ?? '';
    $selectClass    = $selectClass    ?? '';
    $options        = $options        ?? [];
    $optionNull     = $optionNull     ?? null;
    $prependOptions = $prependOptions ?? [];
    $multiple       = $multiple       ?? false;
    $radioLayout    = $radioLayout    ?? 'inline';
    $radioItemClass = $radioItemClass ?? 'custom-radio';
    $checked        = $checked        ?? null;
    $autocomplete   = $autocomplete   ?? null;
    $extraAttrs     = $extraAttrs     ?? '';

    $wrapperClasses = trim(
        'ff-field ff-field--' . $type
        . ($icon ? ' ff-field--has-icon' : '')
        . ($prefix ? ' ff-field--has-prefix' : '')
        . ($suffix ? ' ff-field--has-suffix' : '')
        . ($multiple ? ' ff-field--multiple' : '')
        . ' ' . $wrapClass
    );
    $counterId = $id . '_char_count';

    $ffNormalise = function (array $raw) use ($id) {
        $out = [];
        foreach ($raw as $k => $v) {
            if (is_array($v)) {
                $optValue = $v['value'] ?? $k;
                $out[] = [
                    'value'    => $optValue,
                    'label'    => $v['label']    ?? '',
                    'id'       => $v['id']       ?? ($id . '_' . $optValue),
                    'disabled' => $v['disabled'] ?? false,
                    'attrs'    => $v['attrs']    ?? ''];
            } else {
                $out[] = [
                    'value'    => $k,
                    'label'    => $v,
                    'id'       => $id . '_' . $k,
                    'disabled' => false,
                    'attrs'    => ''];
            }
        }
        return $out;
    };

    $normalizedOptions = $ffNormalise($options);
    $normalizedPrepend = $ffNormalise($prependOptions);

    $ffIsSelected = function ($optValue) use ($value, $multiple) {
        if ($multiple) {
            return is_array($value) && in_array((string)$optValue, array_map('strval', $value), true);
        }
        if (is_array($value)) {
            return in_array((string)$optValue, array_map('strval', $value), true);
        }
        return (string)$value === (string)$optValue;
    };

    if (is_array($value) && !$multiple && !in_array($type, ['select', 'radio', 'checkbox', 'switch'])) {
        $value = '';
    }

    $showCounter  = $charCount && $maxlength && in_array($type, ['text', 'textarea']);
@endphp

<div class="{{ $wrapperClasses }}" data-ff-field>
    @if($label && !in_array($type, ['checkbox', 'switch']))
        <label @if($labelId) id="{{ $labelId }}" @endif
               @if(!in_array($type, ['radio'])) for="{{ $id }}" @endif
               class="ff-field__label">
            <span class="ff-field__label-text">{{ $label }}@if($required)<span class="ff-field__required">*</span>@endif</span>
            @if($badge)
                <span class="ff-field__badge ff-field__badge--{{ $badgeVariant }}">{{ $badge }}</span>
            @endif
        </label>
    @endif

    <div class="ff-field__control">
        @if($icon && in_array($type, ['text','email','password','number','tel','url','search','date','datetime-local','time']))
            <span class="material-icons ff-field__icon">{{ $icon }}</span>
        @endif

        @if($prefix && !in_array($type, ['textarea','select','radio','checkbox','switch']))
            <span class="ff-field__affix ff-field__prefix">{{ $prefix }}</span>
        @endif

        @switch($type)
            @case('textarea')
                <textarea
                    name="{{ $name }}"
                    id="{{ $id }}"
                    class="form-control ff-field__input ff-field__textarea {{ $inputClass }}"
                    placeholder="{{ $placeholder }}"
                    rows="{{ $rows }}"
                    @if($maxlength) maxlength="{{ $maxlength }}" @endif
                    @if($minlength) minlength="{{ $minlength }}" @endif
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    @if($readonly) readonly @endif
                    @if($charCount) data-char-count data-char-count-target="#{{ $counterId }}" @endif
                    {!! $extraAttrs !!}>{{ $value }}</textarea>
                @break

            @case('select')
                <select
                    name="{{ $name }}"
                    id="{{ $id }}"
                    class="form-control ff-field__input ff-field__select {{ $selectClass }} {{ $inputClass }}"
                    @if($multiple) multiple @endif
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    {!! $extraAttrs !!}>
                    @if($optionNull !== null)
                        <option value="" {{ $value === null || $value === '' ? 'selected' : '' }} disabled>{{ $optionNull }}</option>
                    @endif
                    @foreach($normalizedPrepend as $opt)
                        <option value="{{ $opt['value'] }}"
                            {{ $ffIsSelected($opt['value']) ? 'selected' : '' }}
                            @if($opt['disabled']) disabled @endif
                            {!! $opt['attrs'] !!}>{{ $opt['label'] }}</option>
                    @endforeach
                    @foreach($normalizedOptions as $opt)
                        <option value="{{ $opt['value'] }}"
                            {{ $ffIsSelected($opt['value']) ? 'selected' : '' }}
                            @if($opt['disabled']) disabled @endif
                            {!! $opt['attrs'] !!}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                @break

            @case('radio')
                <div class="ff-field__radio-group ff-field__radio-group--{{ $radioLayout }}">
                    @foreach($normalizedOptions as $opt)
                        <div class="{{ $radioItemClass }} ff-field__radio-item">
                            <input type="radio"
                                   id="{{ $opt['id'] }}"
                                   name="{{ $name }}"
                                   value="{{ $opt['value'] }}"
                                   @if($ffIsSelected($opt['value'])) checked @endif
                                   @if($required) required @endif
                                   @if($disabled || $opt['disabled']) disabled @endif
                                   {!! $opt['attrs'] !!}>
                            <label for="{{ $opt['id'] }}">{{ $opt['label'] }}</label>
                        </div>
                    @endforeach
                </div>
                @break

            @case('checkbox')
            @case('switch')
                <label class="ff-field__check {{ $type === 'switch' ? 'ff-field__check--switch' : '' }}">
                    <input
                        type="checkbox"
                        name="{{ $name }}"
                        id="{{ $id }}"
                        value="{{ $value ?? 1 }}"
                        class="{{ $inputClass }}"
                        @if(!empty($checked)) checked @endif
                        @if($required) required @endif
                        @if($disabled) disabled @endif
                        {!! $extraAttrs !!}>
                    <span class="ff-field__check-label">{{ $label }}@if($required)<span class="ff-field__required">*</span>@endif</span>
                </label>
                @break

            @default
                <input
                    type="{{ $type }}"
                    name="{{ $name }}"
                    id="{{ $id }}"
                    value="{{ $value }}"
                    class="form-control ff-field__input {{ $inputClass }}"
                    placeholder="{{ $placeholder }}"
                    @if($maxlength) maxlength="{{ $maxlength }}" @endif
                    @if($minlength) minlength="{{ $minlength }}" @endif
                    @if($min !== null) min="{{ $min }}" @endif
                    @if($max !== null) max="{{ $max }}" @endif
                    @if($step !== null) step="{{ $step }}" @endif
                    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                    @if($required) required @endif
                    @if($disabled) disabled @endif
                    @if($readonly) readonly @endif
                    @if($charCount && $maxlength) data-char-count data-char-count-target="#{{ $counterId }}" @endif
                    {!! $extraAttrs !!}>
        @endswitch

        @if($suffix && !in_array($type, ['textarea','select','radio','checkbox','switch']))
            <span class="ff-field__affix ff-field__suffix">{{ $suffix }}</span>
        @endif

        @if($type === 'password')
            <span class="material-icons ff-field__toggle-password togglePassword"
                  data-ff-toggle-password="#{{ $id }}">visibility_off</span>
        @endif
    </div>

    <div class="ff-field__footer">
        <div class="ff-field__messages">
            @if($hint)<small class="ff-field__hint">{{ $hint }}</small>@endif
            @error($cleanName)
                <div class="ff-field__error">{{ $message }}</div>
            @enderror
        </div>
        @if($showCounter)
            <small class="ff-field__counter">
                <span id="{{ $counterId }}">{{ mb_strlen((string)($value ?? '')) }}</span>/{{ $maxlength }}
            </small>
        @endif
    </div>
</div>
