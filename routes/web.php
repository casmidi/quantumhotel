<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiSettingsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClassTestController;
use App\Http\Controllers\ExpectedDepartureController;
use App\Http\Controllers\HotelSettingsController;
use App\Http\Controllers\GuestInHouseController;
use App\Http\Controllers\NightAuditController;
use App\Http\Controllers\ReceptionCustomerRecaptulationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockPackageController;
use App\Http\Controllers\PackageTransactionController;
use App\Http\Controllers\SynchroniseController;
use App\Http\Controllers\UserAuthorizationController;
use App\Http\Controllers\ChangePasswordController;

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

if (!function_exists('ensureMenuPermission')) {
    function ensureMenuPermission(string $menuKet)
    {
        $user = strtoupper(trim((string) session('user')));

        if ($user === 'S') {
            return null;
        }

        $allowed = DB::table('SANDI2')
            ->whereRaw('RTRIM(Kode) = ?', [$user])
            ->whereRaw('RTRIM(Ket) = ?', [$menuKet])
            ->whereRaw("RTRIM(Boleh) = '*'")
            ->exists();

        if ($allowed) {
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
                'message' => 'You are not authorized to open this menu.',
            ], 403);
        }

        return redirect('/dashboard')->with('error', 'You are not authorized to open this menu.');
    }
}

if (!function_exists('ensureSupervisorAccess')) {
    function ensureSupervisorAccess()
    {
        $role = strtoupper(trim((string) session('role')));

        if ($role === 'SUPERVISOR') {
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
                'message' => 'Only supervisors can access this menu.',
            ], 403);
        }

        return redirect('/dashboard')->with('error', 'Only supervisors can access this menu.');
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

Route::get('/synchronise', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('Q02 Synchronise')) return $response;
    return app(SynchroniseController::class)->index(request());
})->name('synchronise.index');

Route::post('/synchronise/run', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('Q02 Synchronise')) return $response;
    return app(SynchroniseController::class)->run(request());
})->name('synchronise.run');

Route::get('/dashboard', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(DashboardController::class)->index();
});

Route::get('/tools/class-test', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(ClassTestController::class)->index(request());
})->name('tools.class-test');

Route::post('/tools/class-test', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(ClassTestController::class)->fetch(request());
})->name('tools.class-test.fetch');


/*
|--------------------------------------------------------------------------
| MASTER DATA - KELAS
|--------------------------------------------------------------------------
*/
Route::get('/kelas', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('130 Master Kelas')) return $response;
    return app(KelasController::class)->index(request());
});

Route::post('/kelas', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('130 Master Kelas')) return $response;
    return app(KelasController::class)->store(request());
});

Route::post('/kelas/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('130 Master Kelas')) return $response;
    return app(KelasController::class)->update(request(), $kode);
});

Route::get('/kelas/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('130 Master Kelas')) return $response;
    return app(KelasController::class)->destroy(request(), $kode);
});


/*
|--------------------------------------------------------------------------
| MASTER DATA - ROOM
|--------------------------------------------------------------------------
*/
Route::get('/room', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('102 Master Room')) return $response;
    return app(RoomController::class)->index(request());
});

Route::post('/room', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('102 Master Room')) return $response;
    return app(RoomController::class)->store(request());
});

Route::post('/room/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('102 Master Room')) return $response;
    return app(RoomController::class)->update(request(), $kode);
});

Route::get('/room/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('102 Master Room')) return $response;
    return app(RoomController::class)->destroy(request(), $kode);
});


/*
|--------------------------------------------------------------------------
| PACKAGE
|--------------------------------------------------------------------------
*/
Route::get('/stock-package', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M01 Item Package For Global')) return $response;
    return redirect('/item-package-global');
});

Route::get('/item-package-global', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M01 Item Package For Global')) return $response;
    return app(StockPackageController::class)->index(request());
});

Route::post('/item-package-global', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M01 Item Package For Global')) return $response;
    return app(StockPackageController::class)->store(request());
});

Route::post('/item-package-global/{kode}/update', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M01 Item Package For Global')) return $response;
    return app(StockPackageController::class)->update(request(), $kode);
});

Route::get('/item-package-global/{kode}/delete', function ($kode) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M01 Item Package For Global')) return $response;
    return app(StockPackageController::class)->destroy(request(), $kode);
});

Route::get('/menu-package-transaction', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M02 Menu Package for transaction')) return $response;
    return app(PackageTransactionController::class)->index(request());
});

Route::post('/menu-package-transaction', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M02 Menu Package for transaction')) return $response;
    return app(PackageTransactionController::class)->store(request());
});

Route::post('/menu-package-transaction/{nofak}/update', function ($nofak) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M02 Menu Package for transaction')) return $response;
    return app(PackageTransactionController::class)->update(request(), $nofak);
});

Route::get('/menu-package-transaction/{nofak}/delete', function ($nofak) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M02 Menu Package for transaction')) return $response;
    return app(PackageTransactionController::class)->destroy(request(), $nofak);
});


/*
|--------------------------------------------------------------------------
| TRANSACTION
|--------------------------------------------------------------------------
*/
Route::get('/booking', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(BookingController::class)->index(request());
});

Route::post('/booking', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(BookingController::class)->store(request());
});

