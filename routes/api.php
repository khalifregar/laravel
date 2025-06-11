<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PropertyController;

// Register tetap role-based (admin, penjual, pembeli)
Route::prefix('admin')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAdmin']);
});
Route::prefix('penjual')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPenjual']);
});
Route::prefix('pembeli')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPembeli']);
});

// Login universal (tanpa prefix, global endpoint)
Route::post('/login', [AuthController::class, 'login']);

// Authenticated endpoints (me, update profile, logout)
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// OTP Routes
Route::prefix('otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send']);
    Route::post('/verify', [OtpController::class, 'verify']);
});

// Property APIs (role segmented)
Route::middleware('auth:api')->group(function () {
    Route::get('/property-types', [PropertyController::class, 'getPropertyTypes']);

    Route::prefix('admin/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/property/{property_id}', [PropertyController::class, 'show']);
        Route::patch('/property/{property_id}', [PropertyController::class, 'update']);
        Route::delete('/property/{property_id}', [PropertyController::class, 'destroy']);
        Route::get('/type/{tipe}', [PropertyController::class, 'filterByType']);
    });

    Route::prefix('penjual/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/property/{property_id}', [PropertyController::class, 'show']);
        Route::patch('/property/{property_id}', [PropertyController::class, 'update']);
        Route::delete('/property/{property_id}', [PropertyController::class, 'destroy']);
        Route::get('/type/{tipe}', [PropertyController::class, 'filterByType']);
    });

    Route::prefix('pembeli/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'indexForPembeli']);
        Route::get('/property/{property_id}', [PropertyController::class, 'showForPembeli']);
    });
});
