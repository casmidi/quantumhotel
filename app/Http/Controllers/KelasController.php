<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class KelasController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $data = DB::select("SELECT * FROM Kelas ORDER BY Kode");
        return view('kelas.index', compact('data'));
    }
}