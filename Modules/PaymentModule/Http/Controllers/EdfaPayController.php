<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\Processor;
use Modules\UserManagement\Entities\UserAddress;

class EdfaPayController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('edfapay', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid']);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $apiKey = $this->config_values->api_key ?? env('EDFAPAY_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['message' => 'EdfaPay API key is not configured'], 400);
        }

        $payer = json_decode($data['payer_information']);
        $additional = json_decode($data['additional_data'] ?? '{}', true) ?: [];
        $businessName = (business_config('business_name', 'business_information'))->live_values ?? 'Clean365';
        $orderId = preg_replace('/[^A-Za-z0-9_\-]/', '_', $businessName) . '_' . $data->id;

        $phone = $this->formatPhone($payer->phone ?? '');
        $email = !empty($payer->email) ? $payer->email : ('customer+' . substr($data->id, 0, 8) . '@edfapay.com');
        $name = trim($payer->name ?? 'Customer') ?: 'Customer';

        $address = $this->resolveAddress($additional, $payer);

        $amount = round((float)$data->payment_amount, 2);
        $payload = [
            'orderId' => $orderId,
            'currency' => strtoupper($data->currency_code ?: 'SAR'),
            'amount' => $amount,
            'customerDetails' => [
                'name' => $name,
                'email' => $email,
                'phone' => $phone],
            'address' => $address,
            'invoice' => [
                'shippingCharges' => 0,
                'extraCharges' => 0,
                'extraDiscount' => 0,
                'total' => $amount,
                'url' => url('/'),
                'lineItems' => [
                    [
                        'sku' => 'BK-' . substr($data->id, 0, 8),
                        'description' => 'Booking payment',
                        'unitCost' => $amount,
                        'quantity' => 1,
                        'netTotal' => $amount,
                        'discountRate' => 0,
                        'discountAmount' => 0,
                        'taxRate' => 0,
                        'taxTotal' => 0,
                        'total' => $amount]]],
            'recurringInit' => 'N',
            'auth' => 'N',
            'successUrl' => route('edfapay.success', ['payment_id' => $data->id]),
            'failureUrl' => route('edfapay.fail', ['payment_id' => $data->id]),
            'paymentMethod' => 'card',
            'issueDate' => now()->format('Y-m-d\TH:i:s.u'),
            'expireDate' => now()->addDays(7)->format('Y-m-d\TH:i:s.u')];

        try {
            $response = Http::withHeaders([
                'accept' => '*/*',
                'X-API-KEY' => $apiKey,
                'Content-Type' => 'application/json'])->timeout(30)->post('https://app-api.edfapay.com/api/v1/payment-gateway/initiate', $payload);

            $result = $response->json();

            if ($response->successful() && ($result['code'] ?? null) == 200 && !empty($result['data']['redirectUrl'])) {
                $sessionId = $result['data']['id'] ?? null;
                $additional['edfapay_order_id'] = $orderId;
                $this->payment::where(['id' => $data->id])->update([
                    'transaction_id' => $sessionId ?: $orderId,
                    'additional_data' => json_encode($additional)]);

                return redirect()->away($result['data']['redirectUrl']);
            }

            Log::error('EdfaPay initiate failed', [
                'payment_id' => $data->id,
                'status' => $response->status(),
                'body' => $result]);

            if (function_exists($data->failure_hook)) {
                call_user_func($data->failure_hook, $data);
            }

            return $this->payment_response($data, 'fail');
        } catch (\Throwable $e) {
            Log::error('EdfaPay initiate exception: ' . $e->getMessage(), ['payment_id' => $data->id]);

            if (function_exists($data->failure_hook)) {
                call_user_func($data->failure_hook, $data);
            }

            return $this->payment_response($data, 'fail');
        }
    }

    public function success(Request $request)
    {
        $payment = $this->payment::where(['id' => $request['payment_id']])->first();
        if (!isset($payment)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $this->markPaymentSuccessful(
            $payment,
            $payment->transaction_id ?: ($request['sessionId'] ?? $request['transactionId'] ?? $payment->id)
        );

        $payment = $this->payment::where(['id' => $payment->id])->first();

        return $this->renderPaymentResult($payment, true);
    }

    public function fail(Request $request)
    {
        $payment = $this->payment::where(['id' => $request['payment_id']])->first();
        if (!isset($payment)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        // If webhook already marked paid, treat browser return as success.
        if ((int) $payment->is_paid === 1) {
            return $this->renderPaymentResult($payment, true);
        }

        if (function_exists($payment->failure_hook)) {
            call_user_func($payment->failure_hook, $payment);
        }

        return $this->renderPaymentResult($payment, false);
    }

    /**
     * Dashboard webhook endpoint.
     * Configure in EdfaPay Dashboard → Settings → Callback URL / Webhook:
     * https://api.clean365.sa/payment/edfapay/webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('EdfaPay webhook received', $payload);

        $status = (string) ($payload['status'] ?? '');
        $orderId = (string) ($payload['orderId'] ?? '');
        $transactionId = (string) ($payload['transactionId'] ?? '');

        // Intermediate statuses: acknowledge only, do not finalize booking.
        if (in_array($status, ['Redirect', 'Pending'], true)) {
            return response()->json(['message' => 'received', 'status' => $status], 200);
        }

        $payment = $this->findPaymentFromWebhook($orderId, $transactionId);
        if (!$payment) {
            Log::warning('EdfaPay webhook payment not found', [
                'orderId' => $orderId,
                'transactionId' => $transactionId,
                'status' => $status]);

            return response()->json(['message' => 'received'], 200);
        }

        if (in_array(strtolower($status), ['approved', 'completed', 'success', 'captured'], true)
            || str_contains(strtoupper($status), 'APPROVED')
            || str_contains(strtoupper($status), 'COMPLETED')) {
            $this->markPaymentSuccessful($payment, $transactionId ?: $payment->transaction_id);
        } elseif (in_array($status, ['Declined', 'Failed', 'Cancelled', 'Canceled', 'Void'], true)
            || str_contains(strtoupper($status), 'DECLINED')
            || str_contains(strtoupper($status), 'FAILED')) {
            if ((int) $payment->is_paid !== 1 && function_exists($payment->failure_hook)) {
                call_user_func($payment->failure_hook, $payment);
            }
        }

        return response()->json(['message' => 'ok', 'status' => $status], 200);
    }

    private function markPaymentSuccessful(PaymentRequest $payment, ?string $transactionId = null): void
    {
        if ((int) $payment->is_paid === 1) {
            return;
        }

        $this->payment::where(['id' => $payment->id])->update([
            'payment_method' => 'edfapay',
            'is_paid' => 1,
            'transaction_id' => $transactionId ?: $payment->transaction_id ?: $payment->id]);

        $payment = $this->payment::where(['id' => $payment->id])->first();
        if ($payment && function_exists($payment->success_hook)) {
            call_user_func($payment->success_hook, $payment);
        }
    }

    private function renderPaymentResult(PaymentRequest $payment, bool $isSuccess)
    {
        $additional = json_decode($payment->additional_data ?? '{}', true) ?: [];
        $bookingId = $additional['booking_id'] ?? $payment->attribute_id;
        $callback = $payment->external_redirect_link ?: ($additional['callback'] ?? null);

        $locale = strtolower((string) (
            request()->query('lang')
            ?? request()->header('X-localization')
            ?? request()->header('localization')
            ?? ($additional['locale'] ?? 'en')
        ));
        $locale = in_array($locale, ['ar', 'en'], true) ? $locale : 'en';
        app()->setLocale($locale);

        $bookingReadableId = null;
        if ($bookingId) {
            $booking = \Modules\BookingModule\Entities\Booking::find($bookingId);
            $bookingReadableId = $booking?->readable_id;
        }

        $redirectUrl = null;
        if (!empty($callback)) {
            $separator = str_contains($callback, '?') ? '&' : '?';
            $redirectUrl = $callback . $separator . 'flag=' . ($isSuccess ? 'success' : 'fail')
                . '&payment_method=edfapay'
                . '&lang=' . $locale
                . ($bookingId ? '&booking_id=' . urlencode((string) $bookingId) : '')
                . ($bookingReadableId ? '&readable_id=' . urlencode((string) $bookingReadableId) : '');
        }

        $copy = $locale === 'ar'
            ? [
                'title_success' => 'تم الدفع بنجاح',
                'title_fail' => 'فشل الدفع',
                'body_success' => 'تم تأكيد عملية الدفع الخاصة بحجزك بنجاح.',
                'body_fail' => 'لم تكتمل عملية الدفع. يمكنك المحاولة مرة أخرى من التطبيق.',
                'booking_label' => 'رقم الحجز',
                'btn_success' => 'عرض طلباتي',
                'btn_fail' => 'العودة',
                'btn_app' => 'العودة للتطبيق',
                'hint_redirect' => 'سيتم تحويلك تلقائيًا خلال ثوانٍ...',
                'hint_app' => 'يمكنك إغلاق هذه الصفحة والعودة للتطبيق لمتابعة طلباتك.']
            : [
                'title_success' => 'Payment Successful',
                'title_fail' => 'Payment Failed',
                'body_success' => 'Your booking payment has been confirmed successfully.',
                'body_fail' => 'Payment was not completed. You can try again from the app.',
                'booking_label' => 'Booking No.',
                'btn_success' => 'View My Orders',
                'btn_fail' => 'Go Back',
                'btn_app' => 'Back to App',
                'hint_redirect' => 'You will be redirected automatically in a few seconds...',
                'hint_app' => 'You can close this page and return to the app to follow your orders.'];

        return response()->view('paymentmodule::edfapay-result', [
            'isSuccess' => $isSuccess,
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            'copy' => $copy,
            'paymentMethod' => 'edfapay',
            'bookingId' => $bookingId,
            'bookingReadableId' => $bookingReadableId,
            'transactionId' => $payment->transaction_id,
            'redirectUrl' => $redirectUrl]);
    }

    private function findPaymentFromWebhook(string $orderId, string $transactionId): ?PaymentRequest
    {
        if ($transactionId !== '') {
            $byTxn = $this->payment::where('transaction_id', $transactionId)->first();
            if ($byTxn) {
                return $byTxn;
            }
        }

        if ($orderId !== '') {
            $byOrderTxn = $this->payment::where('transaction_id', $orderId)->first();
            if ($byOrderTxn) {
                return $byOrderTxn;
            }

            // orderId format: CompanyName_{payment_request_uuid}
            if (preg_match('/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})$/', $orderId, $matches)) {
                $byId = $this->payment::where('id', $matches[1])->first();
                if ($byId) {
                    return $byId;
                }
            }

            $byAdditional = $this->payment::where('additional_data', 'like', '%' . $orderId . '%')->latest()->first();
            if ($byAdditional) {
                return $byAdditional;
            }
        }

        return null;
    }

    private function formatPhone(?string $phone): string
    {
        $phone = preg_replace('/\s+/', '', (string)$phone);
        if ($phone === '') {
            return '+966500000000';
        }
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }
        if (str_starts_with($phone, '05')) {
            $phone = '+966' . substr($phone, 1);
        } elseif (str_starts_with($phone, '5') && strlen($phone) === 9) {
            $phone = '+966' . $phone;
        } elseif (str_starts_with($phone, '966') && !str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    private function resolveAddress(array $additional, object $payer): array
    {
        $defaults = [
            'country' => 'SA',
            'state' => 'Riyadh',
            'city' => 'Riyadh',
            'address' => ($payer->address ?? '') ?: 'Saudi Arabia',
            'zip' => '00000'];

        $serviceAddressId = $additional['service_address_id'] ?? null;
        if (!$serviceAddressId) {
            return $defaults;
        }

        $userAddress = UserAddress::find($serviceAddressId);
        if (!$userAddress) {
            return $defaults;
        }

        return [
            'country' => $userAddress->country ?: 'SA',
            'state' => $userAddress->street ?: $defaults['state'],
            'city' => $userAddress->city ?: $defaults['city'],
            'address' => $userAddress->address ?: $defaults['address'],
            'zip' => $userAddress->zip_code ?: $defaults['zip']];
    }
}