Route::post('/booking/{resno2}/update', function ($resno2) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(BookingController::class)->update(request(), $resno2);
});

Route::get('/booking/{resno2}/delete', function ($resno2) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(BookingController::class)->destroy(request(), $resno2);
});

Route::get('/checkin', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->index(request());
});

Route::post('/checkin', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->store(request());
});

Route::post('/checkin/scan-ktp', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->scanKtp(request());
});

Route::get('/checkin/{regNo2}/print-registration', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->printRegistration(request(), $regNo2);
});

Route::post('/checkin/{regNo2}/update', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->update(request(), $regNo2);
});

Route::get('/checkin/{regNo2}/delete', function ($regNo2) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('400 Transaksi Check In')) return $response;
    return app(CheckinController::class)->destroy(request(), $regNo2);
});

Route::get('/checkout', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('410 Transaksi Check Out')) return $response;
    return app(CheckoutController::class)->index(request());
});

Route::post('/checkout', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('410 Transaksi Check Out')) return $response;
    return app(CheckoutController::class)->store(request());
});

Route::get('/checkout/{regNo}/print-folio', function ($regNo) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('410 Transaksi Check Out')) return $response;
    return app(CheckoutController::class)->printFolio(request(), $regNo);
});

Route::get('/checkout/{regNo}/export-folio/{format}', function ($regNo, $format) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('410 Transaksi Check Out')) return $response;
    return app(CheckoutController::class)->exportFolio(request(), $regNo, $format);
});

Route::get('/night-audit', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->index(request());
});

Route::post('/night-audit/start', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->start(request());
});

Route::get('/night-audit/{batchId}/report', function ($batchId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->report(request(), (int) $batchId);
});

Route::post('/night-audit/{batchId}/refresh', function ($batchId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->refresh(request(), (int) $batchId);
});

Route::post('/night-audit/{batchId}/close', function ($batchId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->close(request(), (int) $batchId);
});

Route::post('/night-audit/{batchId}/approve', function ($batchId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->approve(request(), (int) $batchId);
});

Route::post('/night-audit/{batchId}/adjustments', function ($batchId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->storeAdjustment(request(), (int) $batchId);
});

Route::post('/night-audit/checklist/{checklistId}', function ($checklistId) {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('M31 Night Audit Report')) return $response;
    return app(NightAuditController::class)->updateChecklist(request(), (int) $checklistId);
});

Route::get('/settings/hotel-branding', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('Q01 Hotel Branding')) return $response;
    return app(HotelSettingsController::class)->edit(request());
});

Route::post('/settings/hotel-branding', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('Q01 Hotel Branding')) return $response;
    return app(HotelSettingsController::class)->update(request());
});

Route::get('/settings/hotel-branding/logo', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('Q01 Hotel Branding')) return $response;
    return app(HotelSettingsController::class)->logo();
});

Route::get('/settings/api', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(ApiSettingsController::class)->edit(request());
});

Route::post('/settings/api', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(ApiSettingsController::class)->update(request());
});

Route::get('/settings/user-authorization', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->index(request());
});

Route::post('/settings/user-authorization', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->update(request());
});

Route::post('/settings/user-authorization/users', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->storeUser(request());
});

Route::post('/settings/user-authorization/positions/menus', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->storePositionDefaultMenus(request());
});

Route::post('/settings/user-authorization/positions/apply', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->applyPositionDefaultMenus(request());
});

Route::post('/settings/user-authorization/positions/menus/delete', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->destroyPositionDefaultMenu(request());
});

Route::post('/settings/user-authorization/menus', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->storeMenu(request());
});

Route::post('/settings/user-authorization/menus/update', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->updateMenu(request());
});

Route::post('/settings/user-authorization/menus/delete', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureSupervisorAccess()) return $response;
    return app(UserAuthorizationController::class)->destroyMenu(request());
});

Route::get('/settings/change-password', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(ChangePasswordController::class)->edit(request());
});

Route::post('/settings/change-password', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(ChangePasswordController::class)->update(request());
});


/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/
Route::get('/guest-in-house', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('902 Laporan Guest In House')) return $response;
    return app(GuestInHouseController::class)->index(request());
});

Route::get('/guest-in-house/print', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('902 Laporan Guest In House')) return $response;
    return app(GuestInHouseController::class)->print(request());
});

Route::get('/expected-departure', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('918 Laporan Expected Departure')) return $response;
    return app(ExpectedDepartureController::class)->index(request());
});

Route::get('/expected-departure/print', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('918 Laporan Expected Departure')) return $response;
    return app(ExpectedDepartureController::class)->print(request());
});

Route::get('/booking-report', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(BookingReportController::class)->index(request());
});

Route::get('/booking-report/print', function () {
    if ($response = ensureSessionAccess()) return $response;
    return app(BookingReportController::class)->print(request());
});

Route::get('/reception-customer-recaptulation', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('925 Laporan Room Recapitulation')) return $response;
    return app(ReceptionCustomerRecaptulationController::class)->index(request());
});

Route::get('/reception-customer-recaptulation/print', function () {
    if ($response = ensureSessionAccess()) return $response;
    if ($response = ensureMenuPermission('925 Laporan Room Recapitulation')) return $response;
    return app(ReceptionCustomerRecaptulationController::class)->print(request());
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
    return redirect('/settings/change-password');
});
