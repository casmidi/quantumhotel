<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $user = DB::select("
            SELECT TOP 1 *
            FROM dbo.sandi
            WHERE RTRIM(Kode) = ?
        ", [$request->username]);

        if ($user) {
            $user = $user[0];

            if (trim($user->Passw) == trim($request->password)) {

                session([
                    'user' => trim($user->Kode),
                    'role' => trim($user->Nama)
                ]);

                return redirect('/dashboard');
            }
        }

        return back()->with('error', 'Login gagal');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/');
    }
}