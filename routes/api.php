<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('admin-login', 'adminLogin');
    Route::post('delete-otp/{token}', 'deleteOneTimePassword');
    Route::post('refresh-token', 'refreshToken');
});

Route::middleware(['auth:api'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('me', 'me');
        Route::post('logout', 'logout');
    });
    Route::prefix('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['show']);
        Route::post('categories/toggle-status/{id}', [CategoryController::class, 'toggleStatus']);

        Route::apiResource('sub-categories', SubCategoryController::class)->except(['show']);
        Route::post('sub-categories/toggle-status/{id}', [SubCategoryController::class, 'toggleStatus']);
    });
});
