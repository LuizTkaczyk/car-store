<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['cors'])->group(function () {
    Route::resource('home', HomeController::class);
    Route::resource('vehicle', VehicleController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('brand', BrandController::class);
    Route::resource('information', InformationController::class);

    Route::get('year-and-price', [HomeController::class, 'yearAndPrice']);
    Route::get('change-brand', [HomeController::class, 'changeBrand']);
    Route::get('change-category', [HomeController::class, 'changeCategory']);
    Route::get('change-year', [HomeController::class, 'changeYear']);
    Route::get('change-price', [HomeController::class, 'changePrice']);
});
