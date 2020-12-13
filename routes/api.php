<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Http\Request;
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

Route::prefix('v1')->group(function(){
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:api')->group(function(){
        Route::get('/recipes',[RecipeController::class,'index'])->middleware('admin');
        Route::post('/recipes', [RecipeController::class,'store']);
        Route::put('/recipes/{id}',[RecipeController::class,'update']);
        Route::delete('recipes/{id}', [RecipeController::class,'destroy']);
        Route::get('logout',[AuthController::class, 'logout'])->name('logout');
    });
});
