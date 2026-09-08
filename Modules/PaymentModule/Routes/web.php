<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\PaymentModule\Http\Controllers\PaytmController;
use Modules\PaymentModule\Http\Controllers\PaymentController;
use Modules\PaymentModule\Http\Controllers\PaystackController;
use Modules\PaymentModule\Http\Controllers\RazorPayController;
use Modules\PaymentModule\Http\Controllers\SenangPayController;
use Modules\PaymentModule\Http\Controllers\FlutterwaveV3Controller;
use Modules\PaymentModule\Http\Controllers\StripePaymentController;
use Modules\PaymentModule\Http\Controllers\EdfaPayController;
use Modules\PaymentModule\Http\Controllers\SubscriptionPaymentController;
use Modules\PaymentModule\Http\Controllers\SslCommerzPaymentController;
use Modules\PaymentModule\Http\Controllers\Web\Admin\PaymentConfigController;


$isPublished = 0;
try {
    $fullData = include('Modules/Gateways/Addon/info.php');
    $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {}


Route::match(['get', 'post'],'payment', [PaymentController::class, 'index']);
Route::match(['get', 'post'],'payment/subscription', [SubscriptionPaymentController::class, 'index']);

if (!$isPublished) {
    Route::group(['prefix' => 'payment'], function () {
        Route::match(['get', 'post'],'/', [PaymentController::class, 'index']);

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success']);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed']);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled']);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')->withoutMiddleware([VerifyCsrfToken::class]);
            Route::post('callback', [RazorPayController::class, 'callback'])->name('callback')->withoutMiddleware([VerifyCsrfToken::class]);
            Route::any('cancel', [RazorPayController::class, 'cancel'])->name('cancel')->withoutMiddleware([VerifyCsrfToken::class]);
            Route::any('create-order', [RazorPayController::class, 'createOrder'])->name('create-order')->withoutMiddleware([VerifyCsrfToken::class]);
            Route::any('verify-payment', [RazorPayController::class, 'verifyPayment'])->name('verify-payment')->withoutMiddleware([VerifyCsrfToken::class]);
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'response'])->name('response');
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
        });
    });
}

// EdfaPay (always registered; works with or without Gateways addon)
Route::group(['prefix' => 'payment/edfapay', 'as' => 'edfapay.'], function () {
    Route::get('pay', [EdfaPayController::class, 'index'])->name('pay');
    Route::match(['get', 'post'], 'success', [EdfaPayController::class, 'success'])->name('success')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::match(['get', 'post'], 'fail', [EdfaPayController::class, 'fail'])->name('fail')->withoutMiddleware([VerifyCsrfToken::class]);
    // Dashboard webhook / callback URL
    Route::post('webhook', [EdfaPayController::class, 'webhook'])->name('webhook')->withoutMiddleware([VerifyCsrfToken::class]);
});

Route::get('payment-success', [PaymentController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentController::class, 'fail'])->name('payment-fail');

/** Admin */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::put('payment-set', [PaymentConfigController::class, 'setPaymentConfig'])->name('payment-set');
        Route::post('update-payment-status/{gateway}/{status}', [PaymentConfigController::class, 'UpdateStatus'])->name('update-payment-status');
    });
});
