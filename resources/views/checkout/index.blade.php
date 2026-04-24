@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="checkout-topbar-brand">
        <div class="checkout-topbar-title">Check Out</div>
        <div class="checkout-topbar-subtitle">Review active stay, preview folio, then finalize guest departure.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="checkout-header-side">
        <div class="checkout-note-pill">
            <small>Active Guest</small>
            <strong>{{ number_format($directory->total(), 0, ',', '.') }}</strong>
        </div>
        <div class="checkout-note-pill">
            <small>Selected Reg</small>
            <strong>{{ $selectedRegNo !== '' ? $selectedRegNo . ($selectedRegNo2 !== '' ? ' / ' . $selectedRegNo2 : '') : 'Choose guest' }}</strong>
        </div>
    </div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .checkout-topbar-brand {
        display: grid;
        gap: 0.22rem;
    }

    .checkout-topbar-title {
        color: #173761;
        font-size: 2.05rem;
        line-height: 0.98;
        font-weight: 900;
        letter-spacing: -0.045em;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
    }

    .checkout-topbar-subtitle {
        color: #516783;
        font-size: 0.94rem;
        line-height: 1.4;
        font-weight: 600;
    }

    .checkout-header-side {
        display: flex;
        align-items: stretch;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex: 1 1 420px;
    }

    .checkout-note-pill {
        min-width: 170px;
        padding: 0.82rem 0.95rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(138, 170, 219, 0.28);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .checkout-note-pill small {
        display: block;
        margin-bottom: 0.18rem;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #8f6a2d;
    }

    .checkout-note-pill strong {
        display: block;
        font-size: 1rem;
        color: #173761;
        line-height: 1.1;
    }

    .checkout-page {
        display: grid;
        gap: 1rem;
        color: #10233b;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: minmax(360px, 1fr) minmax(0, 1.3fr);
        gap: 1rem;
        align-items: stretch;
    }

    .checkout-section {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .checkout-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1.1rem;
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
        border-bottom: 1px solid rgba(137, 167, 214, 0.25);
    }

    .checkout-section-header h3 {
        margin: 0;
        font-size: 1.12rem;
        font-weight: 800;
        color: #172b4d;
    }

    .checkout-section-body {
        padding: 1.1rem;
    }

    .checkout-section-main-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        flex: 1;
    }

    #checkoutDirectoryShell {
        position: relative;
    }

    #checkoutDirectoryShell.is-loading {
        pointer-events: none;
    }

    .checkout-directory-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: rgba(246, 250, 255, 0.72);
        backdrop-filter: blur(2px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.18s ease, visibility 0.18s ease;
        z-index: 4;
    }

    #checkoutDirectoryShell.is-loading .checkout-directory-loading {
        opacity: 1;
        visibility: visible;
    }

    .checkout-directory-loading-card {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.95rem 1.1rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(137, 167, 214, 0.24);
        box-shadow: 0 12px 28px rgba(16, 35, 59, 0.1);
        color: #173761;
        font-weight: 800;
    }

    .checkout-directory-loading-spinner {
        width: 1rem;
        height: 1rem;
        border-radius: 999px;
        border: 2px solid rgba(23, 55, 97, 0.16);
        border-top-color: #173761;
        animation: checkoutDirectorySpin 0.8s linear infinite;
    }

    @keyframes checkoutDirectorySpin {
        to {
            transform: rotate(360deg);
        }
    }

    .checkout-directory-table {
        table-layout: fixed;
    }

    .checkout-directory-table th,
    .checkout-directory-table td {
        vertical-align: middle;
    }

    .checkout-directory-table thead th {
        position: relative;
        background: rgba(240, 246, 255, 0.92);
        transition: background-color 0.18s ease, box-shadow 0.18s ease;
    }

    .checkout-directory-table thead th.is-sorted {
        background: linear-gradient(180deg, #eaf3ff 0%, #dfeeff 100%);
        box-shadow: inset 0 -2px 0 rgba(15, 90, 184, 0.18);
    }

    .checkout-directory-row.is-active {
        background: rgba(41, 98, 255, 0.07);
    }

    .checkout-directory-row td a {
        color: inherit;
        text-decoration: none;
        display: block;
    }

    .checkout-sort-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #173761;
        text-decoration: none;
        font-weight: 900;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 0.76rem;
        transition: color 0.18s ease;
    }

    .checkout-sort-link:hover {
        color: #0f5ab8;
        text-decoration: none;
    }

    .checkout-sort-link.is-active {
        color: #0f5ab8;
    }

    .checkout-sort-link i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.15rem;
        height: 1.15rem;
        border-radius: 999px;
        color: #8da0ba;
        font-size: 0.72rem;
        background: rgba(141, 160, 186, 0.12);
        transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .checkout-sort-link.is-active i {
        color: currentColor;
        background: rgba(15, 90, 184, 0.12);
        transform: translateY(-1px);
    }

    .checkout-sort-link::after {
        content: "";
        display: inline-block;
        width: 0;
        height: 2px;
        border-radius: 999px;
        background: currentColor;
        transition: width 0.18s ease;
    }

    .checkout-sort-link.is-active::after {
        width: 0.75rem;
    }

    .checkout-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .checkout-search-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(180px, 0.6fr) auto;
        gap: 1rem;
        align-items: end;
    }

    .checkout-form-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        flex: 1;
    }

    .checkout-editor-form {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .checkout-directory-table-wrap {
        flex: 1;
    }

    .checkout-directory-footer {
        margin-top: auto;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(137, 167, 214, 0.18);
    }

    .checkout-footer-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        border: 1px solid rgba(137, 167, 214, 0.24);
    }

    .checkout-footer-copy {
        max-width: 320px;
        color: #5e728d;
        font-size: 0.9rem;
        line-height: 1.5;
        font-weight: 600;
    }

    .checkout-footer-copy strong {
        display: block;
        margin-bottom: 0.15rem;
        color: #173761;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .checkout-footer-pagination {
        display: flex;
        justify-content: flex-end;
        flex: 1;
    }

    .checkout-footer-pagination .package-pagination-wrap {
        margin: 0;
    }

    .checkout-subsection {
        border: 1px solid rgba(137, 167, 214, 0.22);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        overflow: hidden;
    }

    .checkout-subsection-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        background: rgba(235, 243, 255, 0.88);
        border-bottom: 1px solid rgba(137, 167, 214, 0.18);
    }

    .checkout-subsection-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 900;
        letter-spacing: 0.03em;
        color: #173761;
    }

    .checkout-subsection-note {
        color: #637792;
        font-size: 0.83rem;
        font-weight: 600;
        text-align: right;
    }

    .checkout-subsection-body {
        padding: 1rem;
    }

    .checkout-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .checkout-meta-card {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(137, 167, 214, 0.22);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .checkout-meta-card small {
        display: block;
        margin-bottom: 0.3rem;
        color: #6b7b92;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .checkout-meta-card strong {
        display: block;
        color: #173761;
        font-size: 1rem;
        font-weight: 900;
        line-height: 1.25;
        word-break: break-word;
    }

    .checkout-field {
        display: grid;
        gap: 0.42rem;
    }

    .checkout-field-wide {
        grid-column: span 2;
    }

    .checkout-field label {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #5f6f84;
    }

    .checkout-static {
        min-height: calc(2.7rem + 2px);
        display: flex;
        align-items: center;
        padding: 0.65rem 0.85rem;
        border-radius: 14px;
        border: 1px solid rgba(137, 167, 214, 0.28);
        background: rgba(248, 251, 255, 0.98);
        color: #173761;
        font-weight: 700;
    }

    .checkout-static.is-strong {
        background: linear-gradient(180deg, #fffef8 0%, #fff8e8 100%);
        border-color: rgba(196, 150, 69, 0.25);
        color: #6a4b11;
    }

    .checkout-system-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }

    .checkout-system-note {
        padding: 0.95rem 1rem;
        border-radius: 16px;
        background: linear-gradient(180deg, #f4f9ff 0%, #edf5ff 100%);
        border: 1px solid rgba(137, 167, 214, 0.24);
        color: #4f647f;
        font-size: 0.9rem;
        line-height: 1.55;
        font-weight: 600;
    }

    .checkout-system-note strong {
        color: #173761;
    }

    .checkout-readonly {
        min-height: calc(2.7rem + 2px);
        padding: 0.65rem 0.85rem;
        border-radius: 14px;
        border: 1px solid rgba(137, 167, 214, 0.24);
        background: linear-gradient(180deg, #eef4ff 0%, #e8f0ff 100%);
        color: #173761;
        font-weight: 800;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .checkout-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .checkout-summary-card {
        padding: 0.95rem 1rem;
        border-radius: 18px;
        border: 1px solid rgba(137, 167, 214, 0.25);
        background: linear-gradient(180deg, #fbfdff 0%, #f0f5ff 100%);
    }

    .checkout-summary-card small {
        display: block;
        margin-bottom: 0.32rem;
        color: #6f7f96;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .checkout-summary-card strong {
        display: block;
        color: #173761;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .checkout-folio-meta {
        color: #5d6f88;
        font-size: 0.9rem;
    }

    .checkout-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .checkout-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        border: 1px solid rgba(137, 167, 214, 0.24);
    }

    .checkout-action-copy {
        max-width: 430px;
        color: #5e728d;
        font-size: 0.9rem;
        line-height: 1.5;
        font-weight: 600;
    }

    .checkout-action-copy strong {
        display: block;
        margin-bottom: 0.15rem;
        color: #173761;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .checkout-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: #70819a;
        border-radius: 18px;
        background: #f7faff;
        border: 1px dashed rgba(137, 167, 214, 0.35);
    }

    .folio-table {
        width: 100%;
        border-collapse: collapse;
    }

    .folio-table th,
    .folio-table td {
        padding: 0.72rem 0.6rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.18);
        font-size: 0.9rem;
    }

    .folio-table th {
        color: #173761;
        background: rgba(240, 246, 255, 0.92);
        font-size: 0.76rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .folio-table td.text-right,
    .folio-table th.text-right {
        text-align: right;
    }

    .folio-negative {
        color: #b42318;
    }

    .folio-muted {
        color: #8a98ad;
    }

    .folio-footer {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
        gap: 0.75rem;
        margin-top: 1rem;
        align-items: end;
    }

    .folio-footer-note {
        color: #5d6f88;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .folio-footer-total {
        min-width: 150px;
        text-align: right;
    }

    .folio-footer-total small {
        display: block;
        color: #6f7f96;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .folio-footer-total strong {
        display: block;
        color: #173761;
        font-size: 1rem;
        font-weight: 900;
    }

    @media (max-width: 1199.98px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .checkout-search-grid,
        .checkout-meta-grid,
        .checkout-system-grid,
        .checkout-form-grid,
        .checkout-summary-grid,
        .folio-footer {
            grid-template-columns: 1fr;
        }

        .checkout-action-bar,
        .checkout-footer-card,
        .checkout-section-header,
        .checkout-subsection-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .checkout-subsection-note {
            text-align: left;
        }

        .checkout-footer-pagination {
            width: 100%;
            justify-content: flex-start;
        }

        .checkout-field-wide {
            grid-column: span 1;
        }
    }
</style>

<section class="checkout-page">
    @if(session('success'))
        <div class="alert alert-success" id="successAlert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="checkout-grid">
        @include('checkout.partials.directory-section')

        <div class="checkout-section">
            <div class="checkout-section-header">
                <h3>Checkout Form</h3>
                <span class="checkout-folio-meta">No KTP scan here. Final action closes active stay and prints folio.</span>
            </div>
            <div class="checkout-section-body checkout-section-main-body">
                @if($selectedRegistration)
                    <form method="POST" action="/checkout" class="checkout-editor-form">
                        @csrf
                        <input type="hidden" name="reg_no" value="{{ $selectedRegistration['reg_no'] }}">

                        <div class="checkout-form-stack">
                            <div class="checkout-subsection">
                                <div class="checkout-subsection-header">
                                    <h4 class="checkout-subsection-title">Stay Snapshot</h4>
                                    <div class="checkout-subsection-note">Ringkasan utama untuk verifikasi cepat sebelum proses checkout.</div>
                                </div>
                                <div class="checkout-subsection-body">
                                    <div class="checkout-meta-grid">
                                        <div class="checkout-meta-card">
                                            <small>Invoice Preview</small>
                                            <strong>{{ $selectedRegistration['invoice_display'] ?: '-' }}</strong>
                                        </div>
                                        <div class="checkout-meta-card">
                                            <small>Registration</small>
                                            <strong>{{ $selectedRegistration['reg_no'] }}</strong>
                                        </div>
                                        <div class="checkout-meta-card">
                                            <small>Room & Payment</small>
                                            <strong>{{ $selectedRegistration['room_label'] ?: '-' }}</strong>
                                            <span class="folio-muted">{{ $selectedRegistration['payment'] ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-subsection">
                                <div class="checkout-subsection-header">
                                    <h4 class="checkout-subsection-title">Guest Information</h4>
                                    <div class="checkout-subsection-note">Pastikan identitas tamu, company, dan detail menginap sudah sesuai.</div>
                                </div>
                                <div class="checkout-subsection-body">
                                    <div class="checkout-form-grid">
                                        <div class="checkout-field">
                                            <label>Guest Name</label>
                                            <div class="checkout-static is-strong">{{ $selectedRegistration['guest'] ?: '-' }}</div>
                                        </div>
                                        <div class="checkout-field">
                                            <label>Company</label>
                                            <div class="checkout-static">{{ $selectedRegistration['company'] ?: '-' }}</div>
                                        </div>
                                        <div class="checkout-field">
                                            <label>Check In Date</label>
                                            <div class="checkout-static">{{ $selectedRegistration['check_in_date'] ?: '-' }}</div>
                                        </div>
                                        <div class="checkout-field">
                                            <label>Check In Time</label>
                                            <div class="checkout-static">{{ $selectedRegistration['check_in_time'] ?: '-' }}</div>
                                        </div>
                                        <div class="checkout-field checkout-field-wide">
                                            <label>Remark</label>
                                            <div class="checkout-static">{{ $selectedRegistration['remark'] ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-subsection">
                                <div class="checkout-subsection-header">
                                    <h4 class="checkout-subsection-title">System Checkout Time</h4>
                                    <div class="checkout-subsection-note">Tanggal dan jam checkout otomatis mengikuti waktu sistem browser.</div>
                                </div>
                                <div class="checkout-subsection-body">
                                    <input type="hidden" name="checkout_date" id="checkout_date" value="{{ $checkoutDate }}">
                                    <input type="hidden" name="checkout_time" id="checkout_time" value="{{ $checkoutTime }}">

                                    <div class="checkout-system-grid">
                                        <div class="checkout-field">
                                            <label for="checkout_date_display">Check Out Date</label>
                                            <input type="date" id="checkout_date_display" class="checkout-readonly" value="{{ $checkoutDate }}" disabled>
                                        </div>
                                        <div class="checkout-field">
                                            <label for="checkout_time_display">Check Out Time</label>
                                            <input type="time" step="1" id="checkout_time_display" class="checkout-readonly" value="{{ $checkoutTime }}" disabled>
                                        </div>
                                    </div>

                                    <div class="checkout-system-note mt-3">
                                        <strong>Info Sistem</strong>
                                        Waktu checkout diambil otomatis dari komputer yang sedang dipakai user. Field ini dibuat info-only supaya proses kerja lebih konsisten dan tidak membingungkan.
                                    </div>
                                </div>
                            </div>

                            @if($folio)
                                <div class="checkout-subsection">
                                    <div class="checkout-subsection-header">
                                        <h4 class="checkout-subsection-title">Billing Summary</h4>
                                        <div class="checkout-subsection-note">Nilai ringkas sebelum preview folio atau simpan checkout.</div>
                                    </div>
                                    <div class="checkout-subsection-body">
                                        <div class="checkout-summary-grid">
                                            <div class="checkout-summary-card">
                                                <small>Total Debit</small>
                                                <strong>Rp {{ number_format($folio['totals']['debit'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="checkout-summary-card">
                                                <small>Total Credit</small>
                                                <strong>Rp {{ number_format($folio['totals']['credit'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="checkout-summary-card">
                                                <small>Balanced</small>
                                                <strong>Rp {{ number_format($folio['totals']['balance'], 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="checkout-summary-card">
                                                <small>Transaction</small>
                                                <strong>Rp {{ number_format($folio['totals']['transaction'], 0, ',', '.') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="checkout-action-bar">
                                <div class="checkout-action-copy">
                                    <strong>Final Action</strong>
                                    Gunakan preview untuk cek guest folio terakhir. Jika semua sudah sesuai, simpan checkout untuk menutup stay aktif dan mengubah status kamar.
                                </div>
                                <div class="checkout-actions">
                                    <a
                                        href="/checkout/{{ urlencode($selectedRegistration['reg_no']) }}/print-folio?mode=preview&checkout_date={{ urlencode($checkoutDate) }}&checkout_time={{ urlencode($checkoutTime) }}"
                                        target="_blank"
                                        class="btn package-btn-secondary"
                                        id="preview-folio-link"
                                        data-base-url="/checkout/{{ urlencode($selectedRegistration['reg_no']) }}/print-folio"
                                    >Preview Folio</a>
                                    <button type="submit" class="btn package-btn-primary" onclick="return confirm('Proses checkout guest ini sekarang?')">Save Check Out</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="checkout-empty">
                        Pilih satu guest aktif dari directory kiri untuk menyiapkan proses checkout dan preview folio.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="checkout-section">
        <div class="checkout-section-header">
            <h3>Guest Folio Preview</h3>
            <span class="checkout-folio-meta">{{ $folio ? 'Live preview of the same folio layout used for printing.' : 'Preview will appear after a guest is selected.' }}</span>
        </div>
        <div class="checkout-section-body">
            @if($folio)
                <div class="package-table-wrap">
                    <table class="folio-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice#</th>
                                <th>Description</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($folio['lines'] as $line)
                                <tr>
                                    <td>{{ $line['date'] }}</td>
                                    <td>{{ $line['invoice'] }}</td>
                                    <td>{{ $line['description'] }}</td>
                                    <td class="text-right">{{ $line['debit'] == 0 ? '-' : number_format($line['debit'], 0, ',', '.') }}</td>
                                    <td class="text-right {{ $line['credit'] < 0 ? 'folio-negative' : '' }}">{{ $line['credit'] == 0 ? '-' : number_format($line['credit'], 0, ',', '.') }}</td>
                                    <td class="text-right {{ $line['balance'] < 0 ? 'folio-negative' : '' }}">{{ $line['balance'] < 0 ? '(' . number_format(abs($line['balance']), 0, ',', '.') . ')' : number_format($line['balance'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="package-empty">Belum ada baris folio untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="folio-footer">
                    <div class="folio-footer-note">Note : {{ $folio['note'] }}</div>
                    <div class="folio-footer-total">
                        <small>Debit</small>
                        <strong>Rp {{ number_format($folio['totals']['debit'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="folio-footer-total">
                        <small>Credit</small>
                        <strong>Rp {{ number_format($folio['totals']['credit'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="folio-footer-total">
                        <small>Balanced</small>
                        <strong>Rp {{ number_format($folio['totals']['balance'], 0, ',', '.') }}</strong>
                    </div>
                </div>
            @else
                <div class="checkout-empty">
                    Folio preview akan muncul di sini setelah satu registration dipilih.
                </div>
            @endif
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('checkout_date');
    const timeInput = document.getElementById('checkout_time');
    const dateDisplayInput = document.getElementById('checkout_date_display');
    const timeDisplayInput = document.getElementById('checkout_time_display');
    const previewLink = document.getElementById('preview-folio-link');

    if (!dateInput || !timeInput || !dateDisplayInput || !timeDisplayInput) {
        return;
    }

    const pad = (value) => String(value).padStart(2, '0');
    const now = new Date();
    const currentDate = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    const currentTime = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

    dateInput.value = currentDate;
    timeInput.value = currentTime;
    dateDisplayInput.value = currentDate;
    timeDisplayInput.value = currentTime;

    const syncPreviewLink = () => {
        if (!previewLink) {
            return;
        }

        const url = new URL(previewLink.dataset.baseUrl, window.location.origin);
        url.searchParams.set('mode', 'preview');
        url.searchParams.set('checkout_date', dateInput.value);
        url.searchParams.set('checkout_time', timeInput.value);
        previewLink.href = url.toString();
    };

    syncPreviewLink();

    let checkoutDirectoryRequestController = null;
    const checkoutDirectoryCache = new Map();

    const parseCheckoutDirectoryShell = (html) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        return doc.getElementById('checkoutDirectoryShell');
    };

    const cloneCheckoutDirectoryShell = (shell) => shell ? shell.cloneNode(true) : null;

    const cacheCheckoutDirectoryShell = (url, shell) => {
        if (!url || !shell) {
            return;
        }

        checkoutDirectoryCache.set(String(url), cloneCheckoutDirectoryShell(shell));
    };

    const getCachedCheckoutDirectoryShell = (url) => {
        const cached = checkoutDirectoryCache.get(String(url));
        return cached ? cloneCheckoutDirectoryShell(cached) : null;
    };

    const replaceCheckoutDirectoryShell = (nextShell) => {
        const currentShell = document.getElementById('checkoutDirectoryShell');
        if (!currentShell || !nextShell) {
            return;
        }

        currentShell.replaceWith(nextShell);
    };

    const refreshCheckoutDirectory = async (url, options = {}) => {
        const currentShell = document.getElementById('checkoutDirectoryShell');
        if (!currentShell) {
            window.location.href = url;
            return;
        }

        const targetUrl = new URL(url, window.location.origin);
        const cachedShell = options.forceRefresh ? null : getCachedCheckoutDirectoryShell(targetUrl.toString());
        if (cachedShell) {
            replaceCheckoutDirectoryShell(cachedShell);
            if (options.pushHistory !== false) {
                window.history.pushState({ checkoutDirectory: true }, '', targetUrl.pathname + targetUrl.search + targetUrl.hash);
            }
            return;
        }

        if (checkoutDirectoryRequestController) {
            checkoutDirectoryRequestController.abort();
        }

        const controller = new AbortController();
        checkoutDirectoryRequestController = controller;
        currentShell.classList.add('is-loading');
        currentShell.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(targetUrl.toString(), {
                headers: {
                    'Accept': 'text/html',
                    'X-Partial-Component': 'checkout-directory',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error('Checkout directory request failed.');
            }

            const html = await response.text();
            const nextShell = parseCheckoutDirectoryShell(html);

            if (!nextShell) {
                throw new Error('Checkout directory markup is missing.');
            }

            cacheCheckoutDirectoryShell(targetUrl.toString(), nextShell);
            replaceCheckoutDirectoryShell(cloneCheckoutDirectoryShell(nextShell));

            if (options.pushHistory !== false) {
                window.history.pushState({ checkoutDirectory: true }, '', targetUrl.pathname + targetUrl.search + targetUrl.hash);
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Unable to refresh checkout directory:', error);
            window.location.href = targetUrl.pathname + targetUrl.search + targetUrl.hash;
        } finally {
            checkoutDirectoryRequestController = null;
            document.getElementById('checkoutDirectoryShell')?.classList.remove('is-loading');
            document.getElementById('checkoutDirectoryShell')?.setAttribute('aria-busy', 'false');
        }
    };

    document.addEventListener('submit', function (event) {
        const submittedForm = event.target;
        if (!(submittedForm instanceof HTMLFormElement)) {
            return;
        }

        if (submittedForm.id !== 'checkoutSearchForm' && !submittedForm.classList.contains('checkout-per-page')) {
            return;
        }

        event.preventDefault();
        const targetUrl = new URL(submittedForm.getAttribute('action') || '/checkout', window.location.origin);
        const params = new URLSearchParams(new FormData(submittedForm));

        if (submittedForm.id === 'checkoutSearchForm') {
            params.delete('page');
        }

        const queryString = params.toString();
        refreshCheckoutDirectory(targetUrl.pathname + (queryString ? '?' + queryString : ''));
    });

    document.addEventListener('click', function (event) {
        const directoryShell = document.getElementById('checkoutDirectoryShell');
        if (!directoryShell) {
            return;
        }

        const link = event.target.closest('a');
        if (!link || !directoryShell.contains(link)) {
            return;
        }

        const href = link.getAttribute('href') || '';
        if (!href || href === '#') {
            return;
        }

        if (link.classList.contains('checkout-sort-link')) {
            event.preventDefault();
            refreshCheckoutDirectory(href);
            return;
        }

        if (link.closest('.package-pagination-wrap') || link.closest('.checkout-actions')) {
            if (href.startsWith('/checkout?') || href === '/checkout') {
                event.preventDefault();
                refreshCheckoutDirectory(href);
            }
        }
    });

    window.addEventListener('popstate', function () {
        if (!window.location.pathname.startsWith('/checkout')) {
            return;
        }

        refreshCheckoutDirectory(window.location.pathname + window.location.search + window.location.hash, {
            pushHistory: false,
            forceRefresh: true,
        });
    });
});
</script>
@endsection
