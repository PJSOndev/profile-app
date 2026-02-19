<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchManagementController;
use App\Http\Controllers\CategoryManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryManagementController;
use App\Http\Controllers\ProductManagementController;
use App\Http\Controllers\ProductLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ShiftManagementController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::get('/login.html', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile.html', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('owner.readonly')->name('profile.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:super admin,owner,admin')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/index.html', [DashboardController::class, 'index'])->name('index');
        Route::view('/404.html', 'pages.404')->name('error.404');
        Route::view('/blank.html', 'pages.blank')->name('blank');
        Route::view('/buttons.html', 'pages.buttons')->name('buttons');
        Route::view('/cards.html', 'pages.cards')->name('cards');
        Route::view('/charts.html', 'pages.charts')->name('charts');
        Route::view('/forgot-password.html', 'pages.forgot-password')->name('forgot-password');
        Route::view('/register.html', 'pages.register')->name('register');
        Route::view('/utilities-animation.html', 'pages.utilities-animation')->name('utilities-animation');
        Route::view('/utilities-border.html', 'pages.utilities-border')->name('utilities-border');
        Route::view('/utilities-color.html', 'pages.utilities-color')->name('utilities-color');
        Route::view('/utilities-other.html', 'pages.utilities-other')->name('utilities-other');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/tables.html', [UserManagementController::class, 'index'])->name('tables');
        Route::get('/users/branches', [BranchManagementController::class, 'index'])->name('users.branches.index');

        Route::get('/products', [ProductManagementController::class, 'index'])->name('products.index');
        Route::get('/products/categories', [CategoryManagementController::class, 'index'])->name('products.categories.index');
        Route::get('/products/logs', [ProductLogController::class, 'index'])->name('products.logs.index');

        Route::get('/sales-report', [SalesReportController::class, 'index'])->name('sales.report');
        Route::get('/sales-report/by-user', [SalesReportController::class, 'salesByUser'])->name('sales.report.by-user');
        Route::get('/sales-report/by-shift', [SalesReportController::class, 'salesByShift'])->name('sales.report.by-shift');

        Route::get('/inventory', [InventoryManagementController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/logs', [InventoryManagementController::class, 'logs'])->name('inventory.logs');
    });

    Route::middleware('role:super admin,admin')->group(function (): void {
        Route::get('/users/shifts', [ShiftManagementController::class, 'index'])->name('users.shifts.index');
        Route::post('/users/shifts', [ShiftManagementController::class, 'store'])->name('users.shifts.store');
        Route::put('/users/shifts/{shift}', [ShiftManagementController::class, 'update'])->name('users.shifts.update');
        Route::delete('/users/shifts/{shift}', [ShiftManagementController::class, 'destroy'])->name('users.shifts.destroy');
    });

    Route::middleware(['role:super admin,owner,admin', 'owner.readonly'])->group(function (): void {
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::post('/users/approve-pending', [UserManagementController::class, 'approveAllPending'])->name('users.approve-all');
        Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::post('/users/branches', [BranchManagementController::class, 'store'])->name('users.branches.store');
        Route::put('/users/branches/{branch}', [BranchManagementController::class, 'update'])->name('users.branches.update');
        Route::delete('/users/branches/{branch}', [BranchManagementController::class, 'destroy'])->name('users.branches.destroy');

        Route::post('/products', [ProductManagementController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductManagementController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductManagementController::class, 'destroy'])->name('products.destroy');

        Route::post('/products/categories', [CategoryManagementController::class, 'store'])->name('products.categories.store');
        Route::put('/products/categories/{category}', [CategoryManagementController::class, 'update'])->name('products.categories.update');
        Route::delete('/products/categories/{category}', [CategoryManagementController::class, 'destroy'])->name('products.categories.destroy');

        Route::post('/inventory/in', [InventoryManagementController::class, 'storeIn'])->name('inventory.store-in');
    });
});
