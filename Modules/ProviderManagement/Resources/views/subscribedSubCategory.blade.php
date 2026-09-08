@extends('providermanagement::layouts.master')

@section('title',translate('My_Subscriptions'))

@section('content')
    @php
        $subscribedSubCategoryEmptyState = view('adminmodule::layouts.partials.components._empty-state', [
            'colspan' => 5,
            'variant' => request()->filled('search') ? 'search' : 'list'])->render();
    @endphp
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('My_Subscriptions')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body pb-5">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between align-items-center">
                                <div class="d-flex gap-2 fw-medium">
                                    <span class="opacity-75">{{translate('Subscribed_Sub_categories')}}:</span>
                                    <span class="title-color" id="sub-category-total">{{$subscribedSubCategories->total()}}</span>
                                </div>

                                <form action="{{url()->current()}}"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search??''}}" name="search"
                                               placeholder="{{translate('search_by_Sub_Category')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </form>
                            </div>

                            <div class="table-responsive-md">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap align-middle">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Sub_Category_Name')}}</th>
                                        <th>{{translate('Category')}}</th>
                                        <th>{{translate('Services')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($subscribedSubCategories as $key=>$sub_category)
                                        <tr>
                                            <td>{{$subscribedSubCategories->firstitem()+$key}}</td>
                                            <td>{{ Str::limit($sub_category->sub_category['name']??translate('Unavailable'), 30) }}</td>
                                            <td>{{ Str::limit($sub_category->category['name']??translate('Unavailable'), 30) }}</td>
                                            <td>
                                                @if(count($sub_category->services) > 0)
                                                    <div
                                                        class="service-details-info-wrap d-inline-block position-relative cursor-pointer">
                                                        <div>{{ $sub_category->sub_category->services_count ?? 0 }}</div>
                                                        <div
                                                            class="service-details-info bg-dark p-2 rounded shadow">
                                                            @foreach($sub_category->services as $service)
                                                                <div class="media gap-2 align-items-center">
                                                                    <img width="40" class="rounded"
                                                                         src="{{$service->thumbnail_full_path}}"
                                                                         alt="{{translate('image')}}">

                                                                    <div class="media-body text-white">
                                                                        <h6 class="text-white">{{\Illuminate\Support\Str::limit($service->name,15)}}</h6>
                                                                        <div class="fs-10">{{translate('Up to: ')}}
                                                                            ${{ $service->variations->first()?->price }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ $sub_category->sub_category->services_count ?? 0 }}
                                                @endif

                                            </td>
                                            <td class="text-center">
                                                <form action="javascript:void(0)" method="post" class="hide-div"
                                                      id="form-{{$sub_category->id}}">
                                                    @csrf
                                                    @method('put')
                                                    <input name="sub_category_id"
                                                           value="{{$sub_category->sub_category_id}}">
                                                </form>
                                                <button type="button" class="btn btn-danger subscribe-btn"
                                                        id="button-{{$sub_category->id}}"
                                                        data-subcategory="{{$sub_category->id}}">
                                                    {{translate('unsubscribe')}}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => request()->filled('search') ? 'search' : 'list',
                                            'showButton' => true,
                                            'buttonTextKey' => 'Available Services',
                                            'buttonUrl' => route('provider.service.available')])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $subscribedSubCategories->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

    <script>
        "use strict";

        let subscriptionRequestInProgress = false;

        $('.subscribe-btn').off('click').on('click', function () {
            if (subscriptionRequestInProgress) {
                return;
            }

            let id = $(this).data('subcategory');
            update_subscription(id)
        });

        function update_subscription(id) {

            var form = $('#form-' + id)[0];
            var formData = new FormData(form);

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_update_subscription')}}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '{{translate('cancel')}}',
                confirmButtonText: '{{translate('yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    send_request(formData, id);
                }
            })
        }

        function update_view(id) {
            const subscribe_button = $('#button-' + id);
            const row = subscribe_button.closest('tr');
            remove_row(row);
            subscribe_button.blur();
        }

    function remove_row(row) {
        row.remove();
        update_total_count(-1);

        if ($('tbody tr').length === 0) {
            $('tbody').html(@json($subscribedSubCategoryEmptyState));
            window.syncTableEmptyStateColspan?.($('tbody')[0]);
        }
    }

        function update_total_count(delta) {
            const totalElement = $('#sub-category-total');
            const currentTotal = parseInt(totalElement.text(), 10) || 0;
            totalElement.text(Math.max(currentTotal + delta, 0));
        }


        function send_request(formData, id) {
            if (subscriptionRequestInProgress) {
                return;
            }

            subscriptionRequestInProgress = true;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('provider.service.update-subscription')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                beforeSend: function () {
                    $('.subscribe-btn').prop('disabled', true);
                    $('.preloader').show()
                },
                success: function (response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('successfully data fetched');
                        update_view(id)

                    } else if(response.response_code === 'default_204'){
                        toastr.warning('{{translate('this_category_is_not_available_in_your_zone')}}')

                    } else {
                        toastr.error('{{translate('your_subscription_package_category_limit_has_ended')}}');
                    }
                },
                error: function (response) {
                    toastr.error('server error')
                },
                complete: function () {
                    subscriptionRequestInProgress = false;
                    $('.subscribe-btn').prop('disabled', false);
                    $('.preloader').hide()
                }
            });
        }
    </script>

@endpush
