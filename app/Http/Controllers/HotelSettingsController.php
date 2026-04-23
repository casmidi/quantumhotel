<?php

namespace App\Http\Controllers;

use App\Support\HotelBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HotelSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $profile = HotelBranding::profile();
        $profile['logo_url'] = $this->logoUrl($profile);
        $themeOptions = HotelBranding::themeOptions();

        return $this->respond($request, 'settings.hotel-branding', [
            'profile' => $profile,
            'themeOptions' => $themeOptions,
        ], [
            'profile' => $profile,
            'theme_options' => $themeOptions,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'NamaPT' => ['required', 'string', 'max:120'],
            'UsahaPT' => ['nullable', 'string', 'max:80'],
            'FaxPT' => ['nullable', 'string', 'max:80'],
            'AlamatPT' => ['nullable', 'string', 'max:255'],
            'AlamatPT2' => ['nullable', 'string', 'max:120'],
            'TelponPT' => ['nullable', 'string', 'max:80'],
            'WebsitePT' => ['nullable', 'string', 'max:120'],
            'EmailPT' => ['nullable', 'string', 'max:120'],
            'FormTheme' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        HotelBranding::save(
            $validated,
            $request->file('logo'),
            (bool) ($validated['remove_logo'] ?? false)
        );

        return $this->respondAfterMutation($request, '/settings/hotel-branding', 'Logo hotel dan profil hotel berhasil disimpan.');
    }

    public function logo()
    {
        $path = HotelBranding::logoAbsolutePath();

        abort_if(!$path, 404);

        return Response::file($path);
    }

    private function logoUrl(array $profile): ?string
    {
        if (empty($profile['logo_path'])) {
            return null;
        }

        $stamp = rawurlencode((string) ($profile['updated_at'] ?? now()->timestamp));

        return url('/settings/hotel-branding/logo?ts=' . $stamp);
    }
}
