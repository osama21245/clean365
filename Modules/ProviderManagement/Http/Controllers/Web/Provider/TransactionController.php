<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\TransactionModule\Entities\Transaction;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function pagination_limit;
use function with_currency_symbol;

class TransactionController extends Controller
{
    protected Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function index(Request $request): Renderable
    {
        $request->validate([
            'trx_type' => 'in:debit,credit,all']);

        $search = $request->has('search') ? $request['search'] : '';
        $trxType = $request->has('trx_type') ? $request['trx_type'] : 'all';
        $queryParam = ['search' => $search, 'trx_type' => $trxType];

        $transactions = $this->providerTransactionsQuery($request)
            ->paginate(pagination_limit())
            ->appends($queryParam);

        return view('providermanagement::provider.transaction.list', compact('transactions', 'trxType', 'search'));
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function download(Request $request): string|StreamedResponse
    {
        $request->validate([
            'trx_type' => 'in:debit,credit,all']);

        $items = $this->providerTransactionsQuery($request)->latest()->get();

        $rowIndex = 0;
        $fileName = 'provider_wallet_transactions_' . date('Y_m_d') . '.xlsx';
        return (new FastExcel($items))->download($fileName, function ($transaction) use (&$rowIndex) {
            $rowIndex++;

            $transactionFrom = '';
            if ($transaction?->from_user?->provider) {
                $transactionFrom = $transaction->from_user->provider->company_name ?? '';
            } elseif ($transaction->from_user) {
                $transactionFrom = trim(($transaction->from_user->first_name ?? '') . ' ' . ($transaction->from_user->last_name ?? ''));
            }
            if ($transaction->from_user_account) {
                $transactionFrom = trim($transactionFrom . ' (' . $transaction->from_user_account . ')');
            }

            $transactionTo = '';
            if ($transaction?->to_user?->provider) {
                $transactionTo = $transaction->to_user->provider->company_name ?? '';
            } elseif ($transaction->to_user) {
                $transactionTo = trim(($transaction->to_user->first_name ?? '') . ' ' . ($transaction->to_user->last_name ?? ''));
            }
            if ($transaction->to_user_account) {
                $transactionTo = trim($transactionTo . ' (' . $transaction->to_user_account . ')');
            }

            return [
                translate('SL')               => $rowIndex,
                translate('Transaction_ID')   => $transaction->id,
                translate('Transaction_Date') => $transaction->created_at ? format_time_by_business_settings($transaction->created_at, 'd-M-y') : '',
                translate('Transaction_From') => $transactionFrom,
                translate('Transaction_To')   => $transactionTo,
                translate('Transaction Type') => $transaction->trx_type,
                translate('Debit')            => with_currency_symbol($transaction->debit),
                translate('Credit')           => with_currency_symbol($transaction->credit),
                translate('Balance')          => with_currency_symbol($transaction->balance)];
        });
    }

    private function providerTransactionsQuery(Request $request)
    {
        return $this->transaction
            ->with(['from_user.provider', 'to_user.provider'])
            ->search($request['search'], ['id'])
            ->when($request->has('trx_type') && $request->trx_type != 'all', function ($query) use ($request) {
                if ($request->trx_type == 'debit') {
                    $query->where('debit', '!=', 0);
                } else {
                    $query->where('credit', '!=', 0);
                }
            })
            ->where(function ($query) {
                $query->where('to_user_id', auth()->user()->id)
                    ->orWhere('from_user_id', auth()->user()->id);
            })
            ->latest();
    }
}
