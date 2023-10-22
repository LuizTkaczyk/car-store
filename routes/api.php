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
});