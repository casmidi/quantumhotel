<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockPackageController;
use App\Http\Controllers\PackageTransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'apiLogin']);

    Route::middleware('api.token')->group(function () {
        Route::get('/me', [AuthController::class, 'apiMe']);
        Route::post('/logout', [AuthController::class, 'apiLogout']);

        Route::get('/kelas', [KelasController::class, 'index']);
        Route::post('/kelas', [KelasController::class, 'store']);
        Route::match(['put', 'patch'], '/kelas/{kode}', [KelasController::class, 'update']);
        Route::delete('/kelas/{kode}', [KelasController::class, 'destroy']);

        Route::get('/room', [RoomController::class, 'index']);
        Route::post('/room', [RoomController::class, 'store']);
        Route::match(['put', 'patch'], '/room/{kode}', [RoomController::class, 'update']);
        Route::delete('/room/{kode}', [RoomController::class, 'destroy']);

        Route::get('/item-package-global', [StockPackageController::class, 'index']);
        Route::post('/item-package-global', [StockPackageController::class, 'store']);
        Route::match(['put', 'patch'], '/item-package-global/{kode}', [StockPackageController::class, 'update']);
        Route::delete('/item-package-global/{kode}', [StockPackageController::class, 'destroy']);

        Route::get('/menu-package-transaction', [PackageTransactionController::class, 'index']);
        Route::post('/menu-package-transaction', [PackageTransactionController::class, 'store']);
        Route::match(['put', 'patch'], '/menu-package-transaction/{nofak}', [PackageTransactionController::class, 'update']);
        Route::delete('/menu-package-transaction/{nofak}', [PackageTransactionController::class, 'destroy']);

        Route::get('/checkin', [CheckinController::class, 'index']);
        Route::post('/checkin', [CheckinController::class, 'store']);
        Route::match(['put', 'patch'], '/checkin/{regNo2}', [CheckinController::class, 'update']);
        Route::delete('/checkin/{regNo2}', [CheckinController::class, 'destroy']);

        Route::get('/checkout', fn () => response()->json([
            'success' => true,
            'data' => ['message' => 'Checkout API placeholder'],
        ]));

        Route::get('/guest-in-house', fn () => response()->json([
            'success' => true,
            'data' => ['message' => 'Guest In House API placeholder'],
        ]));

        Route::get('/expected-departure', fn () => response()->json([
            'success' => true,
            'data' => ['message' => 'Expected Departure API placeholder'],
        ]));

        Route::get('/user', fn () => response()->json([
            'success' => true,
            'data' => ['message' => 'User Management API placeholder'],
        ]));

        Route::get('/change-password', fn () => response()->json([
            'success' => true,
            'data' => ['message' => 'Change Password API placeholder'],
        ]));
    });
});
