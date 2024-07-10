<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('user')->group(function () {
    Route::match(['POST'], '/login', [UserAuthController::class, 'login']);
    Route::match(['POST'], '/register', [UserAuthController::class, 'register']);
    Route::match(['GET', 'POST'], '/active', [UserAuthController::class, 'active'])->middleware('auth:sanctum');
    Route::match(['GET', 'POST'], '/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('task')->group(function () {
    Route::match(['GET'], '/', [TaskController::class, 'list'])->middleware('auth:sanctum');
    Route::match(['POST'], '/create', [TaskController::class, 'create'])->middleware('auth:sanctum');
    Route::match(['POST'], '/update/{task}', [TaskController::class, 'update'])->middleware('auth:sanctum');
    Route::match(['POST'], '/delete/{task}', [TaskController::class, 'delete'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
