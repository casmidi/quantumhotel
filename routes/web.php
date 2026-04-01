<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;

/*
|--------------------------------------------------------------------------
| LOGIN
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
| MASTER KELAS
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

Route::get('/kelas/{kode}/edit', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->edit($kode);
});

Route::get('/kelas/{kode}/delete', function ($kode) {
    if (!session('user')) return redirect('/');
    return app(KelasController::class)->destroy($kode);
});