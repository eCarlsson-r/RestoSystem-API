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
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\BuffetController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PrepareController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::prefix('tables')->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::post('/', [TableController::class, 'store']);
        Route::post('/use', [TableController::class, 'useTable']);
        Route::post('/release', [TableController::class, 'releaseTable']);
        Route::post('/shift', [TableController::class, 'shiftTable']);
        Route::post('/merge', [TableController::class, 'mergeTable']);
        Route::post('/split', [TableController::class, 'splitTable']);
        Route::delete('/', [TableController::class, 'destroy']);
    });

    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::post('/', [BranchController::class, 'store']);
        Route::delete('/{id}', [BranchController::class, 'destroy']);
        Route::delete('/image/{id}', [FileController::class, 'destroy']);
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
        Route::post('/move', [SalesController::class, 'moveTable']);
        Route::get('/orders/{salesId}', [SalesController::class, 'getActiveCaptainOrder']);
        Route::get('/get-table-sale', [SalesController::class, 'getTableSale']);
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
        Route::get('/orders', [PurchasingController::class, 'purchases']);
        Route::get('/order/{id}', [PurchasingController::class, 'purchase']);
        Route::post('/orders', [PurchasingController::class, 'storeOrder']);
        Route::post('/receive', [PurchasingController::class, 'receiveOrder']);
        Route::get('/returns', [PurchasingController::class, 'returns']);
        Route::post('/returns', [PurchasingController::class, 'storeReturn']);
        Route::get('/returns/{id}', [PurchasingController::class, 'return']);
    });

    Route::prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::get('/{id}', [VoucherController::class, 'show']);
        Route::post('/', [VoucherController::class, 'store']);
    });

    Route::prefix('packages')->group(function () {
        Route::get('/', [PackageController::class, 'index']);
        Route::post('/', [PackageController::class, 'store']);
        Route::delete('/{code}', [PackageController::class, 'destroy']);
        Route::delete('/image/{id}', [FileController::class, 'destroy']);
    });

    Route::prefix('buffet')->group(function () {
        Route::get('/', [BuffetController::class, 'index']);
        Route::post('/', [BuffetController::class, 'store']);
        Route::post('/{id}/sync-items', [BuffetController::class, 'syncItems']);
        Route::delete('/{id}', [BuffetController::class, 'destroy']);
        Route::delete('/image/{id}', [FileController::class, 'destroy']);
    });

    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::post('/', [ReservationController::class, 'store']);
        Route::post('/{id}/check-in', [ReservationController::class, 'checkIn']);
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
        Route::post('/{id}/soldout', [ProductController::class, 'toggleSoldOut']);
        Route::delete('/image/{id}', [FileController::class, 'destroy']);
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
        Route::delete('/image/{id}', [FileController::class, 'destroy']);
    });

    Route::prefix('stock')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::post('/', [StockController::class, 'store']);
        Route::get('/transfers', [StockController::class, 'transfers']);
        Route::post('/transfers', [StockController::class, 'move']);
        Route::post('/receive/{id}', [StockController::class, 'receive']);
        Route::get('/card', [StockController::class, 'getStockCard']);
        Route::get('/requests', [StockController::class, 'kitchenRequest']);
        Route::post('/requests', [StockController::class, 'storeKitchenRequest']);
        Route::post('/requests/approve', [StockController::class, 'approveRequest']);
        Route::post('/requests/reject', [StockController::class, 'rejectRequest']);
        Route::get('/mutation', [StockController::class, 'getStockMutation']);
    });

    Route::prefix('ingredients')->group(function () {
        Route::get('/', [IngredientController::class, 'index']);
        Route::post('/', [IngredientController::class, 'store']);
        Route::delete('/{code}', [IngredientController::class, 'destroy']);
    });

    Route::prefix('utilities')->group(function () {
        Route::get('/', [UtilityController::class, 'index']);
        Route::post('/', [UtilityController::class, 'store']);
        Route::delete('/{code}', [UtilityController::class, 'destroy']);
        Route::get('/cities', [UtilityController::class, 'getCities']);
        Route::get('/states', [UtilityController::class, 'getStates']);
    });

    Route::prefix('kitchen')->group(function () {
        Route::get('/tickets', [KitchenController::class, 'getTickets']);
        Route::post('/prepare', [KitchenController::class, 'prepare']);
        Route::post('/order/{id}/status', [KitchenController::class, 'updateStatus']);
        Route::get('/movement', [KitchenController::class, 'approvedRequests']);
    });

    Route::prefix('report')->group(function () {
        Route::get('/sales', [SalesController::class, 'getSalesReport']);
        Route::get('/salesman', [SalesController::class, 'getEmployeeSalesReport']);
        Route::get('/cancel', [SalesController::class, 'getCancellationReport']);
        Route::get('/invoice', [SalesController::class, 'getInvoiceReport']);
        Route::get('/purchase', [PurchasingController::class, 'reportPurchase']);
        Route::get('/supplier', [PurchasingController::class, 'reportSupplierPurchase']);
    });
});