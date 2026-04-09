<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('cari_deposit_data')) {
    function cari_deposit_data($regno)
    {
        $result = DB::table('DATA')
            ->where('RegNo', $regno)
            ->value('Deposit');

        return $result ?? 0;
    }
}

if (!function_exists('cari_card')) {
    function cari_card($regno)
    {
        $result = DB::table('Kas')
            ->where('RegNo', $regno)
            ->where('TipeBayar', 'Kartu Kredit')
            ->sum('nominal');

        return $result ?? 0;
    }
}

if (!function_exists('cari_pelunasan_card_pershift')) {
    function cari_pelunasan_card_pershift($regno, $tgl1, $tgl2)
    {
        $result = DB::table('Kas')
            ->where('RegNo', $regno)
            ->where('TipeBayar', '<>', 'Tunai')
            ->where('StrTgl', '<', $tgl1)
            ->sum('nominal');

        return $result ?? 0;
    }
}

if (!function_exists('cari_deposit_kas')) {
    function cari_deposit_kas($regno)
    {
        $hasil = DB::table('KAS')
            ->where('RegNo', $regno)
            ->sum('Nominal');

        return $hasil ?? 0;
    }
}
