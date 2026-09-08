@php
    $rowIndex         = $rowIndex         ?? 0;
    $fieldNameValue   = $fieldNameValue   ?? '';
    $placeholderValue = $placeholderValue ?? '';
    $isRequired       = $isRequired       ?? 1;
@endphp

<div id="field-row-customer--{{ $rowIndex }}" class="field-row-customer body-bg rounded p-20 position-relative">
    <div class="row g-3 align-items-start">
        <div class="col-md-5">
            @include('partials._form-field', [
                'type'        => 'text',
                'name'        => 'field_name[' . $rowIndex . ']',
                'id'          => 'customer_field_name_' . $rowIndex,
                'label'       => translate('Input Field Name'),
                'placeholder' => translate('Enter Field Name'),
                'required'    => true,
                'maxlength'   => 60,
                'charCount'   => true,
                'value'       => $fieldNameValue,
                'wrapClass'   => 'mb-0'])
        </div>
        <div class="col-md-5">
            @include('partials._form-field', [
                'type'        => 'text',
                'name'        => 'placeholder[' . $rowIndex . ']',
                'id'          => 'customer_placeholder_' . $rowIndex,
                'label'       => translate('Placeholder'),
                'placeholder' => translate('Enter Placeholder Text'),
                'required'    => true,
                'maxlength'   => 120,
                'charCount'   => true,
                'value'       => $placeholderValue,
                'wrapClass'   => 'mb-0'])
        </div>
        <div class="col-md-2">
            <div class="form-check d-flex align-items-center gap-1 pt-md-4 mt-md-2">
                <input class="form-check-input" type="checkbox" value="1"
                       name="is_required[{{ $rowIndex }}]"
                       id="is_required_{{ $rowIndex }}"
                       {{ $isRequired ? 'checked' : '' }}>
                <label class="form-check-label" for="is_required_{{ $rowIndex }}">
                    {{ translate('Is Required') }}
                </label>
            </div>
        </div>
        <button type="button" class="btn btn--danger offline-delete-icon position-absolute top-0 end-3 w-30 h-30 p-0 remove-field-btn rounded-1 d-center" data-counter="{{ $rowIndex }}">
            <i class="material-symbols-outlined m-0 fz-20">delete</i>
        </button>
    </div>
</div>
