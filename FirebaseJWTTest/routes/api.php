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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'backend'], function () {
    // 后台用户认证路由
    Route::post('/register', [BackendAdminController::class, 'register']);
    Route::post('/login', [BackendAdminController::class, 'login']);
    Route::post('/logout', [BackendAdminController::class, 'logout'])->middleware('admin.access.token');
    Route::post('/refresh', [BackendAdminController::class, 'refresh'])->middleware('admin.refresh.token');
    Route::get('/', [BackendAdminController::class, 'backendAdmin'])->middleware('admin.access.token');
});

Route::group(['prefix' => 'user'], function () {
    // 前台用户认证路由
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('user.access.token');
    Route::post('/refresh', [UserController::class, 'refresh'])->middleware('user.refresh.token');
    Route::get('/', [UserController::class, 'user'])->middleware('user.access.token');
});