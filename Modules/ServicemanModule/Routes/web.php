<?php

use Illuminate\Support\Facades\Route;
use Modules\ServicemanModule\Http\Controllers\Web\Admin\ServicemanController as AdminServicemanController;
use Modules\ServicemanModule\Http\Controllers\Web\Admin\TeamController as AdminTeamController;
use Modules\ServicemanModule\Http\Controllers\Web\Admin\LiveMapController as AdminLiveMapController;
use Modules\ServicemanModule\Http\Controllers\Web\Provider\ServicemanController;
use Modules\ServicemanModule\Http\Controllers\Web\Provider\TeamController;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'live-map', 'as' => 'live-map.'], function () {
        Route::get('/', [AdminLiveMapController::class, 'index'])->name('index');
        Route::get('/api', [AdminLiveMapController::class, 'api'])->name('api');
    });

    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.'], function () {
        Route::any('/list', [AdminServicemanController::class, 'index'])->name('list');
        Route::get('create', [AdminServicemanController::class, 'create'])->name('create');
        Route::post('store', [AdminServicemanController::class, 'store'])->name('store');
        Route::get('show/{id}', [AdminServicemanController::class, 'show'])->name('show');
        Route::get('edit/{id}', [AdminServicemanController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [AdminServicemanController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [AdminServicemanController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [AdminServicemanController::class, 'destroy'])->name('delete');
        Route::any('download', [AdminServicemanController::class, 'download'])->name('download');
        Route::delete('remove-image', [AdminServicemanController::class, 'remove_image'])->name('remove-image');
    });

    Route::group(['prefix' => 'team', 'as' => 'team.'], function () {
        Route::any('/list', [AdminTeamController::class, 'index'])->name('list');
        Route::get('create', [AdminTeamController::class, 'create'])->name('create');
        Route::post('store', [AdminTeamController::class, 'store'])->name('store');
        Route::get('show/{id}', [AdminTeamController::class, 'show'])->name('show');
        Route::get('edit/{id}', [AdminTeamController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [AdminTeamController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [AdminTeamController::class, 'destroy'])->name('delete');
        Route::any('status-update/{id}', [AdminTeamController::class, 'statusUpdate'])->name('status-update');
        Route::put('assign-booking/{booking_id}', [AdminTeamController::class, 'assignToBooking'])->name('assign-booking');
        Route::get('servicemen-by-provider', [AdminTeamController::class, 'servicemenByProvider'])->name('servicemen-by-provider');
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.'], function () {
        Route::any('/list', [ServicemanController::class, 'index'])->name('list');
        Route::get('create', [ServicemanController::class, 'create'])->name('create');
        Route::post('store', [ServicemanController::class, 'store'])->name('store');
        Route::get('show/{id}', [ServicemanController::class, 'show'])->name('show');
        Route::get('edit/{id}', [ServicemanController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [ServicemanController::class, 'update'])->name('update');
        Route::any('status-update/{id}', [ServicemanController::class, 'statusUpdate'])->name('status-update');
        Route::delete('delete/{id}', [ServicemanController::class, 'destroy'])->name('delete');
        Route::any('download', [ServicemanController::class, 'download'])->name('download');
        Route::delete('remove-image', [ServicemanController::class, 'remove_image'])->name('remove-image');
    });

    Route::group(['prefix' => 'team', 'as' => 'team.'], function () {
        Route::any('/list', [TeamController::class, 'index'])->name('list');
        Route::get('create', [TeamController::class, 'create'])->name('create');
        Route::post('store', [TeamController::class, 'store'])->name('store');
        Route::get('show/{id}', [TeamController::class, 'show'])->name('show');
        Route::get('edit/{id}', [TeamController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [TeamController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [TeamController::class, 'destroy'])->name('delete');
        Route::any('status-update/{id}', [TeamController::class, 'statusUpdate'])->name('status-update');
        Route::put('assign-booking/{booking_id}', [TeamController::class, 'assignToBooking'])->name('assign-booking');
    });
});
