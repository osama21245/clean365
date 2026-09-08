<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;

use App\Traits\UploadSizeHelperTrait;
use Carbon\Carbon;
use Modules\AdminModule\Traits\AdminMenuWithRoutes;
use function auth;
use function view;
use function bcrypt;
use function response;
use function file_uploader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use function response_formatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Modules\UserManagement\Entities\User;
use Modules\BookingModule\Entities\Booking;
use Illuminate\Contracts\Support\Renderable;
use Modules\AdminModule\Services\AdvanceSearch;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Illuminate\Contracts\Foundation\Application;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\ProviderManagement\Entities\Provider;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\TransactionModule\Entities\Transaction;
use Modules\AdminModule\Entities\RouteSearchHistory;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\ServiceManagement\Entities\CustomerServiceSubscription;


class AdminController extends Controller
{
    use AdminMenuWithRoutes;
    protected Provider $provider;
    protected Account $account;
    protected Booking $booking;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected ChannelList $channelList;
    protected BookingDetailsAmount $booking_details_amount;
    protected $advanceSearchService;
    use AuthorizesRequests;
    use UploadSizeHelperTrait;
    public function __construct(ChannelList $channelList, Provider $provider, Service $service, Account $account, Booking $booking, User $user, Transaction $transaction, BookingDetailsAmount $booking_details_amount, AdvanceSearch $advanceSearchService)
    {
        $this->provider = $provider;
        $this->service = $service;
        $this->account = $account;
        $this->booking = $booking;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->channelList = $channelList;
        $this->booking_details_amount = $booking_details_amount;
        $this->advanceSearchService = $advanceSearchService;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function dashboard(Request $request, Transaction $transaction): View|Factory|Application
    {
        if (supervisorMode()) {
            return $this->companyDashboard($request, $transaction);
        }

        $baseQuery = BookingDetailsAmount::whereHas('booking', function ($query) use ($request) {
            $query->ofBookingStatus('completed');
        })->orWhereHas('repeat', function ($subQuery) {
            $subQuery->ofBookingStatus('completed');
        });
        //->sum('admin_commission');
        $admin_commission = $baseQuery->sum('admin_commission');
        $discount_by_admin = $baseQuery->sum('discount_by_admin');
        $coupon_discount_by_admin = $baseQuery->sum('coupon_discount_by_admin');
        $campaign_discount_by_admin = $baseQuery->sum('campaign_discount_by_admin');

        $commission_earning = $admin_commission - $discount_by_admin - $coupon_discount_by_admin - $campaign_discount_by_admin;

        $fee_amounts = $this->transaction->where('trx_type', TRX_TYPE['received_extra_fee'])->sum('credit');
        $subscription_amounts = $this->transaction->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift'])->sum('credit');

        $data = [];
        $data[] = [
            'top_cards' => [
                'total_commission_earning' => $commission_earning ?? 0,
                'total_fee_earning' => $fee_amounts ?? 0,
                'total_subscription_earning' => $subscription_amounts ?? 0,
                'total_system_earning' => $this->account->sum('received_balance') + $this->account->sum('total_withdrawn'),
                'total_customer' => $this->user->where(['user_type' => 'customer'])->count(),
                'total_provider' => $this->provider->where(['is_approved' => 1])->count(),
                'total_services' => $this->service->count()
            ]
        ];

        $total_earning = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                $query->ofBookingStatus('completed');
            })->orWhereHas('repeat', function ($subQuery) {
                $subQuery->ofBookingStatus('completed');
            })->get()->sum('admin_commission');

        $data[] = ['admin_total_earning' => $total_earning];

        $recent_transactions = $this->transaction
            ->with(['booking'])
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->take(5)
            ->get();
        $data[] = [
            'recent_transactions' => $recent_transactions,
            'this_month_trx_count' => $transaction
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count()
        ];

        $bookings = $this->booking->with([
            'detail.service' => function ($query) {
                $query->select('id', 'name', 'thumbnail');
            }
        ])
            ->where('booking_status', 'pending')
            ->take(5)->latest()->get();
        $data[] = ['bookings' => $bookings];

        $top_providers = $this->provider
            ->withCount(['reviews'])
            ->with(['owner', 'reviews'])
            ->ofApproval(1)
            ->take(5)->orderBy('avg_rating', 'DESC')->get();
        $data[] = ['top_providers' => $top_providers];

