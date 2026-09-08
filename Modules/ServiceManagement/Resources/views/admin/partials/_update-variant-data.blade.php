
@if(isset($variants))
    @php($variant_keys = $variants->pluck('variant_key')->unique()->toArray())
    @foreach($variant_keys as $key=>$item)
        <tr>
            <th scope="row">
                {{str_replace('-',' ',$item)}}
                <input name="variants[]" value="{{$item}}" class="hide-div">
            </th>
            <td>
                <input type="number"
                       value="{{$variants->where('price','>',0)->where('variant_key',$item)->first()->price??0}}"
                       class="theme-input-style" id="default-set-{{$key}}-update"
                       onkeyup="set_update_values('{{$key}}')">
            </td>
            @foreach($zones as $zone)
                <td>
                    <input type="number" name="{{$item}}_{{$zone->id}}_price"
                           value="{{$variants->where('zone_id',$zone->id)->where('variant_key',$item)->first()->price??0}}"
                           class="theme-input-style default-get-{{$key}}-update">
                </td>
            @endforeach
            <td>
                <a class="btn btn-sm btn--danger service-ajax-remove-variant"
                   data-route="{{ route('admin.service.ajax-delete-db-variant', ['service_id' => $variants->first()->service_id, 'variant_key' => $item]) }}"
                   data-id="variation-update-table" data-item="{{count($variant_keys)}}" >
                    <span class="material-icons m-0">delete</span>
                </a>
            </td>
        </tr>
    @endforeach
@endif

<script>
    "use strict";

    function set_update_values(key) {
        var setEl = document.getElementById('default-set-' + key + '-update');
        if (!setEl) return;
        var setValue = setEl.value;
        document.querySelectorAll('.default-get-' + key + '-update').forEach(function (element) {
            element.value = setValue;
        });
    }

    (function () {
        var elements = document.querySelectorAll('.service-ajax-remove-variant');
        elements.forEach(function (element) {
            if (element.dataset.removeBound === '1') return;
            element.dataset.removeBound = '1';
            element.addEventListener('click', function () {
                var route = this.getAttribute('data-route');
                var id = this.getAttribute('data-id');
                ajax_remove_variant(route, id);
            });
        });
    })();
</script>
