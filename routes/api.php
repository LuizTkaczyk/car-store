<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::resource('vehicles', VehicleController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('information', InformationController::class);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [AuthController::class, 'refresh']);
    });

    Route::resource('home', HomeController::class);
    Route::get('filter-values', [HomeController::class, 'filterValues']);
    Route::get('filtered-vehicles', [HomeController::class, 'getFilteredVehicles']);
    Route::get('company-info', [HomeController::class, 'companyInfo']);
});