        $zone_wise_bookings = $this->booking
            ->with([
                'zone' => function ($query) {
                    $query->withoutGlobalScope('translate');
                }
            ])
            ->whereHas('zone', function ($query) {
                $query->ofStatus(1)->withoutGlobalScope('translate');
            })
            ->whereMonth('created_at', now()->month)
            ->select('zone_id', DB::raw('count(*) as total'))
            ->groupBy('zone_id')
            ->get();
        $data[] = ['zone_wise_bookings' => $zone_wise_bookings, 'total_count' => $this->booking->count()];

        $year = session()->has('dashboard_earning_graph_year') ? session('dashboard_earning_graph_year') : date('Y');
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })->orWhereHas('repeat', function ($subQuery) {
                $subQuery->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $all_earnings = [];
        if (empty($amounts) && !empty($fee_amounts)) {
            foreach ($fee_amounts as $key => $fee) {
                $all_earnings[$key] = $fee;
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        } else {
            foreach ($amounts as $amount) {
                foreach ($fee_amounts as $key => $fee) {
                    if ($amount['month'] == $fee['month']) {
                        $all_earnings[$key] = array_merge($amount, $fee);
                    }
                    if (!isset($all_earnings[$key])) {
                        $all_earnings[$key] = $amount;
                    }
                    if (!array_key_exists('fee', $all_earnings[$key])) {
                        $all_earnings[$key]['fee'] = 0;
                    }
                }
            }
        }

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($all_earnings as $key => $item) {
                if (isset($item['month']) && $item['month'] == $month) {
                    $admin_commission = $item['admin_commission'] ?? 0;
                    $itemFee = $item['fee'] ?? 0;

                    $chart_data['total_earning'][] = with_decimal_point($admin_commission + $itemFee);
                    $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                    $found = 1;
                    break;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
                $chart_data['commission_earning'][] = with_decimal_point(0);
            }
        }

        $extraData = $this->getTopDemandedData();
        return view('adminmodule::dashboard', array_merge(compact('data', 'chart_data'), $extraData));
    }

    /**
     * Company (supervisor-mode) dashboard: subscription packages + bookings from packages.
     */
    private function companyDashboard(Request $request, Transaction $transaction): View|Factory|Application
    {
        $subscriptionRevenue = (float) $this->booking
            ->whereNotNull('subscription_id')
            ->where('booking_status', 'completed')
            ->sum('total_booking_amount');

        $totalCompletedRevenue = (float) $this->booking
            ->where('booking_status', 'completed')
            ->sum('total_booking_amount');

        $activeSubscriptions = CustomerServiceSubscription::query()->where('status', 'active')->count();
        $packageBookingsCount = $this->booking->whereNotNull('subscription_id')->count();
        $completedPackageBookings = $this->booking
            ->whereNotNull('subscription_id')
            ->where('booking_status', 'completed')
            ->count();

        $data = [];
        $data[] = [
            'top_cards' => [
                'total_commission_earning' => $subscriptionRevenue,
                'total_fee_earning' => $activeSubscriptions,
                'total_subscription_earning' => $subscriptionRevenue,
                'total_system_earning' => $totalCompletedRevenue,
                'total_customer' => $this->user->where(['user_type' => 'customer'])->count(),
                'total_provider' => $this->provider->where(['is_approved' => 1])->count(),
                'total_services' => $this->service->count(),
                'package_bookings' => $packageBookingsCount,
                'completed_package_bookings' => $completedPackageBookings,
                'active_subscriptions' => $activeSubscriptions,
                'is_company_dashboard' => true
            ]
        ];

        $data[] = ['admin_total_earning' => $subscriptionRevenue];

        $recent_transactions = $this->transaction
            ->with(['booking'])
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->take(5)
            ->get();
        $data[] = [
            'recent_transactions' => $recent_transactions,
            'this_month_trx_count' => $transaction
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count()
        ];

        $bookings = $this->booking->with([
            'detail.service' => function ($query) {
                $query->select('id', 'name', 'thumbnail');
            }
        ])
            ->where('booking_status', 'pending')
            ->take(5)->latest()->get();
        $data[] = ['bookings' => $bookings];

        $top_providers = $this->provider
            ->withCount(['reviews'])
            ->with(['owner', 'reviews'])
            ->ofApproval(1)
            ->take(5)->orderBy('avg_rating', 'DESC')->get();
        $data[] = ['top_providers' => $top_providers];

        $zone_wise_bookings = $this->booking
            ->with([
                'zone' => function ($query) {
                    $query->withoutGlobalScope('translate');
                }
            ])
            ->whereHas('zone', function ($query) {
                $query->ofStatus(1)->withoutGlobalScope('translate');
            })
            ->whereMonth('created_at', now()->month)
            ->select('zone_id', DB::raw('count(*) as total'))
            ->groupBy('zone_id')
            ->get();
        $data[] = ['zone_wise_bookings' => $zone_wise_bookings, 'total_count' => $this->booking->count()];

        $year = session()->has('dashboard_earning_graph_year') ? session('dashboard_earning_graph_year') : date('Y');
        $chart_data = $this->buildCompanyDashboardChartData((int) $year);

        $extraData = $this->getTopDemandedData();
        return view('adminmodule::dashboard', array_merge(compact('data', 'chart_data'), $extraData));
    }

    private function getTopDemandedData(): array
    {
        $top_services = \Modules\BookingModule\Entities\BookingDetail::query()
            ->select('service_id', 'service_name', DB::raw('count(*) as total_requests'))
            ->whereNotNull('service_id')
            ->groupBy('service_id', 'service_name')
            ->orderByDesc('total_requests')
            ->with([
                'service' => function ($q) {
                    $q->select('id', 'name', 'thumbnail');
                }
            ])
            ->take(5)
            ->get();

        if ($top_services->isEmpty()) {
            $top_services = \Modules\ServiceManagement\Entities\Service::query()
                ->withCount('booking_details')
                ->orderByDesc('booking_details_count')
                ->take(5)
                ->get();
        }

        $top_customers = \Modules\UserManagement\Entities\User::query()
            ->where('user_type', 'customer')
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        return compact('top_services', 'top_customers');
    }

    private function buildCompanyDashboardChartData(int $year): array
    {
        $monthlyRevenue = $this->booking
            ->where('booking_status', 'completed')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('SUM(total_booking_amount) as revenue'),
                DB::raw('MONTH(created_at) as month')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyPackageRevenue = $this->booking
            ->whereNotNull('subscription_id')
            ->where('booking_status', 'completed')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('SUM(total_booking_amount) as revenue'),
                DB::raw('MONTH(created_at) as month')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $chart_data = ['total_earning' => [], 'commission_earning' => []];
        foreach (range(1, 12) as $month) {
            $chart_data['total_earning'][] = with_decimal_point((float) ($monthlyRevenue[$month]->revenue ?? 0));
            $chart_data['commission_earning'][] = with_decimal_point((float) ($monthlyPackageRevenue[$month]->revenue ?? 0));
        }

        return $chart_data;
    }


    public function component()
    {
        return view("adminmodule::component");
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDashboardEarningGraph(Request $request): JsonResponse
    {
        $year = (int) $request['year'];
        session()->put('dashboard_earning_graph_year', $year);

        if (supervisorMode()) {
            return response()->json($this->buildCompanyDashboardChartData($year));
        }

        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->whereYear('created_at', '=', $year)
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $all_earnings = [];
        foreach ($amounts as $amount) {
            foreach ($fee_amounts as $key => $fee) {
                if ($amount['month'] == $fee['month']) {
                    $all_earnings[$key] = array_merge($amount, $fee);
                }
                if (!isset($all_earnings[$key])) {
                    $all_earnings[$key] = $amount;
                }
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        }

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($all_earnings as $key => $item) {
                if ($item['month'] == $month) {
                    $chart_data['total_earning'][] = with_decimal_point($item['admin_commission'] + $item['fee']);
                    $chart_data['commission_earning'][] = with_decimal_point($item['admin_commission']);
                    $found = 1;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
                $chart_data['commission_earning'][] = with_decimal_point(0);
            }
        }

        return response()->json($chart_data);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            $user = $this->user->where(['id' => auth('api')->id()])->with(['roles'])->first();
            return response()->json(response_formatter(DEFAULT_200, $user), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            return response()->json(response_formatter(DEFAULT_200, auth('api')->user()), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function profileInfo(Request $request): Renderable
    {
        return view('adminmodule::admin.profile-update');
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['profile_image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'profile_image' => 'image|max:' . uploadMaxFileSizeInKB('image') . '|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
            'password' => '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : ''
        ]);

        $user = $this->user->find($request->user()->id);
        $user->first_name = $request->first_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->last_name = $request->last_name;
        if ($request->has('profile_image')) {
            $user->profile_image = file_uploader('user/profile_image/', APPLICATION_IMAGE_FORMAT, $request->profile_image, $user->profile_image);
        }
        if (!is_null($request->password)) {
            $user->password = bcrypt($request->confirm_password);
        }
        $user->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['key']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpdatedData(Request $request): JsonResponse
    {
        $message = $this->channelList->wherehas('channelUsers', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)->where('is_read', 0);
        })->count();

        return response()->json([
            'status' => 1,
            'data' => [
                'message' => $message
            ]
        ]);
    }

    private function routeFullUrl($uri)
    {
        $fullURL = url($uri);
        if ($uri == 'admin/booking/list/verification') {
            $fullURL = url($uri) . '?booking_status=pending&type=pending';
        }
        if ($uri == 'admin/booking/list') {
            $fullURL = url($uri) . '?booking_status=pending';
        }
        if ($uri == 'admin/configuration/get-notification-setting') {
            $fullURL = url($uri) . '?type=customers';
        }
        if ($uri == 'admin/customer/settings') {
            $fullURL = url($uri) . '?web_page=loyalty_point';
        }
        if ($uri == 'admin/chat/index') {
            $fullURL = url($uri) . '?user_type=customer';
        }
        return $fullURL;
    }

    public function searchRouting(Request $request)
    {
        $searchKeyword = $request->input('search');
        $formattedRoutes = $this->advanceSearchService->pageSearchList($searchKeyword, "admin");
        $menuSearchResults = [];
        if (!empty($searchKeyword)) {
            $menuSearchResults = $this->advanceSearchService->searchMenuList($searchKeyword, "admin");
        }
        $modelSearchResults = $this->advanceSearchService->searchModelList($searchKeyword, "admin");
        $allRoutes = $this->advanceSearchService->sortByPriority($formattedRoutes, $modelSearchResults, $menuSearchResults, $searchKeyword);
        return response()->json([
            'keyword' => $searchKeyword,
            'result' => $allRoutes,
            'htmlView' => view('adminmodule::admin._advance_search', [
                'result' => $allRoutes,
                'keyword' => $searchKeyword,
                'recent' => false
            ])->render()
        ]);
    }

    /**
     * @return array{routeName: string, URI: array|string|string[], fullRoute: string}
     */
    private function filterRoute($model, $route, $type = null, $name = null, $prefix = null): array
    {
        $uri = $route->uri();
        $routeName = $route->getName();
        $formattedRouteName = ucwords(str_replace(['.', '_'], ' ', Str::afterLast($routeName, '.')));
        $uriWithParameter = str_replace('{id}', $model->id, $uri);
        $fullURL = url('/') . '/' . $uriWithParameter;
        if ($type == 'booking') {
            $fullURL = url('/') . '/' . $uriWithParameter . '?web_page=details';
        }
        if ($type == 'customer') {
            $fullURL = $formattedRouteName == 'Detail' ? $fullURL . '?web_page=overview' : $fullURL;
        }
        if ($type == 'provider') {
            $fullURL = $formattedRouteName == 'Details' ? $fullURL . '?web_page=overview' : $fullURL;
        }

        $routeName = $prefix ? $prefix . ' ' . $formattedRouteName : $formattedRouteName;
        $routeName = $name ? $routeName . ' - (' . $name . ')' : $routeName;

        return [
            'page_title' => $routeName ?? '',
            'page_title_value' => $routeName ?? '',
            'key' => base64_encode($uri),
            'uri' => $uriWithParameter ?? '',
            'full_route' => $fullURL ?? '',
            'type' => $model->getTable(),
            'priority' => 3
        ];

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeClickedRoute(Request $request): RedirectResponse
    {
        $userId = auth()->id();
        $userType = auth()->user()->user_type;
        $response = $request['response'];

        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        $clickedRoute = RouteSearchHistory::updateOrCreate(
            [
                'user_id' => $userId,
                'user_type' => $userType,
                'route_uri' => $request['uri']
            ],
            [
                'route_name' => $request['page_title_value'],
                'route_uri' => $request['uri'],
                'route_full_url' => $request['route_full_url'],
                'keyword' => $request['keyword'],
                'response' => $response,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        $clickedRoute->touch();

        $userClickCount = RouteSearchHistory::where('user_id', $userId)->where('user_type', $userType)->count();

        if ($userClickCount >= 15) {
            RouteSearchHistory::where('user_id', $userId)->where('user_type', $userType)->orderBy('created_at', 'asc')->first()->delete();
        }

        $redirectUrl = $request['route_full_url'];
        $separator = (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?');
        $redirectUrl .= $separator . 'keyword=' . urlencode($request['keyword']);
        return redirect($redirectUrl);

    }



    public function recentSearch(): JsonResponse
    {
        $userId = auth()->id();
        $userType = auth()->user()->user_type;
        $recentSearches = RouteSearchHistory::where('user_id', $userId)
            ->where('user_type', $userType)
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();
        $formattedResult = collect($recentSearches)->map(function ($item) {
            return [
                'page_title_value' => $item['route_name'],
                'page_title' => $item['route_name'],
                'uri' => $item['route_uri'],
                'full_route' => $item['route_full_url'],
                'keyword' => $item['keyword'],
                'response' => $item['response']
            ];
        });
        $result = $this->advanceSearchService->getSortRecentSearchByType($formattedResult);
        $isSuggestion = false;

        if (count($result) === 0) {
            $menuSuggestions = [
                [
                    'page_title' => 'Firebase Configuration',
                    'page_title_value' => 'Firebase Configuration',
                    'uri' => 'admin/configuration/third-party/firebase-configuration',
                    'full_route' => url('admin/configuration/third-party/firebase-configuration'),
                    'type' => 'menu',
                    'priority' => 1
                ],
                [
                    'page_title' => 'Digital Payment Setup',
                    'page_title_value' => 'Digital Payment Setup',
                    'uri' => 'admin/configuration/third-party/payment_config?type=digital_payment',
                    'full_route' => url('admin/configuration/third-party/payment_config?type=digital_payment'),
                    'type' => 'menu',
                    'priority' => 1
                ],
                [
                    'page_title' => 'Login Setup',
                    'page_title_value' => 'Login Setup',
                    'uri' => 'admin/business-settings/login/setup',
                    'full_route' => url('admin/business-settings/login/setup'),
                    'type' => 'menu',
                    'priority' => 1
                ]
            ];
            $pageSuggestions = [
                [
                    'page_title' => 'Business Settings',
                    'page_title_value' => 'Business Settings',
                    'uri' => 'admin/business-settings/get-business-information',
                    'full_route' => url('admin/business-settings/get-business-information'),
                    'type' => 'page',
                    'priority' => 2
                ],
                [
                    'page_title' => 'Business Plan',
                    'page_title_value' => 'Business Plan',
                    'uri' => 'admin/business-settings/get-business-information?web_page=business_plan',
                    'full_route' => url('admin/business-settings/get-business-information?web_page=business_plan'),
                    'type' => 'page',
                    'priority' => 2
                ],
                [
                    'page_title' => 'Zone Create',
                    'page_title_value' => 'Zone Create',
                    'uri' => 'admin/zone/create',
                    'full_route' => url('admin/zone/create'),
                    'type' => 'page',
                    'priority' => 2
                ]
            ];

            $result = array_filter([
                'menu' => $menuSuggestions,
                'page' => $pageSuggestions
            ]);
            $isSuggestion = true;
        }

        return response()->json([
            'keyword' => '',
            'result' => $result,
            'htmlView' => view('adminmodule::admin._advance_search', [
                'result' => $result,
                'recent' => !$isSuggestion && count($result) > 0,
                'suggestion' => $isSuggestion,
                'keyword' => ''
            ])->render()
        ]);
    }

    public function refreshSetupGuideUI(): JsonResponse
    {
        $setup = getSetupGuideSteps('admin_panel', auth()->user());

        return response()->json([
            'percentage' => $setup['percentage'],
            'unchecked_keys' => collect($setup['steps'])
                ->where('checked', false)
                ->pluck('key')
                ->values(),
            'unchecked_count' => collect($setup['steps'])
                ->where('checked', false)
                ->count(),
            'steps' => $setup['steps'],
            'all_completed' => collect($setup['steps'])
                ->every(fn($step) => $step['checked'])
        ]);

    }
}
