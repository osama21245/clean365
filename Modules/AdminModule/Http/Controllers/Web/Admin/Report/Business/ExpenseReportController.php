<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;

class ExpenseReportController extends Controller
{
    protected Zone $zone;
    protected Provider $provider;
    protected Category $categories;
    protected Booking $booking;

    protected Account $account;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected BookingDetailsAmount $bookingDetailsAmount;

    public function __construct(Zone $zone, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $bookingDetailsAmount)
    {
        $this->zone = $zone;
        $this->provider = $provider;
        $this->categories = $categories;
        $this->booking = $booking;

        $this->service = $service;
        $this->account = $account;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->bookingDetailsAmount = $bookingDetailsAmount;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getBusinessExpenseReport(Request $request)
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '']);

        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $categories = $this->categories->ofType('main')->select('id', 'name')->get();
        $sub_categories = $this->categories->ofType('sub')->select('id', 'name')->get();

        $zoneIds = $this->normalizeFilterIds($request->input('zone_ids', []));
        $categoryIds = $this->normalizeFilterIds($request->input('category_ids', []));
        $subCategoryIds = $this->normalizeFilterIds($request->input('sub_category_ids', []));

        if ($zoneIds && count($zoneIds) === $zones->count()) {
            $zoneIds = [];
        }
        if ($categoryIds && count($categoryIds) === $categories->count()) {
            $categoryIds = [];
        }
        if ($subCategoryIds && count($subCategoryIds) === $sub_categories->count()) {
            $subCategoryIds = [];
        }

        $search = $request['search'];
        $queryParams = ['search' => $search];
        $queryParams['zone_ids'] = $zoneIds;
        $queryParams['category_ids'] = $categoryIds;
        $queryParams['sub_category_ids'] = $subCategoryIds;
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
        }
        if ($request->has('date_range') && $request['date_range'] == 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        $date_range = $request['date_range'];
        if (is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif ($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if ($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic == 'week' ? 'day' : $deterministic;

        $filtered_booking_amounts = $this->bookingDetailsAmount
            ->with(['booking'])
            ->where(function ($query) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $query->whereHas('booking', function ($subQuery) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                    $this->applyBookingReportFilters($subQuery, $zoneIds, $categoryIds, $subCategoryIds);
                    $this->applyDateRangeFilterIfPresent($subQuery, $request);
                    $subQuery
                        ->ofBookingStatus('completed')
                        ->when($request->has('search'), function ($subQuery) use ($request) {
                            $keys = explode(' ', $request['search']);
                            return $subQuery->where(function ($subQuery) use ($keys) {
                                foreach ($keys as $key) {
                                    $subQuery->where('readable_id', 'LIKE', '%' . $key . '%');
                                }
                            });
                        });
                });
            })
            ->where(function ($query) {
                $query->where('discount_by_admin', '>', 0)
                    ->orWhere('coupon_discount_by_admin', '>', 0)
                    ->orWhere('campaign_discount_by_admin', '>', 0);
            })
            ->latest()
            ->get()
            ->map(function ($amount) {
                $normalDiscount = (float) $amount->discount_by_admin;
                $couponDiscount = (float) $amount->coupon_discount_by_admin;
                $campaignDiscount = (float) $amount->campaign_discount_by_admin;

                return [
                    'created_at' => $amount->created_at,
                    'reference' => $amount->booking->readable_id ?? '',
                    'expense_type' => 'booking_discount',
                    'normal_discount' => $normalDiscount,
                    'coupon_discount' => $couponDiscount,
                    'campaign_discount' => $campaignDiscount,
                    'total_expense' => $normalDiscount + $couponDiscount + $campaignDiscount];
            });

        $filtered_transaction_expenses = $this->buildFilteredTransactionExpenses($request, $zoneIds, $categoryIds, $subCategoryIds)
            ->map(function ($transaction) {
                $normalDiscount = 0;
                $couponDiscount = 0;
                $campaignDiscount = 0;
                $totalExpense = 0;

                if ($transaction->trx_type === TRX_TYPE['add_fund_bonus']) {
                    $normalDiscount = (float) $transaction->credit;
                    $totalExpense = $normalDiscount;
                } elseif ($transaction->trx_type === 'referral_discount') {
                    $campaignDiscount = (float) $transaction->debit;
                    $totalExpense = $campaignDiscount;
                } elseif ($transaction->trx_type === 'referral_earning') {
                    $couponDiscount = (float) $transaction->credit;
                    $totalExpense = $couponDiscount;
                }

                return [
                    'created_at' => $transaction->created_at,
                    'reference' => $transaction->id,
                    'expense_type' => $transaction->trx_type,
                    'normal_discount' => $normalDiscount,
                    'coupon_discount' => $couponDiscount,
                    'campaign_discount' => $campaignDiscount,
                    'total_expense' => $totalExpense];
            });

        $includeNonBookingRows = empty($zoneIds) && empty($categoryIds) && empty($subCategoryIds);

        $filtered_expenses = $this->paginateCollection(
            ($includeNonBookingRows ? $filtered_booking_amounts->concat($filtered_transaction_expenses) : $filtered_booking_amounts)->sortByDesc('created_at')->values(),
            pagination_limit(),
            $request,
            $queryParams
        );

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $query->ofBookingStatus('completed');
                $this->applyBookingReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
                $this->applyDateRangeFilterIfPresent($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(service_unit_cost) as service_unit_cost'),
                    DB::raw('sum(discount_by_admin) as discount_by_admin'),
                    DB::raw('sum(discount_by_provider) as discount_by_provider'),
                    DB::raw('sum(coupon_discount_by_admin) as coupon_discount_by_admin'),
                    DB::raw('sum(coupon_discount_by_provider) as coupon_discount_by_provider'),
                    DB::raw('sum(campaign_discount_by_admin) as campaign_discount_by_admin'),
                    DB::raw('sum(campaign_discount_by_provider) as campaign_discount_by_provider'),
                    DB::raw('sum(admin_commission) as admin_commission'),

                    DB::raw($group_by_deterministic . '(created_at) ' . $group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $bonus_amounts = $this->transaction
            ->where('trx_type', TRX_TYPE['add_fund_bonus'])
            ->when($request->has('date_range'), function ($query) use ($request) {
                self::timeFilterQuery($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as bonus'),
                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $referral_discounts = $this->transaction
            ->where('trx_type', 'referral_discount')
            ->where('debit', '>', 0)
            ->when($request->has('date_range'), function ($query) use ($request) {
                self::timeFilterQuery($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(debit) as referral_discount'),
                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $referral_earnings = $this->transaction
            ->where('trx_type', 'referral_earning')
            ->where('credit', '>', 0)
            ->where('to_user_account', 'user_wallet')
            ->when($request->has('date_range'), function ($query) use ($request) {
                self::timeFilterQuery($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as referral_earning'),
                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $defaultRow = [
            'discount_by_admin' => 0,
            'coupon_discount_by_admin' => 0,
            'campaign_discount_by_admin' => 0,
            'bonus' => 0,
            'referral_discount' => 0,
            'referral_earning' => 0];

        $all_expenses = [];

        foreach ($amounts as $amount) {
            $timelineKey = $amount[$group_by_deterministic];
            $all_expenses[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $amount
            );
        }

        foreach ($bonus_amounts as $bonus) {
            $timelineKey = $bonus[$group_by_deterministic];
            $all_expenses[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses[$timelineKey] ?? [],
                $bonus
            );
        }

        foreach ($referral_discounts as $referral) {
            $timelineKey = $referral[$group_by_deterministic];
            $all_expenses[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses[$timelineKey] ?? [],
                $referral
            );
        }

        foreach ($referral_earnings as $earning) {
            $timelineKey = $earning[$group_by_deterministic];
            $all_expenses[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses[$timelineKey] ?? [],
                $earning
            );
        }

        ksort($all_expenses);
        $all_expenses = array_values($all_expenses);

        $chart_data = ['normal_discount' => array(), 'campaign_discount' => array(), 'coupon_discount' => array(), 'bonus' => array(), 'expenses' => array(), 'timeline' => array(), 'referral_discount' => array(), 'referral_earning' => array()];
        $total_promotional_cost = ['total_expense' => 0, 'discount' => 0, 'coupon' => 0, 'campaign' => 0, 'bonus' => 0, 'referral_discount' => 0, 'referral_earning' => 0];
        if ($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found = 0;
                $chart_data['timeline'][] = $month;
                foreach ($all_expenses as $item) {
                    if ($item['month'] == $month) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['referral_discount'][] = with_decimal_point($item['referral_discount']);
                        $chart_data['referral_earning'][] = with_decimal_point($item['referral_earning']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus'] + $item['referral_discount'] + $item['referral_earning']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['bonus'] += $item['bonus'] ?? 0;
                        $total_promotional_cost['referral_discount'] += $item['referral_discount'] ?? 0;
                        $total_promotional_cost['referral_earning'] += $item['referral_earning'] ?? 0;
                        $total_promotional_cost['total_expense'] +=
                            ($item['discount_by_admin'] ?? 0) +
                            ($item['coupon_discount_by_admin'] ?? 0) +
                            ($item['campaign_discount_by_admin'] ?? 0) +
                            ($item['bonus'] ?? 0) +
                            ($item['referral_discount'] ?? 0) +
                            ($item['referral_earning'] ?? 0);
                    }
                }

                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                    $chart_data['referral_discount'][] = with_decimal_point(0);
                    $chart_data['referral_earning'][] = with_decimal_point(0);
                }
            }

        } elseif ($deterministic == 'year') {
            foreach ($all_expenses as $item) {
                $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                $chart_data['referral_discount'][] = with_decimal_point($item['referral_discount']);
                $chart_data['referral_earning'][] = with_decimal_point($item['referral_earning']);
                $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus'] + $item['referral_discount']+ $item['referral_earning']);
                $chart_data['timeline'][] = $item[$deterministic];

                $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                $total_promotional_cost['bonus'] += $item['bonus'] ?? 0;
                $total_promotional_cost['referral_discount'] += $item['referral_discount'] ?? 0;
                $total_promotional_cost['referral_earning'] += $item['referral_earning'] ?? 0;
                $total_promotional_cost['total_expense'] +=
                    ($item['discount_by_admin'] ?? 0) +
                    ($item['coupon_discount_by_admin'] ?? 0) +
                    ($item['campaign_discount_by_admin'] ?? 0) +
                    ($item['bonus'] ?? 0) +
                    ($item['referral_discount'] ?? 0) +
                    ($item['referral_earning'] ?? 0);

            }
        } elseif ($deterministic == 'day') {
            if ($date_range == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($date_range == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($date_range == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d', strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses as $item) {
                    if ($item['day'] == $i) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['referral_discount'][] = with_decimal_point($item['referral_discount']);
                        $chart_data['referral_earning'][] = with_decimal_point($item['referral_earning']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus'] + $item['referral_discount']+ $item['referral_earning']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['bonus'] += $item['bonus'] ?? 0;
                        $total_promotional_cost['referral_discount'] += $item['referral_discount'] ?? 0;
                        $total_promotional_cost['referral_earning'] += $item['referral_earning'] ?? 0;
                        $total_promotional_cost['total_expense'] +=
                            ($item['discount_by_admin'] ?? 0) +
                            ($item['coupon_discount_by_admin'] ?? 0) +
                            ($item['campaign_discount_by_admin'] ?? 0) +
                            ($item['bonus'] ?? 0) +
                            ($item['referral_discount'] ?? 0) +
                            ($item['referral_earning'] ?? 0);
                    }
                }
                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                    $chart_data['referral_discount'][] = with_decimal_point(0);
                    $chart_data['referral_earning'][] = with_decimal_point(0);
                }
            }
        } elseif ($deterministic == 'week') {
            if ($date_range == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($date_range == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses as $item) {
                    if ($item['day'] == $i) {
                        $chart_data['normal_discount'][] = with_decimal_point($item['discount_by_admin']);
                        $chart_data['campaign_discount'][] = with_decimal_point($item['campaign_discount_by_admin']);
                        $chart_data['coupon_discount'][] = with_decimal_point($item['coupon_discount_by_admin']);
                        $chart_data['bonus'][] = with_decimal_point($item['bonus']);
                        $chart_data['referral_discount'][] = with_decimal_point($item['referral_discount']);
                        $chart_data['referral_earning'][] = with_decimal_point($item['referral_earning']);
                        $chart_data['expenses'][] = with_decimal_point($item['discount_by_admin'] + $item['coupon_discount_by_admin'] + $item['campaign_discount_by_admin'] + $item['bonus'] + $item['referral_discount']+ $item['referral_earning']);
                        $found = 1;

                        $total_promotional_cost['discount'] += $item['discount_by_admin'] ?? 0;
                        $total_promotional_cost['coupon'] += $item['coupon_discount_by_admin'] ?? 0;
                        $total_promotional_cost['campaign'] += $item['campaign_discount_by_admin'] ?? 0;
                        $total_promotional_cost['bonus'] += $item['bonus'] ?? 0;
                        $total_promotional_cost['referral_discount'] += $item['referral_discount'] ?? 0;
                        $total_promotional_cost['referral_earning'] += $item['referral_earning'] ?? 0;
                        $total_promotional_cost['total_expense'] +=
                            ($item['discount_by_admin'] ?? 0) +
                            ($item['coupon_discount_by_admin'] ?? 0) +
                            ($item['campaign_discount_by_admin'] ?? 0) +
                            ($item['bonus'] ?? 0) +
                            ($item['referral_discount'] ?? 0) +
                            ($item['referral_earning'] ?? 0);
                    }
                }
                if (!$found) {
                    $chart_data['normal_discount'][] = with_decimal_point(0);
                    $chart_data['campaign_discount'][] = with_decimal_point(0);
                    $chart_data['coupon_discount'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                    $chart_data['bonus'][] = with_decimal_point(0);
                    $chart_data['referral_discount'][] = with_decimal_point(0);
                    $chart_data['referral_earning'][] = with_decimal_point(0);
                }
            }
        }

        return view('adminmodule::admin.report.business.expense', compact('zones', 'categories', 'sub_categories', 'filtered_expenses', 'chart_data', 'total_promotional_cost', 'deterministic', 'queryParams'));
    }

    public function getBusinessExpenseReportDownload(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '']);

        $zoneIds = $this->normalizeFilterIds($request->input('zone_ids', []));
        $categoryIds = $this->normalizeFilterIds($request->input('category_ids', []));
        $subCategoryIds = $this->normalizeFilterIds($request->input('sub_category_ids', []));

        if ($zoneIds && count($zoneIds) === $this->zone->ofStatus(1)->count()) {
            $zoneIds = [];
        }
        if ($categoryIds && count($categoryIds) === $this->categories->ofType('main')->count()) {
            $categoryIds = [];
        }
        if ($subCategoryIds && count($subCategoryIds) === $this->categories->ofType('sub')->count()) {
            $subCategoryIds = [];
        }

        $filtered_booking_amounts = $this->bookingDetailsAmount
            ->with(['booking'])
            ->whereHas('booking', function ($query) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $this->applyBookingReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
                $this->applyDateRangeFilterIfPresent($query, $request);
                $query
                    ->ofBookingStatus('completed')
                    ->when($request->has('search'), function ($query) use ($request) {
                        $keys = explode(' ', $request['search']);
                        return $query->where(function ($query) use ($keys) {
                            foreach ($keys as $key) {
                                $query->where('readable_id', 'LIKE', '%' . $key . '%');
                            }
                        });
                    });
            })
            ->where(function ($query) {
                $query->where('discount_by_admin', '>', 0)
                    ->orWhere('coupon_discount_by_admin', '>', 0)
                    ->orWhere('campaign_discount_by_admin', '>', 0);
            })
            ->latest()
            ->get()
            ->map(function ($amount) {
                $normalDiscount = (float) $amount->discount_by_admin;
                $couponDiscount = (float) $amount->coupon_discount_by_admin;
                $campaignDiscount = (float) $amount->campaign_discount_by_admin;

                return [
                    'reference' => $amount->booking->readable_id ?? '',
                    'expense_type' => 'booking_discount',
                    'normal_discount' => $normalDiscount,
                    'coupon_discount' => $couponDiscount,
                    'campaign_discount' => $campaignDiscount,
                    'total_expense' => $normalDiscount + $couponDiscount + $campaignDiscount];
            });

        $filtered_transaction_expenses = $this->buildFilteredTransactionExpenses($request, $zoneIds, $categoryIds, $subCategoryIds)
            ->map(function ($transaction) {
                $normalDiscount = 0;
                $couponDiscount = 0;
                $campaignDiscount = 0;
                $totalExpense = 0;

                if ($transaction->trx_type === TRX_TYPE['add_fund_bonus']) {
                    $normalDiscount = (float) $transaction->credit;
                    $totalExpense = $normalDiscount;
                } elseif ($transaction->trx_type === 'referral_discount') {
                    $campaignDiscount = (float) $transaction->debit;
                    $totalExpense = $campaignDiscount;
                } elseif ($transaction->trx_type === 'referral_earning') {
                    $couponDiscount = (float) $transaction->credit;
                    $totalExpense = $couponDiscount;
                }

                return [
                    'reference' => $transaction->id,
                    'expense_type' => $transaction->trx_type,
                    'normal_discount' => $normalDiscount,
                    'coupon_discount' => $couponDiscount,
                    'campaign_discount' => $campaignDiscount,
                    'total_expense' => $totalExpense];
            });

        $filtered_booking_amounts = $filtered_booking_amounts->concat($filtered_transaction_expenses)->values();

        $count = 0;

        $fileName = 'business_expense_report_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($filtered_booking_amounts))->download($fileName, function ($item) use (&$count) {
            $count++;
            return [
                translate('SL')                => $count,
                translate('Reference')         => $item['reference'] ?? '',
                translate('Type')              => str_replace('_', ' ', $item['expense_type'] ?? ''),
                translate('Normal_Discount')   => with_currency_symbol($item['normal_discount']),
                translate('Coupon_Discount')   => with_currency_symbol($item['coupon_discount']),
                translate('Campaign_Discount') => with_currency_symbol($item['campaign_discount']),
                translate('Total_Expense')     => with_currency_symbol($item['total_expense'])];
        });
    }

    private function buildFilteredTransactionExpenses(Request $request, array $zoneIds, array $categoryIds, array $subCategoryIds)
    {
        return $this->transaction
            ->where(function ($query) {
                $query->where('trx_type', TRX_TYPE['add_fund_bonus'])
                    ->orWhere(function ($transactionQuery) {
                        $transactionQuery->where('trx_type', 'referral_discount')
                            ->where('debit', '>', 0);
                    })
                    ->orWhere(function ($transactionQuery) {
                        $transactionQuery->where('trx_type', 'referral_earning')
                            ->where('credit', '>', 0)
                            ->where('to_user_account', 'user_wallet');
                    });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                self::timeFilterQuery($query, $request);
            })
            ->when($request->has('search') && $request->search, function ($query) use ($request) {
                $keys = explode(' ', $request->search);
                $query->where(function ($searchQuery) use ($keys) {
                    foreach ($keys as $key) {
                        $searchQuery->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                $query->where(function ($transactionQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                    $transactionQuery
                        ->whereHas('booking', function ($bookingQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                            $this->applyBookingReportFilters($bookingQuery, $zoneIds, $categoryIds, $subCategoryIds);
                        })
                        ->orWhereHas('repeat', function ($repeatQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                            $repeatQuery->whereHas('booking', function ($bookingQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                                $this->applyBookingReportFilters($bookingQuery, $zoneIds, $categoryIds, $subCategoryIds);
                            });
                        });
                });
            })
            ->latest()
            ->get();
    }

    private function applyBookingReportFilters($query, array $zoneIds, array $categoryIds, array $subCategoryIds): void
    {
        if (!empty($zoneIds)) {
            $query->whereIn('zone_id', $zoneIds);
        }

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (!empty($subCategoryIds)) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        }
    }

    private function applyDateRangeFilterIfPresent($query, Request $request): void
    {
        if ($request->has('date_range')) {
            self::timeFilterQuery($query, $request);
        }
    }

    private function normalizeFilterIds(array $ids): array
    {
        return array_values(array_filter(array_unique($ids), function ($id) {
            return !empty($id) && $id !== 'all';
        }));
    }

    private function paginateCollection($items, int $perPage, Request $request, array $queryParams): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return (new LengthAwarePaginator($pagedItems, $items->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $queryParams]))->appends($queryParams);
    }

    /**
     * @param $instance
     * @param $request
     * @return mixed
     */
    function filterQuery($instance, $request): mixed
    {
        return $instance
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })
            ->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })
            ->when($request->has('date_range') && $request['date_range'] == 'custom_date', function ($query) use ($request) {
                $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            })
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            });
    }

    function timeFilterQuery($instance, $request): mixed
    {
        $now = Carbon::now();

        return $instance
            ->when($request->has('date_range') && $request['date_range'] == 'custom_date', function ($query) use ($request) {
                $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            })
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use ($request, $now) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()]);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [$now->copy()->subDays(15)->startOfDay(), $now->copy()->endOfDay()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereBetween('created_at', [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()]);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [$now->copy()->subMonths(6)->startOfDay(), $now->copy()->endOfDay()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [$now->copy()->month(1)->startOfQuarter(), $now->copy()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [$now->copy()->month(4)->startOfQuarter(), $now->copy()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [$now->copy()->month(7)->startOfQuarter(), $now->copy()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [$now->copy()->month(10)->startOfQuarter(), $now->copy()->month(10)->endOfQuarter()]);
                }
            });
    }
}
