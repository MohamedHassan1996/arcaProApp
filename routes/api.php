<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Dashboard\Auth\AuthController;
use App\Http\Controllers\Api\V1\Dashboard\Event\EventCalendarController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\OperatorMaintenanceControllerTwo;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\OperatorMaintenanceReportController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\PeriodicMaintenanceController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\PeriodicMaintenanceStatsController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\ProductMaintenanceHistoryController;
use App\Http\Controllers\Api\V1\Dashboard\Maintenance\SendReportToClientController;
use App\Http\Controllers\Api\V1\Dashboard\User\ChangeForgetPasswordController;
use App\Http\Controllers\Api\V1\Dashboard\User\UserController;
use App\Http\Controllers\Api\V1\Select\SelectController;
use App\Http\Controllers\Api\V1\Dashboard\User\UserProfileController;
use App\Http\Controllers\Api\V1\Dashboard\User\ChangePasswordController;
use App\Http\Controllers\Api\V1\Dashboard\User\ForgetPasswordController;
use App\Http\Controllers\Api\V1\Dashboard\User\VerifyOtpController;
use App\Http\Controllers\Api\V1\Dashboard\VehicleStock\VehicleStockController;

Route::prefix('v1/')->group(function () {

    // Auth
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login','login');
        Route::post('/logout','logout');
    });

    Route::put('auth/forget-password', ForgetPasswordController::class);
    Route::put('auth/change-forget-password', ChangeForgetPasswordController::class);
    Route::get('verify-otp', VerifyOtpController::class);

    // Users
    Route::apiResource('users', UserController::class);
    Route::apiSingleton('user-profile', UserProfileController::class);
    Route::put('user-profile/change-password', ChangePasswordController::class);

    // Select
    Route::prefix('selects')->group(function(){
        Route::get('', [SelectController::class, 'getSelects']);
    });

    Route::prefix('operator-maintenances')->group(function(){
        Route::get('', [OperatorMaintenanceControllerTwo::class, 'index']);
        Route::get('{guid}', [OperatorMaintenanceControllerTwo::class, 'show']);
    });

    Route::prefix('periodic-maintenances')->group(function(){
        Route::get('', [PeriodicMaintenanceController::class, 'index']);
        Route::get('{id}', [OperatorMaintenanceControllerTwo::class, 'show']);
    });

    Route::prefix('periodic-maintenance-stats')->group(function(){
        Route::get('', [PeriodicMaintenanceStatsController::class, 'index']);
    });

    Route::prefix('vehicle-stocks')->group(function(){
        Route::get('', [VehicleStockController::class, 'index']);
    });

    Route::prefix('operator-maintenance-reports')->group(function(){
        Route::post('', [OperatorMaintenanceReportController::class, 'store']);
    });

    Route::prefix('events')->group(function(){
        Route::get('', [EventCalendarController::class, 'index']);
    });

    Route::prefix('product-maintenance-history')->group(function(){
        Route::get('', [ProductMaintenanceHistoryController::class, 'index']);
    });

    Route::prefix('maintenance-request')->group(function(){
        Route::post('', [MaintenanceRequestController::class, 'store']);
    });

    Route::prefix('selects')->group(function(){
        Route::get('', [SelectController::class, 'getSelects']);
    });

    Route::post('send-maintenance-report', SendReportToClientController::class);


});
