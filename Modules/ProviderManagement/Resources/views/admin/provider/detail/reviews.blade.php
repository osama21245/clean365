@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@push('css_or_js')
    <style>
        .review-toggle-button {
            border: 0;
            background: transparent;
            color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 0;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
        }

        .review-toggle-button__icon {
            font-size: 18px;
            line-height: 1;
            font-weight: 700;
            margin-top: 1px;
        }

        .review-toggle-button--icon {
            min-width: 24px;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            @include('providermanagement::admin.provider.detail._tabs')

            <div class="tab-content">
                <div class="tab-pane fade show active" id="review-tab-pane">
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <div class="row align-items-center">
                                <div class="col-lg-5 mb-30 mb-lg-0 d-flex justify-content-center">
                                    <div class="rating-review">
                                        <h2 class="rating-review__title">
                                            <span class="rating-review__out-of">{{$provider->avg_rating}}</span>/5
                                        </h2>
                                        <div class="rating">
                                            <span
                                                class="{{$provider->avg_rating>=1?'material-icons':'material-symbols-outlined'}}">{{$provider->avg_rating>=1?'star':'grade'}}</span>
                                            <span
                                                class="{{$provider->avg_rating>=2?'material-icons':'material-symbols-outlined'}}">{{$provider->avg_rating>=2?'star':'grade'}}</span>
                                            <span
                                                class="{{$provider->avg_rating>=3?'material-icons':'material-symbols-outlined'}}">{{$provider->avg_rating>=3?'star':'grade'}}</span>
                                            <span
                                                class="{{$provider->avg_rating>=4?'material-icons':'material-symbols-outlined'}}">{{$provider->avg_rating>=4?'star':'grade'}}</span>
                                            <span
                                                class="{{$provider->avg_rating>=5?'material-icons':'material-symbols-outlined'}}">{{$provider->avg_rating>=5?'star':'grade'}}</span>
                                        </div>
                                        <div class="rating-review__info d-flex flex-wrap gap-3">
                                            @php($total_review_count = $provider->reviews->where('is_active', 1)->whereNotNull('review_comment')->count())
                                            @php($totalReviews = $provider->reviews->where('is_active', 1)->whereNotNull('review_rating')->count())
                                            <span>{{ translate(':count ratings', ['count' => $totalReviews]) }}</span>
                                            <span>{{ translate(':count reviews', ['count' => $total_review_count]) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <ul class="common-list common-list__style2 after-none gap-10">
                                        <li>
                                            <span class="review-name">{{translate('excellent')}}</span>
                                            @php($excellent_count=$provider->reviews->where('is_active', 1)->where('review_rating',5)->count())
                                            @php($excellent=(divnum($excellent_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$excellent}}%"
                                                     aria-valuenow="{{$excellent}}" aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$excellent_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('good')}}</span>
                                            @php($good_count=$provider->reviews->where('is_active', 1)->where('review_rating',4)->count())
                                            @php($good=(divnum($good_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{$good}}%"
                                                     aria-valuenow="{{$good}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$good_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('avarage')}}</span>
                                            @php($average_count=$provider->reviews->where('is_active', 1)->where('review_rating',3)->count())
                                            @php($average=(divnum($average_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$average}}%"
                                                     aria-valuenow="{{$average}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$average_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('below_avarage')}}</span>
                                            @php($below_average_count=$provider->reviews->where('is_active', 1)->where('review_rating',2)->count())
                                            @php($below_average=(divnum($below_average_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{$below_average}}%"
                                                     aria-valuenow="{{$below_average}}" aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$below_average_count}}</span>
                                        </li>
                                        <li>
                                            <span class="review-name">{{translate('poor')}}</span>
                                            @php($poor_count=$provider->reviews->where('is_active', 1)->where('review_rating',1)->count())
                                            @php($poor=(divnum($poor_count,$total_review_count))*100)
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{$poor}}%"
                                                     aria-valuenow="{{$poor}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="review-count">{{$poor_count}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end border-bottom pb-2 mb-10">
                        <div class="d-flex gap-2 fw-medium pe--4">
                            <span class="opacity-75">{{translate('Total_Reviews')}}:</span>
                            <span class="title-color">{{$reviews->total()}}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}?web_page={{$webPage}}"
                                      class="search-form search-form_style-two"
                                      method="POST">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search??''}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('search')}}</button>
                                </form>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="dropdown">
                                        <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle"
                                                data-bs-toggle="dropdown">
                                            <span class="material-icons">file_download</span> {{translate('download')}}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                            <li><a class="dropdown-item"
                                                   href="{{route('admin.provider.reviews.download',['search'=>$search, 'provider_id' => request()->id])}}">{{translate('excel')}}</a>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-capitalize">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Review ID')}}</th>
                                        <th>{{translate('reviewer')}}</th>
                                        <th>{{translate('date')}}</th>
                                        <th>{{translate('ratings')}}</th>
                                        <th>{{translate('reviews')}}</th>
                                        <th>{{translate('reply')}}</th>
                                        <th>{{translate('status')}}</th>
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($reviews as $bookingId => $review)
                                        @if($review->reviews->count() > 1)
                                            @php($getReviewInfo = $review->reviews->first())
                                            <tr class="clickable-row" data-target-class="review-group-{{$bookingId}}" aria-expanded="false">
                                                <td>{{$bookingId+$reviews?->firstItem()}}</td>
                                                <td>
                                                    {{ Str::limit($review->reviews->pluck('readable_id')->implode(', '), 18) }}
                                                </td>
                                                <td>
                                                    @if(isset($getReviewInfo->customer))
                                                        <span class="line-limit-1 lh-1">{{$getReviewInfo->customer->first_name . ' ' .$getReviewInfo->customer->last_name}}</span>

                                            <span>{{ translate('Booking ID #:id', ['id' => $review->readable_id ?? 'N/A']) }}</span>
                                                    @else
                                                        <span
                                                            class="opacity-50">{{translate('Customer_not_available')}}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="min-w-120">
                                                        {{ format_time_by_business_settings($getReviewInfo->created_at, 'd-M-Y') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15"
                                                             viewBox="0 0 14 15" fill="none">
                                                            <path
                                                                d="M7 1.81445L8.854 5.76398L13 6.4012L10 9.47376L10.708 13.8145L7 11.764L3.292 13.8145L4 9.47376L1 6.4012L5.146 5.76398L7 1.81445Z"
                                                                fill="#FFB900" stroke="#FFB900" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <span>{{ number_format($review->reviews->pluck('review_rating')->avg(),1) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="review-toggle-button"
                                                            data-collapsed-text="{{translate('See All')}}"
                                                            data-expanded-text="{{translate('See Less')}}"
                                                            aria-expanded="false">
                                                        <span class="review-toggle-button__text">{{translate('See All')}}</span>
                                                        <span class="material-symbols-outlined review-toggle-button__icon">keyboard_arrow_down</span>
                                                    </button>
                                                </td>
                                                <td>
                                                    <button type="button" class="review-toggle-button"
                                                            data-collapsed-text="{{translate('See All')}}"
                                                            data-expanded-text="{{translate('See Less')}}"
                                                            aria-expanded="false">
                                                        <span class="review-toggle-button__text">{{translate('See All')}}</span>
                                                        <span class="material-symbols-outlined review-toggle-button__icon">keyboard_arrow_down</span>
                                                    </button>
                                                </td>
                                                <td>
                                                    <button type="button" class="review-toggle-button review-toggle-button--icon"
                                                            data-collapsed-text="{{translate('See All')}}"
                                                            data-expanded-text="{{translate('See Less')}}"
                                                            aria-label="{{translate('See All')}}"
                                                            aria-expanded="false">
                                                        <span class="material-symbols-outlined review-toggle-button__icon">keyboard_arrow_down</span>
                                                    </button>
                                                </td>
                                                <td>
                                                    <button type="button" class="review-toggle-button review-toggle-button--icon"
                                                            data-collapsed-text="{{translate('See All')}}"
                                                            data-expanded-text="{{translate('See Less')}}"
                                                            aria-label="{{translate('See All')}}"
                                                            aria-expanded="false">
                                                        <span class="material-symbols-outlined review-toggle-button__icon">keyboard_arrow_down</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            @foreach($review->reviews as $key => $providerReview)
                                                <tr class="review-group-{{$bookingId}} d-none">
                                                    <td></td>
                                                    <td>{{ $providerReview->readable_id == 0 ? 'N/A' : $providerReview->readable_id }}</td>
                                                    <td>
                                                        <div class="min-w-180 text-center">
                                                            @if(isset($providerReview->service))
                                                                <img class="img-fluid rounded object-fit-cover" src="{{$providerReview->service->cover_image_full_path}}" alt="" width="56" height="56">
                                                                <span class="d-block mt-2 text-muted">{{ Str::limit($providerReview->service->name, 30) }}</span>
                                                            @endif
                                                            @if(isset($providerReview->customer))
                                                                <span class="d-block line-limit-1">{{$providerReview->customer->first_name . ' ' .$providerReview->customer->last_name}}</span>
                                                            @else
                                                                <span class="opacity-50 d-block mt-2">{{translate('Customer_not_available')}}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="min-w-120">
                                                            {{ format_time_by_business_settings($providerReview->created_at, 'd-M-Y') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1 align-items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                                                <path d="M7 1.81445L8.854 5.76398L13 6.4012L10 9.47376L10.708 13.8145L7 11.764L3.292 13.8145L4 9.47376L1 6.4012L5.146 5.76398L7 1.81445Z" fill="#FFB900" stroke="#FFB900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                           <span>{{$providerReview->review_rating}}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if(filled($providerReview->review_comment))
                                                            <p class="mb-0 line-limit-2 max-w-200 min-w-200 d-inline-block"
                                                               data-bs-custom-class="review-tooltip"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               data-bs-container="body"
                                                               title="{{$providerReview->review_comment}}">
                                                                {{ Str::limit($providerReview->review_comment, 100) }}
                                                            </p>
                                                        @else
                                                            <span class="badge badge-soft-secondary text-dark">{{ translate('No review yet') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(filled($providerReview->reviewReply?->reply))
                                                            <p class="mb-0 line-limit-2 max-w-200 min-w-120 d-inline-block"
                                                               data-bs-custom-class="review-tooltip"
                                                               data-bs-toggle="tooltip"
                                                               data-bs-placement="top"
                                                               data-bs-container="body"
                                                               title="{{$providerReview->reviewReply?->reply}}">
                                                                {{ Str::limit($providerReview->reviewReply?->reply, 100) }}
                                                            </p>
                                                        @else
                                                            <span class="badge badge-soft-secondary text-dark">{{ translate('No reply yet') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($providerReview->review_comment))
                                                            <label class="switcher">
                                                                <input class="switcher_input status-update"
                                                                       type="checkbox"
                                                                       id="review-status-{{$providerReview->id}}"
                                                                       {{$providerReview->is_active ? 'checked' : ''}}
                                                                       data-status="{{$providerReview->id}}">
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($providerReview->review_comment))
                                                            <div class="d-flex gap-2 justify-content-center">
                                                                <button class="action-btn btn--light-primary fw-medium text-capitalize fz-14" data-bs-toggle="modal" id="replyModalBtn"
                                                                        data-bs-target="#replyModal"
                                                                        data-booking_id ="{{$providerReview->booking->readable_id}}"
                                                                        data-readable_id ="{{$providerReview->readable_id}}"
                                                                        data-service_name="{{$providerReview->service->name}}"
                                                                        data-service_img="{{$providerReview->service->cover_image_full_path}}"
                                                                        data-review="{{$providerReview->review_comment ?? translate('No review yet')}}"
                                                                        data-review_reply="{{$providerReview->reviewReply?->reply ?? translate('No reply yet')}}"
                                                                        data-variant_key="{{ $providerReview->service?->bookings[0]?->variant_key }}"
                                                                >
                                                                    <span class="material-icons">visibility</span>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @php($getReview = $review->reviews->first())
                                            <tr>
                                                <td>{{$bookingId+$reviews?->firstItem()}}</td>
                                                <td>{{ $getReview->readable_id == 0 ? 'N/A' : $getReview->readable_id }}</td>
                                                <td>
                                                    <div class="min-w-120">
                                                        @if(isset($review->customer))
                                                            <span class="line-limit-1 lh-1">{{$review->customer->first_name . ' ' .$review->customer->last_name}}</span>

                                                            <span>{{ translate('Booking ID #:id', ['id' => $review->readable_id ?? 'N/A']) }}</span>
                                                        @else
                                                            <span
                                                                class="opacity-50">{{translate('Customer_not_available')}}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="min-w-120">
                                                        {{ format_time_by_business_settings($getReview->created_at, 'd-M-Y') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15"
                                                             viewBox="0 0 14 15" fill="none">
                                                            <path
                                                                d="M7 1.81445L8.854 5.76398L13 6.4012L10 9.47376L10.708 13.8145L7 11.764L3.292 13.8145L4 9.47376L1 6.4012L5.146 5.76398L7 1.81445Z"
                                                                fill="#FFB900" stroke="#FFB900" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <span>{{$getReview->review_rating}}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if(filled($getReview->review_comment))
                                                        <p class="mb-0 line-limit-2 max-w-200 min-w-200 d-inline-block"
                                                           data-bs-custom-class="review-tooltip"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           data-bs-container="body"
                                                           title="{{$getReview->review_comment}}">
                                                            {{ Str::limit($getReview->review_comment, 100) }}
                                                        </p>
                                                    @else
                                                        <span class="badge badge-soft-secondary text-dark">{{ translate('No review yet') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(filled($getReview->reviewReply?->reply))
                                                        <p class="mb-0 line-limit-2 max-w-200 min-w-120 d-inline-block"
                                                           data-bs-custom-class="review-tooltip"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           data-bs-container="body"
                                                           title="{{$getReview->reviewReply?->reply}}">
                                                            {{ Str::limit($getReview->reviewReply?->reply, 100) }}
                                                        </p>
                                                    @else
                                                        <span class="badge badge-soft-secondary text-dark">{{ translate('No reply yet') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($getReview->review_comment))
                                                        <label class="switcher">
                                                            <input class="switcher_input status-update"
                                                                   type="checkbox"
                                                                   id="review-status-{{$getReview->id}}"
                                                                   {{$getReview->is_active ? 'checked' : ''}}
                                                                   data-status="{{$getReview->id}}">
                                                            <span class="switcher_control"></span>
                                                        </label>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($getReview->review_comment))
                                                        <div class="d-flex gap-2 justify-content-center">
                                                            <button
                                                                class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                data-bs-toggle="modal" id="replyModalBtn"
                                                                data-bs-target="#replyModal"
                                                                data-booking_id="{{$getReview?->booking?->readable_id}}"
                                                                data-readable_id="{{$getReview->readable_id}}"
                                                                data-service_name="{{$getReview->service->name}}"
                                                                data-service_img="{{$getReview->service->cover_image_full_path}}"
                                                                data-review="{{$getReview->review_comment ?? translate('No review yet')}}"
                                                                data-review_reply="{{$getReview->reviewReply?->reply ?? translate('No reply yet')}}"
                                                                data-variant_key="{{ $getReview->booking?->detail[0]?->variant_key }}"
                                                            >
                                                                <span class="material-icons">visibility</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        @include('adminmodule::layouts.partials.components._empty-state', [
                                            'variant' => filled($search ?? null) ? 'search' : 'list'])
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $reviews->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <div class="p-3 pt-0">
                        <div class="d-flex gap-3">
                            <img src="" class="rounded aspect-square object-fit-cover" width="80" alt="Service Image">
                            <div class="w-0 flex-grow-1">
                                <div class="mb-2">
                                    <span>{{translate('Booking ID #')}}</span> <label class="booking_id"></label>
                                </div>
                                <h5 class="service_name"></h5>
                                <div class="mt-2">
                                    <span class="variant_key"></span>
                                </div>
                            </div>
                        </div>
                        <div class="review_section mb-3 mt-3">
                            <h4 class="mb-2">{{translate('Review')}}</h4>
                            <div class="p-3 rounded bg--secondary">
                                <p class="review_content"></p>
                            </div>
                        </div>
                        <div class="reply_section">
                            <div>
                                <h4 class="mb-3">{{translate('Reply')}}</h4>
                                <div class="form-group">
                                    <textarea id="reply_content" class="form-control" name="reply_content" rows="4"
                                              readonly disabled></textarea>
                                    <input type="hidden" class="form-control" name="readable_id" value="">
                                </div>
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
        "use strict"

        document.addEventListener('DOMContentLoaded', function () {
            var clickableRows = document.querySelectorAll('.clickable-row');

            function updateReviewToggleButtons(row, expanded) {
                row.querySelectorAll('.review-toggle-button').forEach(function (button) {
                    var text = button.querySelector('.review-toggle-button__text');
                    var icon = button.querySelector('.review-toggle-button__icon');

                    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                    if (text) {
                        text.textContent = expanded ? button.dataset.expandedText : button.dataset.collapsedText;
                    }

                    if (button.dataset.collapsedText && button.dataset.expandedText) {
                        button.setAttribute('aria-label', expanded ? button.dataset.expandedText : button.dataset.collapsedText);
                    }

                    if (icon) {
                        icon.textContent = expanded ? 'keyboard_arrow_up' : 'keyboard_arrow_down';
                    }
                });
            }

            clickableRows.forEach(function (row) {
                updateReviewToggleButtons(row, row.getAttribute('aria-expanded') === 'true');

                row.addEventListener('click', function (event) {
                    if (event.target.closest('.status-update, .action-btn')) {
                        return;
                    }

                    var targetClass = row.getAttribute('data-target-class');
                    if (!targetClass) {
                        return;
                    }

                    document.querySelectorAll('.' + targetClass).forEach(function (reviewRow) {
                        reviewRow.classList.toggle('d-none');
                    });

                    var expanded = row.getAttribute('aria-expanded') === 'true';
                    var nextExpanded = !expanded;

                    row.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
                    updateReviewToggleButtons(row, nextExpanded);
                });
            });
        });

        $('.status-update').on('click', function () {
            let $this = $(this);
            let itemId = $(this).data('status');
            let initialState = $this.prop('checked');
            let route = '{{ route('admin.service.review-status-update', ['id' => ':itemId']) }}';
            route = route.replace(':itemId', itemId);
            route_alert_reload(route, '{{ translate('want_to_update_status') }}', true, initialState ? 1 : 0, 'review-status-' + itemId);
        })

        $('#replyModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            const serviceImg = button.data('service_img');
            const serviceName = button.data('service_name');
            const bookingID = button.data('booking_id');
            const readableID = button.data('readable_id');
            const review = button.data('review');
            const reviewReply = button.data('review_reply');
            const variantKey = button.data('variant_key');
            const action = button.data('action');

            modal.find('.service_name').text(serviceName);
            modal.find('.variant_key').text(variantKey);
            modal.find('.booking_id').text(bookingID);
            modal.find('.review_content').text(review);
            modal.find('img').attr('src', serviceImg);

            modal.find('textarea[name=reply_content]').val(reviewReply);
            modal.find('input[name=readable_id]').val(readableID);
            modal.find('form').attr('action', action);
        });
    </script>

@endpush
