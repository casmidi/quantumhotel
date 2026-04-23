<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $profile = HotelBranding::profile();
        $profile['logo_url'] = !empty($profile['logo_path'])
            ? url('/settings/hotel-branding/logo?ts=' . rawurlencode((string) ($profile['updated_at'] ?? now()->timestamp)))
            : null;

        return $this->respond($request, 'checkout.index', [
            'profile' => $profile,
        ], [
            'profile' => $profile,
        ]);
    }
}
