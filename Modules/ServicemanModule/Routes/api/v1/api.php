<?php

use Illuminate\Support\Facades\Route;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Serviceman\ConfigController as ServicemanConfigController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\ServicemanController as ServicemanProviderController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\TeamController as ProviderTeamController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Serviceman\ServicemanController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops\DashboardController as OpsDashboardController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops\TeamController as OpsTeamController;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Provider\Ops\JobController as OpsJobController;
use App\Http\Controllers\Api\V1\FieldAgentLocationController;

//provider routes
Route::group(['prefix' => 'provider', 'as' => 'provider', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api', 'update-field-agent-last-seen']], function () {

    //serviceman
    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.'], function () {
        Route::get('/', [ServicemanProviderController::class, 'index']);
        Route::post('/', [ServicemanProviderController::class, 'store']);
        Route::get('{id}/edit', [ServicemanProviderController::class, 'edit']);
        Route::put('{id}', [ServicemanProviderController::class, 'update']);
        Route::get('{id}', [ServicemanProviderController::class, 'show']);

        Route::delete('delete', [ServicemanProviderController::class, 'destroy']);
        Route::put('status/update', [ServicemanProviderController::class, 'changeActiveStatus']);
    });

    Route::group(['prefix' => 'team', 'as' => 'team.'], function () {
        Route::get('/', [ProviderTeamController::class, 'index']);
        Route::post('/', [ProviderTeamController::class, 'store']);
        Route::get('{id}', [ProviderTeamController::class, 'show']);
        Route::put('{id}', [ProviderTeamController::class, 'update']);
        Route::delete('{id}', [ProviderTeamController::class, 'destroy']);
        Route::put('assign-booking/{booking_id}', [ProviderTeamController::class, 'assignToBooking']);
    });

    Route::group(['prefix' => 'ops', 'as' => 'ops.'], function () {
        Route::get('dashboard', [OpsDashboardController::class, 'index']);

        Route::get('teams', [OpsTeamController::class, 'index']);
        Route::get('teams/{id}', [OpsTeamController::class, 'show']);

        Route::get('jobs', [OpsJobController::class, 'index']);
        Route::get('jobs/{booking_id}', [OpsJobController::class, 'show']);
        Route::put('jobs/{booking_id}/seen', [OpsJobController::class, 'markSeen']);
        Route::put('jobs/{booking_id}/field-status', [OpsJobController::class, 'updateFieldStatus']);
        Route::put('jobs/{booking_id}/progress', [OpsJobController::class, 'updateProgress']);
        Route::post('jobs/{booking_id}/images/{stage}', [OpsJobController::class, 'uploadImages']);
        Route::post('jobs/{booking_id}/notes', [OpsJobController::class, 'storeNote']);
        Route::get('jobs/{booking_id}/attendance', [OpsJobController::class, 'attendance']);
        Route::post('jobs/{booking_id}/attendance', [OpsJobController::class, 'storeAttendance']);
        Route::post('jobs/{booking_id}/support', [OpsJobController::class, 'storeSupport']);
    });

});

//customer section
Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'namespace' => 'Api\V1\Serviceman'], function () {

    Route::post('forgot-password', [ServicemanController::class, 'forgotPassword']);
    Route::post('otp-verification', [ServicemanController::class, 'otpVerification']);
    Route::put('reset-password', [ServicemanController::class, 'resetPassword']);

    Route::group(['middleware' => ['auth:api', 'update-field-agent-last-seen']], function () {
        Route::put('update-location', [FieldAgentLocationController::class, 'update'])
            ->middleware('throttle-location-writes');

        Route::get('dashboard', [ServicemanController::class, 'dashboard']);
        Route::get('dashboard/booking-statistics', [ServicemanController::class, 'bookingStatistics']);

        Route::group(['prefix' => 'config'], function () {
            Route::get('/', [ServicemanConfigController::class, 'configuration'])->withoutMiddleware(['auth:api', 'update-field-agent-last-seen']);
            Route::get('page-details/{key}', [ServicemanConfigController::class, 'pageDetails'])->withoutMiddleware(['auth:api', 'update-field-agent-last-seen']);

            Route::get('get-zone-id', [ServicemanConfigController::class, 'getZone']);
            Route::get('place-api-autocomplete', [ServicemanConfigController::class, 'placeApiAutocomplete']);
            Route::get('distance-api', [ServicemanConfigController::class, 'distanceApi']);
            Route::get('place-api-details', [ServicemanConfigController::class, 'placeApiDetails']);
            Route::get('geocode-api', [ServicemanConfigController::class, 'geocodeApi']);
            Route::get('get-routes', [ServicemanConfigController::class, 'getRoutes']);
        });

        Route::get('info', [ServicemanController::class, 'index']);
        Route::put('update/profile', [ServicemanController::class, 'updateProfile']);
        Route::put('update/fcm-token', [ServicemanController::class, 'updateFcmToken']);
        Route::post('broadcasts/ack', [\App\Http\Controllers\Api\V1\AdBroadcastAckController::class, 'store']);
        Route::get('inbox-notifications', [\App\Http\Controllers\Api\V1\UserInboxNotificationController::class, 'index']);
        Route::get('inbox-notifications/{id}', [\App\Http\Controllers\Api\V1\UserInboxNotificationController::class, 'show']);
        Route::get('push-notifications', [ServicemanController::class, 'pushNotifications']);

        Route::group(['prefix' => 'profile', 'middleware' => ['auth:api']], function () {
            Route::put('info', [ServicemanController::class, 'profileInfo']);
            Route::put('change-password', [ServicemanController::class, 'changePassword']);
        });
    });

    Route::post('change-language', [ServicemanController::class, 'changeLanguage']);
});
