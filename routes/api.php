<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserQuizProgressController;
use App\Http\Controllers\AdminController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/progress', [UserQuizProgressController::class, 'store']);
    Route::get('/progress/{section_id}', [UserQuizProgressController::class, 'show']);
    Route::get('/progress', [UserQuizProgressController::class, 'index']);

    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users']);
    });
});