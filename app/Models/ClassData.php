<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassData extends Model
{
    protected $table = 'class_data'; // sesuaikan nama tabel

    protected $fillable = [
        'reg_no',
        'res_no',
        'tipe',
        'kode_cust',
        'nama_cust',
        'deposit',
        'service',
        'tax',
        'disc',
        'discount',
        'discount2',
        'rate',
        'posisi',
        'sales'
    ];

    // ========================
    // ACCESSOR (GET)
    // ========================
    public function getRegNoAttribute($value)
    {
        return $value;
    }

    public function getNamaCustAttribute($value)
    {
        return strtoupper($value); // contoh modifikasi
    }

    // ========================
    // MUTATOR (SET)
    // ========================
    public function setRegNoAttribute($value)
    {
        $this->attributes['reg_no'] = $value;
    }

    public function setDepositAttribute($value)
    {
        $this->attributes['deposit'] = (float) $value;
    }
}