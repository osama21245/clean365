<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerModule\Http\Controllers\Web\Admin\CustomerController;
use Modules\CustomerModule\Http\Controllers\PagesController;

Route::group(['prefix' => 'pages'], function () {
    Route::get('about-us', [PagesController::class, 'aboutUs'])->name('about-us');
    Route::get('privacy-policy', [PagesController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('terms-and-conditions', [PagesController::class, 'termsAndConditions'])->name('terms-and-conditions');
    Route::get('refund-policy', [PagesController::class, 'refundPolicy'])->name('refund-policy');
    Route::get('return-policy', [PagesController::class, 'returnPolicy'])->name('return-policy');
    Route::get('cancellation-policy', [PagesController::class, 'cancellationPolicy'])->name('cancellation-policy');
});

Route::get('about-us', [PagesController::class, 'aboutUs']);
Route::get('privacy-policy', [PagesController::class, 'privacyPolicy']);
Route::get('terms-and-conditions', [PagesController::class, 'termsAndConditions']);
Route::get('refund-policy', [PagesController::class, 'refundPolicy']);
Route::get('return-policy', [PagesController::class, 'returnPolicy']);
Route::get('cancellation-policy', [PagesController::class, 'cancellationPolicy']);


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::any('list', [CustomerController::class, 'index'])->name('index');
        Route::any('create', [CustomerController::class, 'create'])->name('create');
        Route::post('store', [CustomerController::class, 'store'])->name('store');
        Route::any('detail/{id}', [CustomerController::class, 'show'])->name('detail');
        Route::get('edit/{id}', [CustomerController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [CustomerController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [CustomerController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [CustomerController::class, 'destroy'])->name('delete');
        Route::any('download', [CustomerController::class, 'download'])->name('download');
    });
});
