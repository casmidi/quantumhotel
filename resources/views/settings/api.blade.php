@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="api-settings-topbar-title">API Settings</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .api-settings-topbar-title {
        color: #173761;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
    }

    .api-settings-shell {
        max-width: 960px;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .api-settings-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
    }

    .api-settings-head h3 {
        margin: 0;
        color: #173761;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .api-settings-head p {
        margin: 0.28rem 0 0;
        color: #60748f;
        font-size: 0.9rem;
    }

    .api-settings-body {
        padding: 1.2rem;
    }

    .api-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .api-settings-field {
        display: grid;
        gap: 0.45rem;
    }

    .api-settings-field-wide {
        grid-column: span 2;
    }

    .api-settings-field label {
        margin: 0;
        color: #173761;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .api-settings-note {
        margin-top: 0.5rem;
        color: #60748f;
        font-size: 0.86rem;
        line-height: 1.45;
    }

    .api-settings-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    @media (max-width: 767.98px) {
        .api-settings-grid {
            grid-template-columns: 1fr;
        }

        .api-settings-field-wide {
            grid-column: span 1;
        }
    }
</style>

<div class="container-fluid px-0">
    @if (session('success'))
        <div class="alert package-alert mb-4" id="successAlert">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/settings/api" class="api-settings-shell">
        @csrf
        <div class="api-settings-head">
            <h3>Global CRUD API Access</h3>
            <p>Applies to every endpoint inside /api/v1 that uses the shared CRUD API middleware.</p>
        </div>

        <div class="api-settings-body">
            <div class="api-settings-grid">
                <div class="api-settings-field api-settings-field-wide">
                    <label for="auth_mode">Authentication Mode</label>
                    <select name="auth_mode" id="auth_mode" class="form-control package-input">
                        <option value="basic" {{ ($settings['auth_mode'] ?? 'basic') === 'basic' ? 'selected' : '' }}>Basic Auth</option>
                        <option value="token" {{ ($settings['auth_mode'] ?? 'basic') === 'token' ? 'selected' : '' }}>Bearer Token</option>
                    </select>
                    <div class="api-settings-note">
                        Basic Auth accepts the API username/password below when filled. If left empty, it accepts existing application users from SANDI. Bearer Token uses /api/v1/login and Authorization: Bearer.
                    </div>
                </div>

                <div class="api-settings-field">
                    <label for="basic_username">Basic Username</label>
                    <input type="text" name="basic_username" id="basic_username" class="form-control package-input"
                        value="{{ old('basic_username', $settings['basic_username'] ?? '') }}" autocomplete="off">
                </div>

                <div class="api-settings-field">
                    <label for="basic_password">Basic Password</label>
                    <input type="password" name="basic_password" id="basic_password" class="form-control package-input"
                        value="" autocomplete="new-password">
                    <div class="api-settings-note">
                        {{ !empty($settings['has_static_basic_credential']) ? 'Password is already set. Leave blank to keep it.' : 'Leave blank to use SANDI user passwords.' }}
                    </div>
                </div>
            </div>

            <div class="api-settings-actions">
                <button type="submit" class="btn package-btn-primary">Save API Settings</button>
            </div>
        </div>
    </form>
</div>
@endsection
