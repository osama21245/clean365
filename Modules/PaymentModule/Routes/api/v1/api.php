<?php

Route::group(['prefix' => 'admin', 'as'=>'admin.', 'namespace' => 'Api\V1\Admin','middleware'=>['auth:api']], function () {
    Route::group(['prefix'=>'payment-config'],function (){
        Route::get('get', [PaymentConfigController::class, 'payment_config_get']);
        Route::put('set', [PaymentConfigController::class, 'payment_config_set']);
    });
});
