<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PropertyController;

// ===========================
// ✅ Register per Role
// ===========================
Route::prefix('admin')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAdmin']);
});
Route::prefix('penjual')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPenjual']);
});
Route::prefix('pembeli')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPembeli']);
});

// ===========================
// ✅ Universal Login
// ===========================
Route::post('/login', [AuthController::class, 'login']);

// ===========================
// ✅ OTP Routes
// ===========================
Route::prefix('otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send']);
    Route::post('/verify', [OtpController::class, 'verify']);
});

// ===========================
// ✅ Authenticated User Routes
// ===========================
Route::middleware('auth:admin,penjual,pembeli')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// ===========================
// ✅ Property Routes
// ===========================
Route::middleware('auth:admin,penjual,pembeli')->group(function () {
    // Public list for all authenticated roles
    Route::get('/property-types', [PropertyController::class, 'getPropertyTypes']);

    // Admin property management
    Route::prefix('admin/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/property/{property_id}', [PropertyController::class, 'show']);
        Route::patch('/property/{property_id}', [PropertyController::class, 'update']);
        Route::delete('/property/{property_id}', [PropertyController::class, 'destroy']);
        Route::get('/type/{tipe}', [PropertyController::class, 'filterByType']);
    });

    // Penjual property management
    Route::prefix('penjual/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/property/{property_id}', [PropertyController::class, 'show']);
        Route::patch('/property/{property_id}', [PropertyController::class, 'update']);
        Route::delete('/property/{property_id}', [PropertyController::class, 'destroy']);
        Route::get('/type/{tipe}', [PropertyController::class, 'filterByType']);
    });

    // Pembeli view-only access
    Route::prefix('pembeli/properties')->group(function () {
        Route::get('/', [PropertyController::class, 'indexForPembeli']);
        Route::get('/property/{property_id}', [PropertyController::class, 'showForPembeli']);
    });
});
