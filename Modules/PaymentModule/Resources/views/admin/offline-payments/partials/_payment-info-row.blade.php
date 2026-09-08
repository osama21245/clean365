@php
    $rowIndex   = $rowIndex   ?? 0;
    $titleValue = $titleValue ?? '';
    $dataValue  = $dataValue  ?? '';
@endphp

<div id="field-row-payment--{{ $rowIndex }}" class="field-row-payment body-bg rounded p-20">
    <div class="row g-3 align-items-start">
        <div class="col-md-5">
            @include('partials._form-field', [
                'type'        => 'text',
                'name'        => 'title[]',
                'id'          => 'payment_title_' . $rowIndex,
                'label'       => translate('Title'),
                'placeholder' => translate('Enter Title'),
                'required'    => true,
                'maxlength'   => 60,
                'charCount'   => true,
                'value'       => $titleValue,
                'wrapClass'   => 'mb-0'])
        </div>
        <div class="col-md-5">
            @include('partials._form-field', [
                'type'        => 'text',
                'name'        => 'data[]',
                'id'          => 'payment_data_' . $rowIndex,
                'label'       => translate('Data'),
                'placeholder' => translate('Enter Data'),
                'required'    => true,
                'maxlength'   => 120,
                'charCount'   => true,
                'value'       => $dataValue,
                'wrapClass'   => 'mb-0'])
        </div>
        <div class="col-md-2 d-flex justify-content-end align-items-start pt-md-4 mt-md-2">
            <button type="button" class="btn btn--danger w-30 h-30 p-0 remove-field-payment-btn rounded-1 d-center" data-counter-payment="{{ $rowIndex }}">
                <i class="material-symbols-outlined m-0 fz-20">delete</i>
            </button>
        </div>
    </div>
</div>
