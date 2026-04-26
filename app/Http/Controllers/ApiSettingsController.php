<?php

namespace App\Http\Controllers;

use App\Support\ApiSettings;
use Illuminate\Http\Request;

class ApiSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $settings = ApiSettings::current();
        $settings['has_static_basic_credential'] = ApiSettings::hasStaticBasicCredential($settings);

        return $this->respond($request, 'settings.api', [
            'settings' => $settings,
        ], [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'auth_mode' => ['required', 'in:basic,token'],
            'basic_username' => ['nullable', 'string', 'max:80'],
            'basic_password' => ['nullable', 'string', 'max:120'],
        ]);

        ApiSettings::save($validated);

        return $this->respondAfterMutation($request, '/settings/api', 'API settings saved successfully.');
    }
}
