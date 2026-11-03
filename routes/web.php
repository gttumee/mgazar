<?php

use App\Http\Controllers\LandController;
use App\Http\Controllers\LandOfferController;
use Illuminate\Support\Facades\Route;

Route::post('/check-parcel', [LandController::class, 'checkParcel'])
    ->middleware('throttle:20,1')
    ->name('lands.check-parcel');
Route::get('/', [LandController::class, 'index'])->name('home');
Route::post('/lands', [LandController::class, 'store'])->name('lands.store');
Route::post('/lands/{land}/view', [LandController::class, 'view'])->name('lands.view');
Route::post('/lands/{land}/offers', [LandOfferController::class, 'store'])->name('lands.offers.store');
