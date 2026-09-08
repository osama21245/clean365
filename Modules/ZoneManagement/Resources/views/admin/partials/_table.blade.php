<div class="table-responsive">
    @php
        $zoneTableColspan = 4;
    @endphp
    @can('zone_manage_status')
        @php
            $zoneTableColspan++;
        @endphp
    @endcan
    @canany(['zone_delete', 'zone_update'])
        @php
            $zoneTableColspan++;
        @endphp
    @endcanany
    <table id="example" class="table align-middle">
        <thead>
        <tr>
            <th>{{translate('SL')}}</th>
            <th>{{translate('zone_name')}}</th>
            <th>{{translate('providers')}}</th>
            <th>{{translate('Category')}}</th>
            @can('zone_manage_status')
                <th>{{translate('status')}}</th>
            @endcan
            @canany(['zone_delete', 'zone_update'])
                <th>{{translate('action')}}</th>
            @endcan
        </tr>
        </thead>
        <tbody>
        @forelse($zones as $key=>$zone)
            <tr>
                <td>{{$key+$zones->firstItem()}}</td>
                <td>{{$zone->name}}</td>
                <td>{{$zone->providers_count}}</td>
                <td>{{$zone->categories_count}}</td>
                @can('zone_manage_status')
                    <td>
                        <label class="switcher">
                            <input class="switcher_input status-update"
                                   data-id="{{$zone->id}}"
                                   type="checkbox" {{$zone->is_active?'checked':''}}>
                            <span class="switcher_control"></span>
                        </label>
                    </td>
                @endcan
                @canany(['zone_delete', 'zone_update'])
                    <td>
                        <div class="d-flex gap-2">
                            @can('zone_update')
                                <a href="{{route('admin.zone.edit',[$zone->id])}}"
                                   class="action-btn btn--light-primary demo_check">
                                    <span class="material-icons">edit</span>
                                </a>
                            @endcan
                            @can('zone_delete')
                                <button type="button"
                                        data-id="delete-{{$zone->id}}"
                                        data-message="{{translate('want_to_delete_this_zone')}}?"
                                        class="action-btn btn--danger {{ env('APP_ENV') != 'demo' ? 'form-alert' : 'demo_check' }}"
                                        style="--size: 30px">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                                <form
                                    action="{{route('admin.zone.delete',[$zone->id])}}"
                                    method="post" id="delete-{{$zone->id}}"
                                    class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endcan
                        </div>
                    </td>
                @endcan
            </tr>
        @empty
            @include('adminmodule::layouts.partials.components._empty-state', [
                'colspan' => $zoneTableColspan,
                'variant' => request()->filled('search') ? 'search' : 'list'])
        @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end">
    {!! $zones->links() !!}
</div>
