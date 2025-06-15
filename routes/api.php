<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\MidtransWebhookController;

Route::prefix('admin')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAdmin']);
});
Route::prefix('penjual')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPenjual']);
});
Route::prefix('pembeli')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPembeli']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send']);
    Route::post('/verify', [OtpController::class, 'verify']);
});

Route::middleware('auth:admin,penjual,pembeli')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/property-types', [PropertyController::class, 'getPropertyTypes']);
    Route::get('/lokasi/properties', [PropertyController::class, 'filterByLocation']);

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

    Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle']);
});
