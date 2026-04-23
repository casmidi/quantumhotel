@extends('layouts.app')

@section('title', 'Check-out')

@section('content')
@include('partials.crud-package-theme')

<style>
    .checkout-shell {
        display: grid;
        gap: 1.25rem;
    }

    .checkout-card {
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .checkout-header {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 1rem;
        align-items: center;
        padding: 1.1rem 1.2rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
    }

    .checkout-logo {
        width: 84px;
        height: 84px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(137, 167, 214, 0.2);
        overflow: hidden;
    }

    .checkout-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .checkout-logo-fallback {
        color: #173761;
        font-size: 0.76rem;
        font-weight: 800;
        text-align: center;
        line-height: 1.35;
    }

    .checkout-title {
        margin: 0;
        color: #173761;
        font-size: 1.35rem;
        font-weight: 900;
    }

    .checkout-subtitle {
        margin: 0.35rem 0 0;
        color: #5e738f;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .checkout-body {
        padding: 1.2rem;
        display: grid;
        gap: 1rem;
    }

    .checkout-note {
        padding: 1rem 1.05rem;
        border-radius: 18px;
        background: #f8fbff;
        border: 1px solid rgba(137, 167, 214, 0.24);
        color: #576d89;
        line-height: 1.65;
    }
</style>

<section class="package-shell checkout-shell">
    <div class="checkout-card">
        <div class="checkout-header">
            <div class="checkout-logo">
                @if (!empty($profile['logo_url']))
                    <img src="{{ $profile['logo_url'] }}" alt="Hotel logo">
                @else
                    <div class="checkout-logo-fallback">HOTEL<br>LOGO</div>
                @endif
            </div>
            <div>
                <h1 class="checkout-title">Check-out Form</h1>
                <p class="checkout-subtitle">
                    Logo hotel dari menu <strong>Settings > Hotel Branding</strong> akan tampil di sini juga, jadi identitas hotel konsisten di check-in, print registrasi, dan checkout.
                </p>
            </div>
        </div>
        <div class="checkout-body">
            <div class="checkout-note">
                Header checkout sudah siap memakai logo hotel yang Anda upload. Kalau nanti form checkout dilengkapi penuh, logo yang sama akan tetap dipakai di pojok kiri atas tanpa perlu upload ulang.
            </div>
        </div>
    </div>
</section>
@endsection
