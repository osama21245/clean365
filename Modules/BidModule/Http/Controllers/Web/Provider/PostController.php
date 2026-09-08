<?php

namespace Modules\BidModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\PostBid;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\CategoryManagement\Entities\Category;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController extends Controller
{
    public function __construct(
        private Post              $post,
        private SubscribedService $subscribed_service,
        private IgnoredPost       $ignored_post,
        private PostBid           $post_bid,
        private Category          $category,
    )
    {
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse|Renderable
     * @throws ValidationException
     */
    public function index(Request $request): Renderable|RedirectResponse
    {
        Validator::make($request->all(), [
            'type' => 'required|in:all,new_booking_request,placed_offer',
            'category_id' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'select_date' => 'nullable|string|in:all,today,this_week,this_month,custom_range'])->validate();

        $queryParams = $request->only([
            'search',
            'category_id',
            'start_date',
            'end_date',
            'select_date',
            'type'
        ]);

        $filterCounter = collect($queryParams)->filter(function ($value, $key) use ($queryParams) {
            if (($key === 'select_date' && $value === 'custom_range') || $key === 'type' || $key === 'search') {
                return false;
            }

            return !is_null($value) && $value !== '';
        })->count();

        $query_param = $queryParams;

        $subscribed_sub_categories = $this->subscribed_service
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

        $ignored_posts = $this->ignored_post->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['bids.provider', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('is_booked', 0)
            ->whereNotIn('id', $ignored_posts)
            ->whereIn('sub_category_id', $subscribed_sub_categories)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->whereBetween('created_at', [Carbon::now()->subDays($bidding_post_validity), Carbon::now()])
            ->when($request['type'] != 'all' && $request['type'] != 'new_booking_request', function ($query) use ($request) {
                $query->whereHas('bids', function ($query) use ($request) {
                    if ($request['type'] == 'placed_offer') {
                        $query->where('status', 'pending')->where('provider_id', $request->user()->provider->id);
                    } else if ($request['type'] == 'booking_placed') {
                        $query->where('status', 'accepted');
                    }
                });
            })
            ->when($request['type'] != 'all' && $request['type'] == 'new_booking_request', function ($query) use ($request) {
                if ($request->user()?->provider?->service_availability && (!$request->user()->provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $query->whereDoesntHave('bids', function ($query) use ($request) {
                        $query->where('provider_id', $request->user()->provider->id);
                    });
                } else {
                    $query->whereNull('id');
                }
            })
            ->when($request['type'] == 'all', function ($query) use ($request) {
                if (!$request->user()?->provider?->service_availability || ($request->user()->provider->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $query->whereHas('bids', function ($query) use ($request) {
                        if ($request['type'] == 'placed_offer') {
                            $query->where('status', 'pending')->where('provider_id', $request->user()->provider->id);
                        } else if ($request['type'] == 'booking_placed') {
                            $query->where('status', 'accepted');
                        }
                    });
                }
            })
            ->when($request->has('search') && $request['search'] != null, function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('customer', function ($query) use ($request, $keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('category_id') && $request['category_id'] != null, function ($query) use ($request) {
                $query->where('category_id', $request['category_id']);
            })
            ->when($request['select_date'] == 'custom_range' && $request->has('start_date') && $request['start_date'] != null && $request->has('end_date') && $request['end_date'] != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request['start_date'] . ' 00:00:00',
                    $request['end_date'] . ' 23:59:59'
                ]);
            })
            ->when($request['select_date'] == 'today', function ($query) {
                $query->whereDate('created_at', today());
            })
            ->when($request['select_date'] == 'this_week', function ($query) {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            })
            ->when($request['select_date'] == 'this_month', function ($query) {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($query_param);

        if ($request['type'] == 'all') {
            foreach ($posts as $key => $post) {
                if ($post->bids) {
                    foreach ($post->bids as $bid) {
                        if ($bid->status == 'denied') unset($posts[$key]);
                    }
                }
            }
        }

        $coordinates = auth()->user()->provider->coordinates ?? null;
        foreach ($posts as $post) {
            $distance = null;
            if (!is_null($coordinates) && $post->service_address) {
                $distance = get_distance(
                    [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                    [$post->service_address?->lat, $post->service_address?->lon]
                );
                $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
            }
            $post->distance = $distance;
        }

        $bid_offers_visibility_for_providers = business_config('bid_offers_visibility_for_providers', 'bidding_system')?->live_values;
        $type = $request['type'];
        $search = $request['search'];
        $category_id = $request->input('category_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $select_date = $request->input('select_date');

        $categories = collect();
        if ($this->category) {
            $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        }

        $this->post->where('is_checked', 0)->update(['is_checked' => 1]);
        return view('bidmodule::provider.customize-list', compact('posts', 'bid_offers_visibility_for_providers', 'type', 'search', 'category_id', 'start_date', 'end_date', 'select_date', 'queryParams', 'categories', 'filterCounter'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     * @throws ValidationException
     */
    public function export(Request $request): string|StreamedResponse
    {
        Validator::make($request->all(), [
            'type' => 'in:all,new_booking_request,placed_offer',
            'search' => 'max:255'
        ])->validate();

        $subscribed_sub_categories = $this->subscribed_service
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

        $ignored_posts = $this->ignored_post->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['bids', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->whereNotIn('id', $ignored_posts)
            ->whereIn('sub_category_id', $subscribed_sub_categories)
            ->whereBetween('created_at', [Carbon::now()->subDays($bidding_post_validity), Carbon::now()])
            ->when($request['type'] != 'all', function ($query) use ($request) {
                $query->where('is_booked', ($request['type'] == 'placed_offer' ? 1 : 0));
            })
            ->get();

        $formatted = $posts->map(function ($item, $key) {
            return [
                translate('SL') => $key + 1,
                translate('Booking ID') => $item->booking?->readable_id ?? '',
                translate('Customer_Name') => $item->customer ? trim(($item->customer->first_name ?? '') . ' ' . ($item->customer->last_name ?? '')) : '',
                translate('Customer_Phone') => $item->customer?->phone ?? '',
                translate('Booking Request Time') => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d h:i A') : '',
                translate('Service Time') => $item->booking_schedule ? \Carbon\Carbon::parse($item->booking_schedule)->format('d-m-Y h:i A') : '',
                translate('Category') => $item->category?->name ?? translate('Category not available'),
                translate('Other Provider Offering') => translate(':count Providers', ['count' => $item->bids?->count() ?? 0])];
        });

        $fileName = 'provider_customized_request_list_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($formatted))->download($fileName);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param $post_id
     * @return RedirectResponse|Renderable
     */
    public function details(Request $request, $post_id): Renderable|RedirectResponse
    {
        $post = $this->post
            ->with(['bids', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer', 'service_address'])
            ->where('id', $post_id)
            ->first();

        $coordinates = auth()->user()->provider->coordinates ?? null;
        $distance = null;
        if (!is_null($coordinates) && $post->service_address) {
            $distance = get_distance(
                [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                [$post->service_address?->lat, $post->service_address?->lon]
            );
            $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
        }

        if (!isset($post)) {
            Toastr::success(translate(DEFAULT_404['key']));
            return back();
        }

        $bid_offers_visibility_for_providers = business_config('bid_offers_visibility_for_providers', 'bidding_system')?->live_values;
        return view('bidmodule::provider.details', compact('post', 'bid_offers_visibility_for_providers', 'distance'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Application|Redirector|RedirectResponse
     * @throws ValidationException
     */
    public function updateStatus(Request $request, $id): Redirector|RedirectResponse|Application
    {
        Validator::make($request->all(), [
            'status' => 'in:accept,ignore',

            'offered_price' => $request['status'] == 'accept' ? 'required' : '',
            'provider_note' => ''])->validate();

        if ($request['status'] == 'accept') {
            $post_bid = $this->post_bid;
            $post_bid->offered_price = $request['offered_price'];
            $post_bid->provider_note = $request['provider_note'];
            $post_bid->status = 'pending';
            $post_bid->post_id = $id;
            $post_bid->provider_id = $request->user()->provider->id;
            $post_bid->save();

            Toastr::success(translate(DEFAULT_UPDATE_200['key']));
            return back();

        } else if ($request['status'] == 'ignore') {
            $this->ignored_post->updateOrCreate(
                ['post_id' => $id, 'provider_id' => $request->user()->provider->id], [
                'post_id' => $id
            ]);
        }

        return redirect(route('provider.booking.post.list', ['type' => 'all']));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function multiIgnore(Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'uuid'])->validate();

        foreach ($request->post_ids as $id) {
            IgnoredPost::updateOrCreate(
                ['post_id' => $id, 'provider_id' => auth()->user()->provider->id], [
                'post_id' => $id
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    public function withdraw($id, Request $request): RedirectResponse
    {
        $post_bids = $this->post_bid
            ->where('status', 'pending')
            ->where('post_id', $id)
            ->where('provider_id', auth()->user()->provider->id);

        if ($post_bids->count() < 1) {
            Toastr::success(translate(DEFAULT_404['key']));
            return back();
        }

        $post_bids->delete();

        Toastr::success(translate(DEFAULT_DELETE_200['key']));
        return back();
    }

    /**
     * @return void
     */
    public function check_all(): void
    {
        $this->post->where('is_checked', 0)->update(['is_checked' => 1]);
    }
}
