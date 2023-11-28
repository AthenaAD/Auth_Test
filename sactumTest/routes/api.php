<?php

use App\Http\Controllers\BackendAdminController;
use App\Http\Controllers\UserController;
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

Route::group(['middleware'=> 'backend:backend', 'prefix' => 'backend'], function () {
    // 后台用户认证路由
    Route::post('/register', [BackendAdminController::class, 'register'])->withoutMiddleware('backend:backend');
    Route::post('/login', [BackendAdminController::class, 'login'])->withoutMiddleware('backend:backend');
    Route::post('/logout', [BackendAdminController::class, 'logout']);
    Route::get('/', [BackendAdminController::class, 'backendAdmin']);
});

Route::group(['middleware'=> 'user:user', 'prefix' => 'user'], function () {
    // 前台用户认证路由
    Route::post('/register', [UserController::class, 'register'])->withoutMiddleware('user:user');
    Route::post('/login', [UserController::class, 'login'])->withoutMiddleware('user:user');
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/', [UserController::class, 'user']);
});