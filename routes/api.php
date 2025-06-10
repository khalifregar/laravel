<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PropertyController;


Route::prefix('admin')->middleware('auth:api')->group(function () {
    Route::post('/register', [AuthController::class, 'registerAdmin'])->withoutMiddleware('auth:api');
    Route::post('/login', [AuthController::class, 'loginAdmin'])->withoutMiddleware('auth:api');
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
});
Route::prefix('penjual')->middleware('auth:api')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPenjual'])->withoutMiddleware('auth:api');
    Route::post('/login', [AuthController::class, 'loginPenjual'])->withoutMiddleware('auth:api');
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
});
Route::prefix('pembeli')->middleware('auth:api')->group(function () {
    Route::post('/register', [AuthController::class, 'registerPembeli'])->withoutMiddleware('auth:api');
    Route::post('/login', [AuthController::class, 'loginPembeli'])->withoutMiddleware('auth:api');
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile/{id}', [AuthController::class, 'updateProfile']);
    Route::post('/logout/{id}', [AuthController::class, 'logout']);
});
Route::prefix('otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send']);
    Route::post('/verify', [OtpController::class, 'verify']);
});
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

