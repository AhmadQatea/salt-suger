<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\RestaurantSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public digital menu
|--------------------------------------------------------------------------
*/
Route::get('/', [MenuController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{category:slug}', [MenuController::class, 'index'])->name('menu.category');

/*
|--------------------------------------------------------------------------
| Shopping cart
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{key}', [CartController::class, 'update'])
    ->where('key', '[0-9]+:[a-f0-9]+')
    ->name('cart.items.update');
Route::delete('/cart/items/{key}', [CartController::class, 'destroy'])
    ->where('key', '[0-9]+:[a-f0-9]+')
    ->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

/*
|--------------------------------------------------------------------------
| Checkout & orders
|--------------------------------------------------------------------------
*/
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
Route::get('/order/success/{order}', [OrderController::class, 'success'])
    ->middleware('signed')
    ->name('orders.success');

/*
|--------------------------------------------------------------------------
| Admin authentication (guest)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Protected admin area
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'auth.session', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/settings', [RestaurantSettingController::class, 'edit'])->name('settings.edit');
        Route::match(['put', 'patch'], '/settings', [RestaurantSettingController::class, 'update'])
            ->name('settings.update');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])
            ->name('categories.toggle');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])
            ->name('products.toggle');

        Route::get('/qr-code', [QrCodeController::class, 'index'])->name('qr-code.index');
        Route::get('/qr-code/download/{format}', [QrCodeController::class, 'download'])
            ->whereIn('format', ['svg', 'png'])
            ->name('qr-code.download');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.status.update');
    });
});
