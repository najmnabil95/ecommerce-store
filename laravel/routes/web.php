<?php

use App\Http\Controllers\AdminViewController;
use App\Http\Controllers\WebStoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Storefront Multi-page dynamic routes
Route::get('/', [WebStoreController::class, 'home'])->name('store.home');
Route::get('/products', [WebStoreController::class, 'products'])->name('store.products');
Route::get('/products/{id}', [WebStoreController::class, 'showProduct'])->name('store.product.show');
Route::get('/category/{slug}', [WebStoreController::class, 'category'])->name('store.category');
Route::get('/compare', [WebStoreController::class, 'compare'])->name('store.compare');
Route::get('/cart', [WebStoreController::class, 'cart'])->name('store.cart');
Route::get('/checkout', [WebStoreController::class, 'checkout'])->name('store.checkout');
Route::get('/track', [WebStoreController::class, 'track'])->name('store.track');

// Admin Multi-page dynamic routes
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminViewController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/products', [AdminViewController::class, 'products'])->name('admin.products');
    Route::get('/orders', [AdminViewController::class, 'orders'])->name('admin.orders');
    Route::get('/reviews', [AdminViewController::class, 'reviews'])->name('admin.reviews');
    Route::get('/audit', [AdminViewController::class, 'audit'])->name('admin.audit');
    Route::get('/database', [AdminViewController::class, 'database'])->name('admin.database');
});
