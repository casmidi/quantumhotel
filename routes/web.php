<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HotelSettingsController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockPackageController;
use App\Http\Controllers\PackageTransactionController;

if (!function_exists('ensureSessionAccess')) {
    function ensureSessionAccess()
    {
        if (session('user')) {
            return null;
        }

        $request = request();
        $accept = strtolower((string) $request->header('Accept', ''));
        $wantsJson = $request->expectsJson()
            || $request->wantsJson()
            || $request->is('api/*')
            || $request->ajax()
            || str_contains($accept, '/json');

        if ($wantsJson) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect('/');
    }
}

if (!function_exists('respondPlaceholder')) {
    function respondPlaceholder(string $message)
    {
        $request = request();
        $accept = strtolower((string) $request->header('Accept', ''));
        $wantsJson = $request->expectsJson()
            || $request->wantsJson()
            || $request->is('api/*')
            || $request->ajax()
            || str_contains($accept, '/json');

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $message,
                ],
            ]);
        }

        return $message;
    }
}

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
    if ($response = ensureSessionAccess()) return $response;
    return app(DashboardController::class)->index();
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - KELAS
|--------------------------------------------------------------------------
*/
Route::get('/kelas', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(KelasController::class)->index(request());
});

Route::post('/kelas', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(KelasController::class)->store(request());
});

Route::post('/kelas/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(KelasController::class)->update(request(), $kode);
});

Route::get('/kelas/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(KelasController::class)->destroy(request(), $kode);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - ROOM
|--------------------------------------------------------------------------
*/
Route::get('/room', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(RoomController::class)->index(request());
});

Route::post('/room', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(RoomController::class)->store(request());
});

Route::post('/room/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(RoomController::class)->update(request(), $kode);
});

Route::get('/room/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(RoomController::class)->destroy(request(), $kode);
});


/*
|--------------------------------------------------------------------------
| PACKAGE
|--------------------------------------------------------------------------
*/
Route::get('/stock-package', function () {
    if ($response = ensureSessionAccess()) return $response;
    return redirect('/item-package-global');
});

Route::get('/item-package-global', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(StockPackageController::class)->index(request());
});

Route::post('/item-package-global', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(StockPackageController::class)->store(request());
});

Route::post('/item-package-global/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(StockPackageController::class)->update(request(), $kode);
});

Route::get('/item-package-global/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    return app(StockPackageController::class)->destroy(request(), $kode);
});

Route::get('/menu-package-transaction', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(PackageTransactionController::class)->index(request());
});

Route::post('/menu-package-transaction', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(PackageTransactionController::class)->store(request());
});

Route::post('/menu-package-transaction/{nofak}/update', function ($nofak) {
    if ($response = ensureSessionAccess()) return $response;
    return app(PackageTransactionController::class)->update(request(), $nofak);
});

Route::get('/menu-package-transaction/{nofak}/delete', function ($nofak) {
    if ($response = ensureSessionAccess()) return $response;
    return app(PackageTransactionController::class)->destroy(request(), $nofak);
});


/*
|--------------------------------------------------------------------------
| TRANSACTION
|--------------------------------------------------------------------------
*/
Route::get('/checkin', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->index(request());
});

Route::post('/checkin', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->store(request());
});

Route::post('/checkin/scan-ktp', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->scanKtp(request());
});

Route::get('/checkin/{regNo2}/print-registration', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->printRegistration(request(), $regNo2);
});

Route::post('/checkin/{regNo2}/update', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->update(request(), $regNo2);
});

Route::get('/checkin/{regNo2}/delete', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckinController::class)->destroy(request(), $regNo2);
});

Route::get('/checkout', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckoutController::class)->index(request());
});

Route::post('/checkout', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckoutController::class)->store(request());
});

Route::get('/checkout/{regNo}/print-folio', function ($regNo) {
    if ($response = ensureSessionAccess()) return $response;
    return app(CheckoutController::class)->printFolio(request(), $regNo);
});

Route::get('/settings/hotel-branding', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(HotelSettingsController::class)->edit(request());
});

Route::post('/settings/hotel-branding', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(HotelSettingsController::class)->update(request());
});

Route::get('/settings/hotel-branding/logo', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(HotelSettingsController::class)->logo();
});


/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/
Route::get('/guest-in-house', function () {
    if ($response = ensureSessionAccess()) return $response;
    return respondPlaceholder('Guest In House Report');
});

Route::get('/expected-departure', function () {
    if ($response = ensureSessionAccess()) return $response;
    return respondPlaceholder('Expected Departure Report');
});


/*
|--------------------------------------------------------------------------
| TOOLS
|--------------------------------------------------------------------------
*/
Route::get('/user', function () {
    if ($response = ensureSessionAccess()) return $response;
    return respondPlaceholder('User Management Page');
});

Route::get('/change-password', function () {
    if ($response = ensureSessionAccess()) return $response;
    return respondPlaceholder('Change Password Page');
});
