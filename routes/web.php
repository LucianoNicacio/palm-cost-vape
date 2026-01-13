<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\AgeVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (No Age Verification)
// ==========================================

Route::get('/age-verification', [AgeVerificationController::class, 'show'])
    ->name('age.gate');

Route::post('/age-verification', [AgeVerificationController::class, 'verify'])
    ->name('age.verify');

// ==========================================
// AGE-VERIFIED ROUTES
// ==========================================

Route::middleware('verify.age')->group(function () {

    // Home Page
    Route::get('/', [HomeController::class, 'index'])
        ->name('home');

    // Product Catalog
    Route::get('/shop', [CatalogController::class, 'index'])
        ->name('shop');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::post('/cart/add/{product}', [CartController::class, 'add'])
        ->name('cart.add');

    Route::put('/cart/update/{product}', [CartController::class, 'update'])
        ->name('cart.update');

    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::delete('/cart/clear', [CartController::class, 'clear'])
        ->name('cart.clear');

    // Checkout Routes
    Route::get('/checkout', [ReservationController::class, 'checkout'])
        ->name('checkout');

    Route::post('/checkout', [ReservationController::class, 'store'])
        ->name('checkout.store');

    Route::get('/confirmation/{confirmationNumber}', [ReservationController::class, 'confirmation'])
        ->name('reserve.confirmation');
});

// ==========================================
// ADMIN ROUTES
// ==========================================

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Product Import
        Route::get('/products-import', [ProductImportController::class, 'showForm'])
            ->name('products.import.form');

        Route::post('/products-import', [ProductImportController::class, 'import'])
            ->name('products.import');

        Route::get('/products-template', [ProductImportController::class, 'downloadTemplate'])
            ->name('products.template');

        // Reservations
        Route::get('/reservations', [AdminReservationController::class, 'index'])
            ->name('reservations.index');

        Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])
            ->name('reservations.show');

        Route::put('/reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])
            ->name('reservations.status');

        // Products CRUD
        Route::get('/products', [ProductController::class, 'index'])
            ->name('products.index');

        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');

        Route::post('/products', [ProductController::class, 'store'])
            ->name('products.store');

        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('products.update');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

        Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
            ->name('products.stock');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show');

        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update');

        Route::get('/customers-export', [CustomerController::class, 'export'])
            ->name('customers.export');
    });

require __DIR__.'/settings.php';

require __DIR__.'/auth.php';
