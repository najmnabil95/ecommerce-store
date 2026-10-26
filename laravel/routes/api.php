<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Categories ---
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// --- Products ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::patch('/products/{id}/quick-update', [ProductController::class, 'quickUpdate']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::post('/products/{id}/reviews', [ProductController::class, 'addReview']);

// --- Coupons ---
Route::get('/coupons', [CouponController::class, 'index']);
Route::post('/coupons', [CouponController::class, 'store']);
Route::patch('/coupons/{id}/toggle', [CouponController::class, 'toggle']);
Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

// --- Orders ---
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/track', [OrderController::class, 'track']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
Route::get('/customer/orders', [OrderController::class, 'customerOrders']);

// --- Auth ---
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/auth/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Settings ---
Route::get('/settings', [SettingController::class, 'index']);
Route::post('/admin/settings', [SettingController::class, 'store']);

// --- Admin Bulk Operations & Reports ---
Route::post('/admin/products/bulk', [ProductController::class, 'bulkAction']);
Route::post('/admin/orders/bulk-status', [OrderController::class, 'bulkStatus']);
Route::get('/stats', [OrderController::class, 'stats']);
Route::get('/admin/reports/export', [OrderController::class, 'exportCsv']);

// --- Admin Users Management ---
Route::get('/admin/users', [UserController::class, 'index']);
Route::post('/admin/users', [UserController::class, 'store']);
Route::get('/admin/users/{id}/orders', [UserController::class, 'userOrders']);
Route::patch('/admin/users/{id}/role', [UserController::class, 'updateRole']);
Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);

// --- Reviews Management & Helpful Votes ---
Route::post('/reviews/{id}/helpful', [ReviewController::class, 'helpfulVote']);
Route::get('/admin/reviews', [ReviewController::class, 'index']);
Route::delete('/admin/reviews/{id}', [ReviewController::class, 'destroy']);

// --- System & Database Tools ---
Route::get('/admin/system/stats', [SystemController::class, 'stats']);
Route::post('/admin/system/reset-mock', [SystemController::class, 'resetMock']);
Route::post('/admin/system/init-clean', [SystemController::class, 'initClean']);
Route::get('/admin/system/backup', [SystemController::class, 'backup']);
Route::post('/admin/system/optimize', [SystemController::class, 'optimize']);

