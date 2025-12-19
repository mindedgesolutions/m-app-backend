<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
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

    // Admin auth routes start -------------------------
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::controller(CategoryController::class)->prefix('categories')->group(function () {
            Route::post('toggle-status/{id}', 'toggleStatus');
            Route::get('all', 'all');
        });

        Route::apiResource('sub-categories', SubCategoryController::class)->except(['show']);

        Route::controller(SubCategoryController::class)->prefix('sub-categories')->group(function () {
            Route::post('toggle-status/{id}', 'toggleStatus');
            Route::get('all', 'all');
        });
    });
    // Admin auth routes end -------------------------

    // Common auth routes for all roles start -------------------------
    Route::apiResource('products', ProductController::class)->except(['show']);
    Route::post('products/toggle-status/{id}', [ProductController::class, 'toggleStatus']);
    // Common auth routes for all roles end -------------------------

    // User routes start -------------------------
    Route::middleware(['role:user'])->group(function () {});
    // User routes end -------------------------
});
