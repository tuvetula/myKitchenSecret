<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('verify_email/{id}', [AuthController::class, 'verifyEmail'])->name('api.verifyEmail');
    Route::post('forget_password', [AuthController::class, 'forgetPassword'])->name('api.forgetPassword');
    Route::patch('reset_password/{id}',[AuthController::class, 'resetPassword'])->name('api.resetPassword');
    Route::post('login', [AuthController::class, 'login'])->name('api.login')->middleware('throttle:login');
    Route::post('refresh_token', [AuthController::class, 'refreshToken'])->name('api.refresh_token');

    Route::middleware('auth:api')->group(function () {
        Route::prefix('recipes')->group(function () {
            Route::middleware('admin')->group(function () {
                Route::get('/', [RecipeController::class, 'index']);
                Route::post('/', [RecipeController::class, 'store']);
                Route::get('/{id}', [RecipeController::class, 'show']);
                Route::put('/{id}', [RecipeController::class, 'update']);
                Route::delete('/{id}', [RecipeController::class, 'destroy']);
            });
        });
        Route::prefix('users')->group(function () {
            Route::middleware('admin')->group(function () {
                Route::get('/', [UserController::class, 'index']);
                Route::post('/', [UserController::class, 'store']);
            });
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    });
});
