<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangePasswordController extends Controller
{
    public function edit(Request $request)
    {
        $userCode = strtoupper(trim((string) session('user')));

        return $this->respond($request, 'settings.change-password', [
            'userCode' => $userCode,
        ], [
            'user' => $userCode,
        ]);
    }

    public function update(Request $request)
    {
        $userCode = strtoupper(trim((string) session('user')));

        if ($userCode === '') {
            return $this->respondError($request, 'Session user was not found. Please login again.', 401, [], '/');
        }

        $validated = $request->validate([
            'old_password' => ['required', 'string', 'max:30'],
            'new_password' => ['required', 'string', 'max:30'],
            'retype_password' => ['required', 'string', 'max:30'],
        ]);

        $oldPassword = trim($validated['old_password']);
        $newPassword = trim($validated['new_password']);
        $retypePassword = trim($validated['retype_password']);

        if ($newPassword !== $retypePassword) {
            return $this->respondError($request, 'Retype password does not match the new password.');
        }

        $user = DB::table('SANDI')
            ->selectRaw('RTRIM(Kode) AS kode, RTRIM(Passw) AS passw')
            ->whereRaw('UPPER(LTRIM(RTRIM(Kode))) = ?', [$userCode])
            ->first();

        if (!$user) {
            return $this->respondError($request, 'User was not found. Please login again.', 404, [], '/logout', false);
        }

        if (trim((string) $user->passw) !== $oldPassword) {
            return $this->respondError($request, 'Old password is incorrect.');
        }

        DB::table('SANDI')
            ->whereRaw('UPPER(LTRIM(RTRIM(Kode))) = ?', [$userCode])
            ->update(['Passw' => $newPassword]);

        return $this->respondAfterMutation(
            $request,
            '/settings/change-password',
            'Password has been changed successfully.'
        );
    }
}
