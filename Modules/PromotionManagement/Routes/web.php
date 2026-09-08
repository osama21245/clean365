<?php

use Illuminate\Support\Facades\Route;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\DiscountController;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\CouponController;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\BannerController;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\BeforeAfterController;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\PushNotificationController;
use Modules\PromotionManagement\Http\Controllers\Web\Admin\AdBroadcastController;



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
        Route::any('create', [DiscountController::class, 'create'])->name('create');
        Route::any('list', [DiscountController::class, 'index'])->name('list');
        Route::post('store', [DiscountController::class, 'store'])->name('store');
        Route::get('edit/{id}', [DiscountController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [DiscountController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [DiscountController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [DiscountController::class, 'destroy'])->name('delete');
        Route::any('download', [DiscountController::class, 'download'])->name('download');
    });

    Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
        Route::any('create', [CouponController::class, 'create'])->name('create');
        Route::any('list', [CouponController::class, 'index'])->name('list');
        Route::post('store', [CouponController::class, 'store'])->name('store');
        Route::get('edit/{id}', [CouponController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [CouponController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [CouponController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [CouponController::class, 'destroy'])->name('delete');
        Route::any('download', [CouponController::class, 'download'])->name('download');
    });

    Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
        Route::any('create', [BannerController::class, 'create'])->name('create');
        Route::post('store', [BannerController::class, 'store'])->name('store');
        Route::get('edit/{id}', [BannerController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [BannerController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [BannerController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [BannerController::class, 'destroy'])->name('delete');
        Route::any('download', [BannerController::class, 'download'])->name('download');
    });

    Route::group(['prefix' => 'before-after', 'as' => 'before-after.'], function () {
        Route::any('create', [BeforeAfterController::class, 'create'])->name('create');
        Route::post('store', [BeforeAfterController::class, 'store'])->name('store');
        Route::get('edit/{id}', [BeforeAfterController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [BeforeAfterController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [BeforeAfterController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [BeforeAfterController::class, 'destroy'])->name('delete');
    });

    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.'], function () {
        Route::any('create', [PushNotificationController::class, 'create'])->name('create');
        Route::post('store', [PushNotificationController::class, 'store'])->name('store');
        Route::get('edit/{id}', [PushNotificationController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [PushNotificationController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [PushNotificationController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [PushNotificationController::class, 'destroy'])->name('delete');
        Route::any('download', [PushNotificationController::class, 'download'])->name('download');
        Route::get('resend/{id}', [PushNotificationController::class, 'resendNotification'])->name('resend');
    });

    Route::group(['prefix' => 'ad-broadcast', 'as' => 'ad-broadcast.'], function () {
        Route::get('/', [AdBroadcastController::class, 'index'])->name('index');
        Route::get('create', [AdBroadcastController::class, 'create'])->name('create');
        Route::post('store', [AdBroadcastController::class, 'store'])->name('store');
        Route::put('ai-push', [AdBroadcastController::class, 'updateAiPush'])->name('update-ai-push');
    });

});

