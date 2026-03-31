<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;

/*
|--------------------------------------------------------------------------
| DEBUG (sementara, untuk test DB)
|--------------------------------------------------------------------------
*/

Route::get('/test-db', function () {
    return DB::select("SELECT TOP 5 * FROM Kelas");
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
    return view('dashboard');
});

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
Route::get('/room-class',[KelasController::class,'index']);


// Route::get('/room-class', function () {
//     if (!session('user')) return redirect('/');
//     return "Room Classes Page";
// });

Route::get('/room', function () {
    if (!session('user')) return redirect('/');
    return "Rooms Page";
});

/*
|--------------------------------------------------------------------------
| MODULE KELAS (INI YANG KITA BUAT)
|--------------------------------------------------------------------------
*/

Route::get('/kelas', [KelasController::class, 'index']);
Route::post('/kelas/save', [KelasController::class, 'save']);
Route::get('/kelas/edit/{kode}', [KelasController::class, 'edit']);
Route::get('/kelas/delete/{kode}', [KelasController::class, 'delete']);

/*
|--------------------------------------------------------------------------
| TRANSACTIONS
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
| REPORTS
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