<?php

use Illuminate\Support\Facades\Route;
use Modules\BlogModule\Http\Controllers\Api\V1\Customer\ArticleController;

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'middleware' => ['api']], function () {
    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('{slug}', [ArticleController::class, 'show']);
    });
});

Route::group(['prefix' => 'landing', 'as' => 'landing.', 'middleware' => ['api']], function () {
    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('{slug}', [ArticleController::class, 'show']);
    });
});
