<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->prefix('auth')->group(function () {
    Route::resource('vehicle', VehicleController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('brand', BrandController::class);
    Route::resource('information', InformationController::class);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::post('login', [AuthController::class, 'login']);
Route::resource('home', HomeController::class);

Route::get('year-and-price', [HomeController::class, 'yearAndPrice']);
Route::get('filtered-vehicles', [HomeController::class, 'getFilteredVehicles']);
Route::get('brand-home', [HomeController::class, 'getBrands']);
Route::get('category-home', [HomeController::class, 'getCategories']);
