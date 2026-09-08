<tr id="service-row--{{$data['variant_key']}}">
    <td class="text-wrap ps-lg-3">
        <div class="d-flex flex-column">
            <a href="{{route('admin.service.detail',[$data['service_id']])}}"
               class="fw-bold">{{Str::limit($data['service_name'], 30)}}</a>
            <div>{{Str::limit($data['variant_key'], 50)}}</div>
        </div>
    </td>
    <td id="service-cost-{{$data['variant_key']}}">{{with_currency_symbol($data['service_cost'])}}</td>
    <td>
        <input type="number" min="1" name="qty[]" class="form-control booking-service-qty-input dark-color-bo m-auto"
               id="qty-{{$data['variant_key']}}" value="{{$data['quantity']}}"
               oninput="this.value = this.value.replace(/[^0-9]/g, '');" readonly>
    </td>
    <td id="discount-amount-{{$data['variant_key']}}">
        <span class="discount-amount-value">{{$data['total_discount_amount']}}</span>
        <br><span class="fz-12 text-capitalize discount-type-label">{{!empty($data['discount_type']) ? translate($data['discount_type']) : ''}}</span>
    </td>
    <td id="total-cost-{{$data['variant_key']}}">{{with_currency_symbol($data['total_cost'])}}</td>
    <td>
        <div class="d-flex justify-content-center">
            <span class="material-icons text-danger cursor-pointer remove-service-row"
                  data-row="service-row--{{$data['variant_key']}}">delete
            </span>
        </div>
    </td>
    <input type="hidden" name="service_ids[]" value="{{$data['service_id']}}">
    <input type="hidden" name="variant_keys[]" value="{{$data['variant_key']}}">
</tr>

<script>
    "use strict";

    $(".remove-service-row").on('click', function (){
        let row = $(this).data('row');
        removeServiceRow(row)
    })
</script>
