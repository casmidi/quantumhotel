<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockPackageController;

/*
|--------------------------------------------------------------------------
| DEBUG
|--------------------------------------------------------------------------
*/
Route::get('/test-db', function () {
    return DB::select("SELECT TOP 5 * FROM KELAS");
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'loginForm']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if (!session('user')) return redirect('/');
    return app(DashboardController::class)->index();
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - KELAS
|--------------------------------------------------------------------------
*/
Route::get('/kelas', function () {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->index(request());
});

Route::post('/kelas', function () {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->store(request());
});

Route::post('/kelas/{kode}/update', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->update(request(), $kode);
});

Route::get('/kelas/{kode}/delete', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->destroy($kode);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - ROOM
|--------------------------------------------------------------------------
*/
Route::get('/room', function () {
    if (!session('user')) return redirect('/');
    return app(RoomController::class)->index();
});

Route::post('/room', function () {
    if (!session('user')) return redirect('/');
    return app(RoomController::class)->store(request());
});

Route::post('/room/{kode}/update', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(RoomController::class)->update(request(), $kode);
});

Route::get('/room/{kode}/delete', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(RoomController::class)->destroy($kode);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - STOCK PACKAGE
|--------------------------------------------------------------------------
*/
Route::get('/stock-package', function () {
    if (!session('user')) return redirect('/');
    return app(StockPackageController::class)->index();
});

Route::post('/stock-package', function () {
    if (!session('user')) return redirect('/');
    return app(StockPackageController::class)->store(request());
});

Route::post('/stock-package/process', function () {
    if (!session('user')) return redirect('/');
    return app(StockPackageController::class)->process(request());
});

Route::post('/stock-package/{kode}/update', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(StockPackageController::class)->update(request(), $kode);
});

Route::get('/stock-package/{kode}/delete', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(StockPackageController::class)->destroy($kode);
});


/*
|--------------------------------------------------------------------------
| TRANSACTION
|--------------------------------------------------------------------------
*/
Route::get('/checkin', function () {
    if (!session('user')) return redirect('/');
    return "Check-In Page";
});

Route::get('/checkout', function () {
    if (!session('user')) return redirect('/');
    return "Check-Out Page";
});


/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/
Route::get('/guest-in-house', function () {
    if (!session('user')) return redirect('/');
    return "Guest In House Report";
});

Route::get('/expected-departure', function () {
    if (!session('user')) return redirect('/');
    return "Expected Departure Report";
});


/*
|--------------------------------------------------------------------------
| TOOLS
|--------------------------------------------------------------------------
*/
Route::get('/user', function () {
    if (!session('user')) return redirect('/');
    return "User Management Page";
});

Route::get('/change-password', function () {
    if (!session('user')) return redirect('/');
    return "Change Password Page";
});

