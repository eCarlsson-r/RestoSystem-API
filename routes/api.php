<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\BuffetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\IngredientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('tables')->group(function () {
    Route::get('/', [TableController::class, 'index']);
    Route::get('/floor', [TableController::class, 'floorIndex']);
    Route::post('/', [TableController::class, 'store']);
    Route::post('/use', [TableController::class, 'useTable']);
    Route::post('/release', [TableController::class, 'releaseTable']);
    Route::post('/shift', [TableController::class, 'shiftTable']);
    Route::post('/merge', [TableController::class, 'mergeTable']);
    Route::delete('/', [TableController::class, 'destroy']);
});

Route::prefix('branches')->group(function () {
    Route::get('/', [BranchController::class, 'index']);
    Route::post('/', [BranchController::class, 'store']);
    Route::delete('/{id}', [BranchController::class, 'destroy']);
});

Route::prefix('rooms')->group(function () {
    Route::get('/', [RoomController::class, 'index']);
    Route::post('/', [RoomController::class, 'store']);
    Route::delete('/{id}', [RoomController::class, 'destroy']);
});

Route::prefix('sales')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::get('/{id}', [SalesController::class, 'show']);
    Route::post('/', [SalesController::class, 'store']);
    Route::post('/checkout', [SalesController::class, 'checkout']);
    Route::post('/split', [SalesController::class, 'splitSales']);
    Route::post('/merge', [SalesController::class, 'mergeSales']);
    Route::get('/orders/{salesId}', [SalesController::class, 'getActiveCaptainOrder']);
});

Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::delete('/{id}', [EmployeeController::class, 'destroy']);
});

Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index']);
    Route::post('/', [SupplierController::class, 'store']);
    Route::delete('/{id}', [SupplierController::class, 'destroy']);
});

Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::delete('/{id}', [CustomerController::class, 'destroy']);
});

Route::prefix('purchasing')->group(function () {
    Route::post('/order', [PurchasingController::class, 'storeOrder']);
    Route::post('/receive', [PurchasingController::class, 'receiveOrder']);
});

Route::prefix('vouchers')->group(function () {
    Route::get('/', [VoucherController::class, 'index']);
    Route::post('/check', [VoucherController::class, 'check']);
    Route::post('/', [VoucherController::class, 'store']);
});

Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index']);
    Route::post('/', [PackageController::class, 'store']);
    Route::delete('/{code}', [PackageController::class, 'destroy']);
});

Route::prefix('utilities')->group(function () {
    Route::get('/cities', [UtilityController::class, 'getCities']);
    Route::get('/states', [UtilityController::class, 'getStates']);
});

Route::prefix('buffet')->group(function () {
    Route::get('/packages', [BuffetController::class, 'index']);
    Route::post('/package', [BuffetController::class, 'storePackage']);
    Route::post('/order', [BuffetController::class, 'storeOrder']);
});

Route::prefix('notifications')->group(function () {
    Route::post('/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('/unsubscribe', [NotificationController::class, 'unsubscribe']);
});

Route::prefix('files')->group(function () {
    Route::get('/{fileid}', [FileController::class, 'show']);
    Route::post('/upload', [FileController::class, 'store']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::get('/{id}/recipe', [ProductController::class, 'getRecipe']);
});

Route::prefix('prepare')->group(function () {
    Route::get('/', [PrepareController::class, 'index']);
    Route::get('/{id}', [PrepareController::class, 'show']);
    Route::post('/', [PrepareController::class, 'store']);
    Route::delete('/{id}', [PrepareController::class, 'destroy']);
    Route::get('/{id}/recipe', [PrepareController::class, 'getRecipe']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

Route::prefix('stock')->group(function () {
    Route::get('/', [StockController::class, 'index']);
    Route::post('/', [StockController::class, 'store']);
    Route::post('/move', [StockController::class, 'move']);
    Route::post('/receive/{id}', [StockController::class, 'receive']);
});

Route::prefix('ingredients')->group(function () {
    Route::get('/', [IngredientController::class, 'index']);
    Route::post('/', [IngredientController::class, 'store']);
    Route::delete('/{code}', [IngredientController::class, 'destroy']);
});

Route::prefix('kitchen')->group(function () {
    Route::get('/tickets', [KitchenController::class, 'getTickets']);
});

Route::get('/dashboard', [DashboardController::class, 'index']);