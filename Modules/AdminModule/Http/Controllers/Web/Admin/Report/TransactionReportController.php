<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report;

use Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use function pagination_limit;
use function view;
use function with_currency_symbol;

class TransactionReportController extends Controller
{
    protected Zone $zone;
    protected Provider $provider;
    protected Category $categories;
    protected Booking $booking;

    protected Account $account;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    use AuthorizesRequests;


    public function __construct(Zone $zone, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $booking_details_amount)
    {
        $this->zone = $zone;
        $this->provider = $provider;
        $this->categories = $categories;
        $this->booking = $booking;

        $this->service = $service;
        $this->account = $account;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->booking_details_amount = $booking_details_amount;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function getTransactionReport(Request $request): Renderable
    {
        $this->authorize('report_view');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'provider_ids' => 'array',
            'provider_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'filter_by' => 'in:collect_cash,payment,withdraw,commission,all',

            'transaction_type' => 'in:all,debit,credit'
        ]);

        $zones = $this->zone->select('id', 'name')->get();
        $providers = $this->provider->ofApproval(1)->select('id', 'company_name', 'company_phone')->get();

        //params
        $queryParams = ['transaction_type' => $request->input('transaction_type', 'all')];
        $queryParams += $request->only(['search', 'zone_ids', 'provider_ids', 'date_range', 'filter_by']);
        if ($request['date_range'] === 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        //card data
        $adminAccount = Account::where('user_id', Auth::user()->id)->first();
        $commission_earning = BookingDetailsAmount::where(function ($query) {
            $query->whereHas('booking', function ($subQuery) {
                $subQuery->ofBookingStatus('completed');
            })->orWhereHas('repeat', function ($subQuery) {
                $subQuery->ofBookingStatus('completed');
            });
        })->sum('admin_commission');

        $subscription_amounts = $this->transaction->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift'])->sum('credit');

        $extra_fee = $this->transaction
            ->where('trx_type', TRX_TYPE['received_extra_fee'])
            ->sum('credit');

        $adminTotalEarning = $commission_earning + $subscription_amounts + $extra_fee;

        //table data
        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user.provider', 'to_user.provider','repeat'])
            ->when($request->has('transaction_type') && $request->transaction_type != 'all', function ($query) use ($request) {
                if ($request->transaction_type == 'debit') {
                    $query->where('debit', '!=', 0);
                } elseif ($request->transaction_type == 'credit') {
                    $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                });
            })
            ->when($request->has('provider_ids'), function ($query) use ($request) {
                $query->whereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when($request->has('filter_by') && $request['filter_by'] != 'all', function ($query) use ($request) {
                $query->where('trx_type', $request['filter_by']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.report.transaction', compact('zones', 'providers', 'filteredTransactions', 'adminAccount', 'commission_earning', 'adminTotalEarning','extra_fee', 'queryParams'));
    }


    /**
     * @param Request $request
     * @return StreamedResponse|string
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException|AuthorizationException
     */
    public function downloadTransactionReport(Request $request): StreamedResponse|string
    {
        $this->authorize('report_export');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'provider_ids' => 'array',
            'provider_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'filter_by' => 'in:collect_cash,payment,withdraw,commission,all',

            'transaction_type' => 'in:all,debit,credit'
        ]);

        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user.provider', 'to_user.provider', 'repeat'])
            ->when($request->has('transaction_type') && $request->transaction_type != 'all', function ($query) use ($request) {
                if ($request->transaction_type == 'debit') {
                    $query->where('debit', '!=', 0);
                } elseif ($request->transaction_type == 'credit') {
                    $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                });
            })
            ->when($request->has('provider_ids'), function ($query) use ($request) {
                $query->whereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when($request->has('filter_by') && $request['filter_by'] != 'all', function ($query) use ($request) {
                $query->where('trx_type', $request['filter_by']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        $rowIndex = 0;
        $fileName = 'transaction_report_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($filteredTransactions))->download($fileName, function ($transaction) use (&$rowIndex) {
            $rowIndex++;
            $transactionTo = '';
            if (isset($transaction->to_user) && isset($transaction->to_user->provider)) {
                $transactionTo = $transaction->to_user->provider->company_name ?? '';
            } elseif (isset($transaction->to_user)) {
                $transactionTo = trim(($transaction->to_user->first_name ?? '') . ' ' . ($transaction->to_user->last_name ?? ''));
            }

            return [
                translate('SL')               => $rowIndex,
                translate('Transaction_ID')   => $transaction->id,
                translate('Transaction_Date') => $transaction->created_at ? date('d-M-Y h:ia', strtotime($transaction->created_at)) : '',
                translate('Transaction_To')   => $transactionTo,
                translate('Debit')            => with_currency_symbol($transaction->debit),
                translate('Credit')           => with_currency_symbol($transaction->credit),
                translate('Balance')          => with_currency_symbol($transaction->balance),
                translate('Transaction Type') => str_replace('_', ' ', $transaction->trx_type ?? '')];
        });
    }

    private function applyDateRangeConditions($query, $request): void
    {
        $now = Carbon::now();

        if ($request['date_range'] == 'custom_date') {
            $startDate = Carbon::parse($request['from'])->startOfDay();
            $endDate = Carbon::parse($request['to'])->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
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

}
