<?php

use Illuminate\Support\Facades\Route;
use Modules\BlogModule\Http\Controllers\Web\Admin\ArticleController;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
        Route::get('list', [ArticleController::class, 'index'])->name('list');
        Route::get('create', [ArticleController::class, 'create'])->name('create');
        Route::post('store', [ArticleController::class, 'store'])->name('store');
        Route::get('edit/{id}', [ArticleController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [ArticleController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [ArticleController::class, 'destroy'])->name('delete');
        Route::any('status-update/{id}', [ArticleController::class, 'statusUpdate'])->name('status-update');
        Route::get('automation', [ArticleController::class, 'automation'])->name('automation');
        Route::match(['post', 'put'], 'automation', [ArticleController::class, 'updateAutomation'])->name('automation.update');
        Route::post('generate-now', [ArticleController::class, 'generateNow'])->name('generate-now');
    });
});
