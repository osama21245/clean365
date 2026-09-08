<?php

use Illuminate\Support\Facades\Route;
use Modules\TransactionModule\Http\Controllers\Web\Admin\TransactionController;


Route::group(['prefix' => 'admin', 'as'=>'admin.', 'namespace' => 'Web\Admin','middleware'=>['admin']], function () {

    Route::group(['prefix' => 'transaction', 'as'=>'transaction.'], function () {
        Route::any('list', [TransactionController::class, 'index'])->name('list');
        Route::any('download', [TransactionController::class, 'download'])->name('download');
    });

});
