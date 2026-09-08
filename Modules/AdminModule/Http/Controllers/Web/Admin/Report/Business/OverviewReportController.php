<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
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
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OverviewReportController extends Controller
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
    use AuthorizesRequests;

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
     * @throws AuthorizationException
     */
    public function getBusinessOverviewReport(Request $request)
    {
        $this->authorize('report_view');
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

        $queryParams = $request->only('search', 'zone_ids', 'category_ids', 'sub_category_ids', 'date_range');
        $queryParams['zone_ids'] = $zoneIds;
        $queryParams['category_ids'] = $categoryIds;
        $queryParams['sub_category_ids'] = $subCategoryIds;
        if ($request->date_range === 'custom_date') {
            $queryParams['from'] = $request->from;
            $queryParams['to'] = $request->to;
        }

        $date_range = $request['date_range'];
        if(is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic=='week'?'day':$deterministic;

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $query->ofBookingStatus('completed')
                    ->when(!empty($zoneIds), function ($query) use ($zoneIds) {
                        $query->whereIn('zone_id', $zoneIds);
                    })
                    ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                        $query->whereIn('category_id', $categoryIds);
                    })
                    ->when(!empty($subCategoryIds), function ($query) use ($subCategoryIds) {
                        $query->whereIn('sub_category_id', $subCategoryIds);
                    })
                    ->when($request->has('date_range'), function ($query) use ($request) {
                        $this->applyDateRangeConditions($query, $request);
                    });
            })->orWhereHas('repeat', function ($subQuery) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $subQuery->ofBookingStatus('completed')
                    ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                        $query->whereHas('booking', function ($bookingQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                            $this->applyBookingReportFilters($bookingQuery, $zoneIds, $categoryIds, $subCategoryIds);
                        });
                    })
                    ->when($request->has('date_range'), function ($q) use ($request) {
                        $this->applyDateRangeConditions($q, $request);
                    });
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

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $earningAmount = $this->transaction
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                $this->applyTransactionReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
            })
            ->when($request->has('date_range'), function ($query) use($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as earning'),

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $bonus_amounts = $this->transaction
            ->where('trx_type', TRX_TYPE['add_fund_bonus'])
            ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                $this->applyTransactionReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
            })
            ->when($request->has('date_range'), function ($query) use($request) {
                $this->applyDateRangeConditions($query, $request);
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
            ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                $this->applyTransactionReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(debit) as referral_discount'),
                    DB::raw($group_by_deterministic . '(created_at) ' . $group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $referral_earnings = $this->transaction
            ->where('trx_type', 'referral_earning')
            ->where('credit', '>', 0)
            ->where('to_user_account', 'user_wallet')
            ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                $this->applyTransactionReportFilters($query, $zoneIds, $categoryIds, $subCategoryIds);
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as referral_earning'),
                    DB::raw($group_by_deterministic . '(created_at) ' . $group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $defaultRow = [
            'service_unit_cost' => 0,
            'discount_by_admin' => 0,
            'discount_by_provider' => 0,
            'coupon_discount_by_admin' => 0,
            'coupon_discount_by_provider' => 0,
            'campaign_discount_by_admin' => 0,
            'campaign_discount_by_provider' => 0,
            'admin_commission' => 0,
            'earning' => 0,
            'bonus' => 0,
            'referral_discount' => 0,
            'referral_earning' => 0];

        $all_expenses_and_earnings = [];

        foreach ($amounts as $amount) {
            $timelineKey = $amount[$group_by_deterministic];
            $all_expenses_and_earnings[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $amount
            );
        }

        foreach ($earningAmount as $earning) {
            $timelineKey = $earning[$group_by_deterministic];
            $all_expenses_and_earnings[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses_and_earnings[$timelineKey] ?? [],
                $earning
            );
        }

        foreach ($bonus_amounts as $bonus) {
            $timelineKey = $bonus[$group_by_deterministic];
            $all_expenses_and_earnings[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses_and_earnings[$timelineKey] ?? [],
                $bonus
            );
        }

        foreach ($referral_discounts as $referralDiscount) {
            $timelineKey = $referralDiscount[$group_by_deterministic];
            $all_expenses_and_earnings[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses_and_earnings[$timelineKey] ?? [],
                $referralDiscount
            );
        }

        foreach ($referral_earnings as $referralEarning) {
            $timelineKey = $referralEarning[$group_by_deterministic];
            $all_expenses_and_earnings[$timelineKey] = array_merge(
                $defaultRow,
                [$group_by_deterministic => $timelineKey],
                $all_expenses_and_earnings[$timelineKey] ?? [],
                $referralEarning
            );
        }

        ksort($all_expenses_and_earnings);
        $amounts = array_values($all_expenses_and_earnings);

        $chart_data = ['earnings'=>array(), 'expenses'=>array(), 'timeline'=>array()];
        $all_expenses = ['discount' => 0, 'coupon' => 0, 'campaign' => 0, 'bonus' => 0, 'referral_discount' => 0, 'referral_earning' => 0];

        if($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found = 0;
                $chart_data['timeline'][] = $month;

                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['month'] == $month) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0) +
                            data_get($item, 'referral_discount', 0) +
                            data_get($item, 'referral_earning', 0)
                        );

                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                        $all_expenses['referral_discount'] += data_get($item, 'referral_discount', 0);
                        $all_expenses['referral_earning'] += data_get($item, 'referral_earning', 0);
                    }
                }

                if (!$found) {
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }

        }
        elseif ($deterministic == 'year') {
            foreach ($all_expenses_and_earnings as $item) {
                $admin_commission = data_get($item, 'admin_commission', 0); // Use data_get with a default value

                $chart_data['earnings'][] = with_decimal_point($admin_commission + data_get($item, 'earning', 0));
                $chart_data['expenses'][] = with_decimal_point(
                    data_get($item, 'discount_by_admin', 0) +
                    data_get($item, 'coupon_discount_by_admin', 0) +
                    data_get($item, 'campaign_discount_by_admin', 0) +
                    data_get($item, 'bonus', 0) +
                    data_get($item, 'referral_discount', 0) +
                    data_get($item, 'referral_earning', 0)
                );
                $chart_data['timeline'][] = $item[$deterministic];

                $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                $all_expenses['referral_discount'] += data_get($item, 'referral_discount', 0);
                $all_expenses['referral_earning'] += data_get($item, 'referral_earning', 0);
            }
        }
        elseif ($deterministic == 'day') {
            if ($date_range == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($date_range == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($date_range == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d',strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found=0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['day'] == $i) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0) +
                            data_get($item, 'referral_discount', 0) +
                            data_get($item, 'referral_earning', 0)
                        );

                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                        $all_expenses['referral_discount'] += data_get($item, 'referral_discount', 0);
                        $all_expenses['referral_earning'] += data_get($item, 'referral_earning', 0);
                    }
                }
                if(!$found){
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }
        }
        elseif ($deterministic == 'week') {
            if ($date_range == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($date_range == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found=0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['day'] == $i) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0) +
                            data_get($item, 'referral_discount', 0) +
                            data_get($item, 'referral_earning', 0)
                        );
                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                        $all_expenses['referral_discount'] += data_get($item, 'referral_discount', 0);
                        $all_expenses['referral_earning'] += data_get($item, 'referral_earning', 0);
                    }
                }
                if(!$found){
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }
        }

        return view('adminmodule::admin.report.business.overview', compact('zones', 'categories', 'sub_categories', 'amounts', 'chart_data', 'all_expenses', 'deterministic', 'queryParams'));
    }

    public function getBusinessOverviewReportDownload(Request $request): StreamedResponse|string
    {
        $this->authorize('report_export');
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

        $date_range = $request['date_range'];
        if(is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic=='week'?'day':$deterministic;

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $query->ofBookingStatus('completed')
                    ->when(!empty($zoneIds), function ($query) use ($zoneIds) {
                        $query->whereIn('zone_id', $zoneIds);
                    })
                    ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                        $query->whereIn('category_id', $categoryIds);
                    })
                    ->when(!empty($subCategoryIds), function ($query) use ($subCategoryIds) {
                        $query->whereIn('sub_category_id', $subCategoryIds);
                    })
                    ->when($request->has('date_range'), function ($query) use ($request) {
                        $this->applyDateRangeConditions($query, $request);
                    });
            })->orWhereHas('repeat', function ($subQuery) use ($request, $zoneIds, $categoryIds, $subCategoryIds) {
                $subQuery->ofBookingStatus('completed')
                    ->when(!empty($zoneIds) || !empty($categoryIds) || !empty($subCategoryIds), function ($query) use ($zoneIds, $categoryIds, $subCategoryIds) {
                        $query->whereHas('booking', function ($bookingQuery) use ($zoneIds, $categoryIds, $subCategoryIds) {
                            $this->applyBookingReportFilters($bookingQuery, $zoneIds, $categoryIds, $subCategoryIds);
                        });
                    })
                    ->when($request->has('date_range'), function ($q) use ($request) {
                        $this->applyDateRangeConditions($q, $request);
                    });
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

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get();

        foreach ($amounts as $amount) {
            $total_earning = data_get($amount, 'admin_commission', 0) + data_get($amount, 'earning', 0);
            $total_expense = data_get($amount, 'discount_by_admin', 0)
                + data_get($amount, 'coupon_discount_by_admin', 0)
                + data_get($amount, 'campaign_discount_by_admin', 0)
                + data_get($amount, 'bonus', 0)
                + data_get($amount, 'referral_discount', 0)
                + data_get($amount, 'referral_earning', 0);

            $net_profit = $total_earning - $total_expense;
            $net_profit_rate = $total_earning != 0 ? ($net_profit * 100) / $total_earning : $net_profit * 100;

            $amount->total_earning = $total_earning;
            $amount->total_expense = $total_expense;
            $amount->net_profit = $net_profit;
            $amount->net_profit_rate = $net_profit_rate;
        }

        $rowIndex = 0;
        $fileName = 'business_overview_report_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($amounts))->download($fileName, function ($item) use ($deterministic, &$rowIndex) {
            $rowIndex++;

            if ($deterministic == 'month') {
                $duration = \DateTime::createFromFormat('!m', $item['month'])?->format('F') ?? $item['month'];
            } else {
                $duration = $item[$deterministic] ?? '';
            }

            return [
                translate('SL')              => $rowIndex,
                translate('Duration')        => $duration,
                translate('total_earning')   => with_currency_symbol($item['total_earning']),
                translate('Total_Expenses')  => with_currency_symbol($item['total_expense']),
                translate('Net_Profit')      => with_currency_symbol($item['net_profit']),
                translate('Net_Profit_Rate') => with_currency_symbol($item['net_profit_rate']) . ' %'];
        });
    }

    private function applyDateRangeConditions($query, $request): void
    {
        $now = Carbon::now();

        if ($request['date_range'] == 'custom_date') {
            $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            return;
        }

        switch ($request['date_range']) {
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'last_week':
                $startDate = $now->copy()->subWeek()->startOfWeek();
                $endDate = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_15_days':
                $startDate = $now->copy()->subDays(15)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'last_year':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate = $now->copy()->subYear()->endOfYear();
                break;
            case 'last_6_month':
                $startDate = $now->copy()->subMonths(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'this_year_1st_quarter':
                $startDate = $now->copy()->month(1)->startOfQuarter();
                $endDate = $now->copy()->month(1)->endOfQuarter();
                break;
            case 'this_year_2nd_quarter':
                $startDate = $now->copy()->month(4)->startOfQuarter();
                $endDate = $now->copy()->month(4)->endOfQuarter();
                break;
            case 'this_year_3rd_quarter':
                $startDate = $now->copy()->month(7)->startOfQuarter();
                $endDate = $now->copy()->month(7)->endOfQuarter();
                break;
            case 'this_year_4th_quarter':
                $startDate = $now->copy()->month(10)->startOfQuarter();
                $endDate = $now->copy()->month(10)->endOfQuarter();
                break;
            default:
                return;
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    private function normalizeFilterIds(array $ids): array
    {
        return array_values(array_filter(array_unique($ids), function ($id) {
            return !empty($id) && $id !== 'all';
        }));
    }

    private function applyTransactionReportFilters($query, array $zoneIds, array $categoryIds, array $subCategoryIds): void
    {
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
}
