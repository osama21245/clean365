@php
    $discount = $discount ?? null;
    $hasDiscount = old('has_discount', $discount?->is_active ? '1' : '0') == '1';
    $amountType = old('discount_amount_type', $discount->discount_amount_type ?? 'percent');
@endphp

<div class="col-12 mt-3">
    <div class="p-xxl-20 p-12px bg-light rounded border">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h5 class="mb-0">{{ translate('Service Discount') }}</h5>
            <div class="form-check form-switch m-0">
                <input type="hidden" name="has_discount" value="0">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="has_discount" id="has_discount" value="1"
                       {{ $hasDiscount ? 'checked' : '' }}>
                <label class="form-check-label" for="has_discount">{{ translate('Enable Discount') }}</label>
            </div>
        </div>

        <div id="service-discount-fields" class="{{ $hasDiscount ? '' : 'd-none' }}">
            <div class="row g-3">
                <div class="col-12">
                    <div class="mb-1 fw-medium">{{ translate('Discount Amount Type') }}</div>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div class="custom-radio">
                            <input type="radio" id="service_discount_percent" name="discount_amount_type"
                                   value="percent" {{ $amountType == 'percent' ? 'checked' : '' }}>
                            <label for="service_discount_percent">{{ translate('Percentage') }}</label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio" id="service_discount_amount" name="discount_amount_type"
                                   value="amount" {{ $amountType == 'amount' ? 'checked' : '' }}>
                            <label for="service_discount_amount">{{ translate('Fixed Amount') }}</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <label class="fs-14 fw-medium mb-2" id="discount_amount_label">
                        {{ translate('Discount Amount') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" name="discount_amount" id="discount_amount"
                           min="0" step="any"
                           value="{{ old('discount_amount', $discount->discount_amount ?? '') }}"
                           placeholder="{{ translate('Enter Discount Amount') }}">
                </div>

                <div class="col-lg-4 col-md-6">
                    <label class="fs-14 fw-medium mb-2">
                        {{ translate('Min Purchase') }} ({{ currency_symbol() }})
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" name="min_purchase" id="min_purchase"
                           min="0" step="any"
                           value="{{ old('min_purchase', $discount->min_purchase ?? 1) }}"
                           placeholder="{{ translate('Enter Min Purchase') }}">
                </div>

                <div class="col-lg-4 col-md-6" id="max_discount_amount_wrap">
                    <label class="fs-14 fw-medium mb-2">
                        {{ translate('Max Discount') }} ({{ currency_symbol() }})
                    </label>
                    <input type="number" class="form-control" name="max_discount_amount" id="max_discount_amount"
                           min="0" step="any"
                           value="{{ old('max_discount_amount', $discount->max_discount_amount ?? '') }}"
                           placeholder="{{ translate('Enter Max Discount') }}">
                </div>

                <div class="col-lg-6 col-md-6">
                    <label class="fs-14 fw-medium mb-2">
                        {{ translate('Start Date') }} <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" name="discount_start_date" id="discount_start_date"
                           value="{{ old('discount_start_date', optional($discount)->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                </div>

                <div class="col-lg-6 col-md-6">
                    <label class="fs-14 fw-medium mb-2">
                        {{ translate('End Date') }} <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" name="discount_end_date" id="discount_end_date"
                           value="{{ old('discount_end_date', optional($discount)->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>
</div>
