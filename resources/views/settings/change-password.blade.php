@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="change-password-topbar-title">Change Password</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .change-password-topbar-title {
        color: var(--package-title);
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
        font-size: 2rem;
        font-weight: 900;
        line-height: 1;
    }

    .change-password-shell {
        max-width: 720px;
    }

    .change-password-card {
        overflow: hidden;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-shell-bg);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .change-password-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--package-shell-border);
        background: var(--package-header-bg);
    }

    .change-password-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.08rem;
        font-weight: 850;
    }

    .change-password-head p {
        margin: 0.25rem 0 0;
        color: var(--package-muted);
        font-size: 0.88rem;
    }

    .change-password-body {
        display: grid;
        gap: 0.9rem;
        padding: 1.1rem 1.2rem 1.25rem;
    }

    .change-password-field {
        display: grid;
        gap: 0.4rem;
    }

    .change-password-field label {
        color: var(--package-label);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .change-password-card .form-control.package-input {
        background: var(--package-input-bg) !important;
        border-color: var(--package-input-border) !important;
        color: var(--package-text) !important;
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04) !important;
    }

    .change-password-card .form-control.package-input:focus {
        border-color: var(--package-input-focus) !important;
        box-shadow: var(--package-input-focus-shadow) !important;
    }

    .change-password-readonly {
        font-weight: 850;
    }

    .change-password-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 0.35rem;
    }

    @media (max-width: 575.98px) {
        .change-password-shell {
            max-width: none;
        }
    }
</style>

<section class="change-password-shell">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/settings/change-password" class="change-password-card">
        @csrf
        <div class="change-password-head">
            <h3>Change Password</h3>
            <p>Update password for the current signed-in user.</p>
        </div>

        <div class="change-password-body">
            <div class="change-password-field">
                <label for="user_code">User</label>
                <input type="text" id="user_code" class="form-control package-input change-password-readonly" value="{{ $userCode }}" readonly>
            </div>

            <div class="change-password-field">
                <label for="old_password">Old Password</label>
                <input type="password" name="old_password" id="old_password" class="form-control package-input" maxlength="30" autocomplete="current-password" required autofocus>
            </div>

            <div class="change-password-field">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control package-input" maxlength="30" autocomplete="new-password" required>
            </div>

            <div class="change-password-field">
                <label for="retype_password">Retype</label>
                <input type="password" name="retype_password" id="retype_password" class="form-control package-input" maxlength="30" autocomplete="new-password" required>
            </div>

            <div class="change-password-actions">
                <button type="submit" class="btn package-btn-primary">Save Password</button>
            </div>
        </div>
    </form>
</section>
@endsection
