@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="authorization-topbar-title">User Authorization</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .authorization-topbar-title {
        color: var(--package-title);
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
    }

    .authorization-shell {
        display: grid;
        gap: 1rem;
    }

    .authorization-card {
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        overflow: hidden;
    }

    .authorization-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--package-shell-border);
        background: var(--package-header-bg);
        flex-wrap: wrap;
    }

    .authorization-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.08rem;
        font-weight: 850;
    }

    .authorization-head p {
        margin: 0.25rem 0 0;
        color: var(--package-muted);
        font-size: 0.88rem;
    }

    .authorization-body {
        padding: 1rem 1.2rem 1.2rem;
        background: var(--package-shell-bg);
    }

    .authorization-alert {
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
        color: var(--package-title);
        box-shadow: var(--package-shell-shadow);
        font-weight: 700;
    }

    .authorization-card .form-control.package-input,
    .authorization-card select.package-input {
        background: var(--package-input-bg) !important;
        border-color: var(--package-input-border) !important;
        color: var(--package-text) !important;
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04) !important;
    }

    .authorization-card .form-control.package-input:focus,
    .authorization-card select.package-input:focus {
        border-color: var(--package-input-focus) !important;
        box-shadow: var(--package-input-focus-shadow) !important;
    }

    .authorization-card .form-control.package-input::placeholder {
        color: var(--package-muted);
        opacity: 0.72;
    }

    .authorization-secondary-btn {
        border: 1px solid var(--package-input-border) !important;
        border-radius: 999px;
        background: var(--package-button-secondary-bg) !important;
        color: var(--package-button-secondary-text) !important;
        font-weight: 800;
    }

    .authorization-secondary-btn:hover,
    .authorization-secondary-btn:focus {
        border-color: var(--package-input-focus) !important;
        box-shadow: var(--package-input-focus-shadow);
    }

    .authorization-user-grid {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) repeat(3, minmax(120px, 0.35fr)) auto;
        gap: 0.85rem;
        align-items: end;
    }

    .authorization-field {
        display: grid;
        gap: 0.4rem;
    }

    .authorization-field label,
    .authorization-table th {
        color: var(--package-label);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .authorization-stat {
        min-height: 42px;
        display: flex;
        align-items: center;
        padding: 0.55rem 0.75rem;
        border: 1px solid var(--package-input-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
        color: var(--package-title);
        font-weight: 800;
    }

    .authorization-add-user-btn {
        min-height: 42px;
        min-width: 112px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        border: 1px solid #8f650f !important;
        border-radius: 999px;
        background: linear-gradient(180deg, #b78422 0%, #8d5d0e 100%) !important;
        color: #fffaf0 !important;
        box-shadow: 0 10px 24px rgba(132, 86, 11, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.28);
        font-weight: 900;
        white-space: nowrap;
    }

    .authorization-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        margin-bottom: 0.85rem;
        flex-wrap: wrap;
    }

    .authorization-search {
        flex: 1 1 280px;
        max-width: 460px;
    }

    .authorization-actions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .authorization-head-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .authorization-head-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0.55rem 1rem;
        border: 1px solid var(--package-input-border);
        border-radius: 999px;
        background: var(--package-button-secondary-bg);
        color: var(--package-button-secondary-text);
        font-size: 0.86rem;
        font-weight: 850;
        text-decoration: none;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    .authorization-head-action:hover,
    .authorization-head-action:focus {
        color: var(--package-button-secondary-text);
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
        text-decoration: none;
    }

    .authorization-filter {
        display: inline-flex;
        gap: 0.35rem;
        padding: 0.25rem;
        border: 1px solid var(--package-input-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
    }

    .authorization-filter button {
        border: 0;
        border-radius: 7px;
        padding: 0.48rem 0.72rem;
        background: transparent;
        color: var(--package-muted);
        font-size: 0.84rem;
        font-weight: 800;
    }

    .authorization-filter button.is-active {
        background: var(--package-button-primary);
        color: #fff;
    }

    .authorization-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .authorization-form-wide {
        grid-column: span 2;
    }

    .authorization-table-wrap {
        max-height: 62vh;
        overflow: auto;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-shell-bg);
    }

    .authorization-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .authorization-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 0.8rem 0.85rem;
        background: var(--package-table-head-bg);
        border-bottom: 1px solid var(--package-shell-border);
        white-space: nowrap;
    }

    .authorization-table td {
        padding: 0.72rem 0.85rem;
        border-bottom: 1px solid var(--package-shell-border);
        color: var(--package-text);
        vertical-align: middle;
    }

    .authorization-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .authorization-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .authorization-table tbody tr:hover {
        background: var(--package-table-hover);
        box-shadow: inset 4px 0 0 var(--package-table-hover-accent);
    }

    .authorization-menu-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        min-height: 30px;
        padding: 0.25rem 0.48rem;
        border-radius: 8px;
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
        font-weight: 850;
        font-size: 0.78rem;
    }

    .authorization-menu-title {
        display: block;
        color: var(--package-title);
        font-weight: 800;
    }

    .authorization-menu-key {
        display: none;
        margin-top: 0.12rem;
        color: var(--package-muted);
        font-size: 0.82rem;
    }

    .authorization-check {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0;
        color: var(--package-title);
        font-weight: 800;
        cursor: pointer;
    }

    .authorization-check input {
        width: 1.1rem;
        height: 1.1rem;
        accent-color: var(--package-table-hover-accent);
    }

    .authorization-savebar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.7rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .authorization-empty {
        padding: 1.8rem;
        color: var(--package-muted);
        text-align: center;
        font-weight: 700;
    }

    .authorization-master-toolbar {
        display: flex;
        align-items: end;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    .authorization-master-toolbar .authorization-field {
        flex: 1 1 220px;
    }

    .authorization-position-toolbar {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) auto auto;
        align-items: end;
        gap: 0.75rem;
        margin-bottom: 0.9rem;
    }

    .authorization-position-toolbar .authorization-field {
        min-width: 0;
    }

    .authorization-position-toolbar .btn {
        min-height: 46px;
        white-space: nowrap;
    }

    .authorization-position-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin: 0 0 0.65rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
    }

    .authorization-position-summary-title {
        display: grid;
        gap: 0.1rem;
        min-width: 0;
    }

    .authorization-position-summary-title strong {
        color: var(--package-title);
        font-weight: 850;
    }

    .authorization-position-summary-title span {
        color: var(--package-muted);
        font-size: 0.84rem;
        font-weight: 800;
    }

    .authorization-position-summary form {
        margin: 0;
    }

    .authorization-sync-action {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .authorization-sync-copy {
        max-width: 260px;
        color: var(--package-muted);
        font-size: 0.82rem;
        font-weight: 750;
        line-height: 1.25;
        text-align: right;
    }

    .authorization-sync-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.48rem;
        min-height: 42px;
        padding-inline: 1rem;
        border: 1px solid #8f650f !important;
        border-radius: 999px;
        background: linear-gradient(180deg, #a87416 0%, #84560b 100%) !important;
        color: #fffaf0 !important;
        box-shadow: 0 10px 24px rgba(132, 86, 11, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.28);
        font-weight: 900;
        white-space: nowrap;
    }

    .authorization-sync-btn:hover,
    .authorization-sync-btn:focus {
        color: #fffaf0 !important;
        box-shadow: 0 12px 30px rgba(132, 86, 11, 0.25), var(--package-input-focus-shadow);
    }

    .authorization-sync-btn:disabled {
        cursor: not-allowed;
        opacity: 0.55;
        box-shadow: none;
    }

    .authorization-confirm {
        position: fixed;
        inset: 0;
        z-index: 1090;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        pointer-events: none;
    }

    .authorization-confirm.is-open {
        display: flex;
        pointer-events: auto;
    }

    .authorization-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(21, 14, 5, 0.42);
        backdrop-filter: blur(4px);
    }

    .authorization-confirm-dialog {
        position: relative;
        width: min(430px, 100%);
        border: 1px solid rgba(184, 139, 54, 0.38);
        border-radius: 8px;
        background: linear-gradient(180deg, #fffdf8 0%, #fff8ea 100%);
        box-shadow: 0 24px 70px rgba(66, 43, 8, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.8);
        overflow: hidden;
    }

    .authorization-confirm-head {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1rem 1.1rem 0.75rem;
    }

    .authorization-confirm-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 8px;
        background: #fff4dc;
        color: #9f341f;
        box-shadow: inset 0 0 0 1px rgba(184, 139, 54, 0.32);
    }

    .authorization-confirm-title {
        margin: 0;
        color: var(--package-title);
        font-size: 1.05rem;
        font-weight: 900;
    }

    .authorization-confirm-body {
        padding: 0 1.1rem 1rem 4.95rem;
        color: var(--package-muted);
        font-size: 0.93rem;
        font-weight: 750;
        line-height: 1.4;
    }

    .authorization-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
        padding: 0.85rem 1.1rem 1.1rem;
        background: rgba(255, 249, 235, 0.72);
        border-top: 1px solid rgba(184, 139, 54, 0.2);
    }

    .authorization-confirm-cancel,
    .authorization-confirm-ok {
        min-height: 38px;
        padding: 0.48rem 1rem;
        border-radius: 999px;
        font-weight: 900;
    }

    .authorization-confirm-cancel {
        border: 1px solid var(--package-input-border);
        background: #fffdf8;
        color: var(--package-muted);
    }

    .authorization-confirm-ok {
        border: 1px solid #8f650f;
        background: linear-gradient(180deg, #b78422 0%, #8d5d0e 100%);
        color: #fffaf0;
        box-shadow: 0 10px 24px rgba(132, 86, 11, 0.18);
    }

    .authorization-modal {
        position: fixed;
        inset: 0;
        z-index: 1088;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }

    .authorization-modal.is-open {
        display: flex;
    }

    .authorization-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(21, 14, 5, 0.42);
        backdrop-filter: blur(4px);
    }

    .authorization-modal-dialog {
        position: relative;
        width: min(880px, 100%);
        max-height: calc(100vh - 2.5rem);
        overflow: auto;
        border: 1px solid rgba(184, 139, 54, 0.38);
        border-radius: 8px;
        background: var(--package-shell-bg);
        box-shadow: 0 24px 70px rgba(66, 43, 8, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .authorization-modal-preview {
        max-height: 260px;
        overflow: auto;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
    }

    .authorization-modal-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid var(--package-shell-border);
        background: var(--package-table-head-bg);
        color: var(--package-title);
        font-weight: 900;
    }

    .authorization-modal-preview-count {
        color: var(--package-muted);
        font-size: 0.82rem;
        font-weight: 850;
    }

    .authorization-modal-preview-row {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr);
        gap: 0.7rem;
        padding: 0.62rem 0.75rem;
        border-bottom: 1px solid var(--package-shell-border);
        color: var(--package-text);
        font-weight: 750;
    }

    .authorization-modal-preview-row:last-child {
        border-bottom: 0;
    }

    .authorization-modal-warning {
        margin-bottom: 0.85rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid #d8b7ac;
        border-radius: 8px;
        background: #fff7f3;
        color: #9f341f;
        font-weight: 850;
    }

    .authorization-position-feedback {
        margin-bottom: 0.8rem;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-table-hover);
        color: var(--package-title);
        font-weight: 800;
    }

    .authorization-master-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin: 1rem 0 0.65rem;
        color: var(--package-title);
        font-weight: 850;
    }

    .authorization-master-count {
        color: var(--package-muted);
        font-size: 0.84rem;
        font-weight: 800;
    }

    .authorization-master-description {
        min-width: 240px;
    }

    .authorization-master-key {
        color: var(--package-muted);
        font-size: 0.84rem;
        word-break: break-word;
    }

    .authorization-usage-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        min-height: 30px;
        padding: 0.25rem 0.55rem;
        border-radius: 8px;
        background: var(--package-heading-bg);
        color: var(--package-title);
        font-weight: 850;
    }

    .authorization-row-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .authorization-danger-btn {
        border: 1px solid #d8b7ac !important;
        border-radius: 999px;
        background: #fff7f3 !important;
        color: #9f341f !important;
        font-weight: 850;
    }

    .authorization-danger-btn:disabled {
        cursor: not-allowed;
        opacity: 0.52;
    }

    .authorization-icon-btn {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px !important;
    }

    .authorization-visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .authorization-master-row {
        cursor: pointer;
    }

    .authorization-master-row.is-selected {
        background: var(--package-table-hover) !important;
        box-shadow: inset 4px 0 0 var(--package-button-primary);
    }

    .authorization-tabs {
        display: flex;
        gap: 0.45rem;
        padding: 0.25rem;
        border: 1px solid var(--package-input-border);
        border-radius: 8px;
        background: var(--package-heading-bg);
        overflow-x: auto;
    }

    .authorization-tab {
        border: 0;
        border-radius: 7px;
        padding: 0.62rem 0.95rem;
        background: transparent;
        color: var(--package-muted);
        font-weight: 850;
        white-space: nowrap;
    }

    .authorization-tab.is-active {
        background: var(--package-button-primary);
        color: #fff;
    }

    .authorization-tab-panel {
        display: none;
    }

    .authorization-tab-panel.is-active {
        display: grid;
        gap: 1rem;
    }

    .authorization-position-grid {
        display: grid;
        grid-template-columns: minmax(220px, 0.45fr) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .authorization-position-list {
        max-height: 62vh;
        overflow: auto;
        border: 1px solid var(--package-shell-border);
        border-radius: 8px;
        background: var(--package-shell-bg);
    }

    .authorization-position-item {
        width: 100%;
        border: 0;
        border-bottom: 1px solid var(--package-shell-border);
        padding: 0.75rem 0.85rem;
        background: transparent;
        color: var(--package-title);
        text-align: left;
        font-weight: 850;
    }

    .authorization-position-item.is-active {
        background: var(--package-table-hover);
        box-shadow: inset 4px 0 0 var(--package-button-primary);
    }

    @media (max-width: 991.98px) {
        .authorization-user-grid {
            grid-template-columns: 1fr 1fr;
        }

    }

    @media (max-width: 575.98px) {
        .authorization-user-grid {
            grid-template-columns: 1fr;
        }

        .authorization-form-grid {
            grid-template-columns: 1fr;
        }

        .authorization-form-wide {
            grid-column: span 1;
        }

        .authorization-master-toolbar {
            display: grid;
        }

        .authorization-position-toolbar {
            grid-template-columns: 1fr;
        }

        .authorization-position-summary {
            align-items: stretch;
            flex-direction: column;
        }

        .authorization-sync-action {
            justify-content: stretch;
        }

        .authorization-sync-copy {
            max-width: none;
            text-align: left;
        }

        .authorization-confirm-body {
            padding-left: 1.1rem;
        }

        .authorization-position-grid {
            grid-template-columns: 1fr;
        }

        .authorization-topbar-title {
            font-size: 1.35rem;
        }
    }
</style>

<section class="package-shell authorization-shell">
    @if (session('success'))
        <div class="alert authorization-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert authorization-alert">{{ session('error') }}</div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert authorization-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="authorization-tabs" aria-label="User authorization sections">
        <button type="button" class="authorization-tab is-active" data-authorization-tab="user">User Authorization</button>
        <button type="button" class="authorization-tab" data-authorization-tab="positions">Role Menu Defaults</button>
        <button type="button" class="authorization-tab" data-authorization-tab="menus">Master Menu</button>
    </div>

    <div class="authorization-tab-panel is-active" data-authorization-panel="user">

    <div class="authorization-card">
        <div class="authorization-head">
            <div>
                <h3>User Profile</h3>
                <p>Select a user, then configure which menus they can access.</p>
            </div>
        </div>
        <div class="authorization-body">
            <form method="GET" action="/settings/user-authorization" class="authorization-user-grid" id="userSelectorForm">
                <div class="authorization-field">
                    <label for="user">User</label>
                    <select name="user" id="user" class="form-control package-input">
                        @foreach ($users as $user)
                            <option value="{{ $user['kode'] }}" {{ $selectedKode === $user['kode'] ? 'selected' : '' }}>
                                {{ $user['kode'] }} - {{ $user['nama'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="authorization-field">
                    <label>Position</label>
                    <div class="authorization-stat">{{ $selectedUser['nama'] ?? '-' }}</div>
                </div>
                <div class="authorization-field">
                    <label>Cashier Code</label>
                    <div class="authorization-stat">{{ $selectedUser['kode_kasir'] ?? '-' }}</div>
                </div>
                <div class="authorization-field">
                    <label>Allowed Menu</label>
                    <div class="authorization-stat"><span id="allowedCounter">{{ $allowedCount }}</span> / {{ $permissionRows->count() }}</div>
                </div>
                <div class="authorization-field">
                    <label>&nbsp;</label>
                    <button type="button" class="btn authorization-add-user-btn" id="openAddUserModal">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        <span>Add User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="/settings/user-authorization" class="authorization-card" id="authorizationForm">
        @csrf
        <input type="hidden" name="kode" value="{{ $selectedKode }}">

        <div class="authorization-head">
            <div>
                <h3>Menu Access</h3>
                <p>Checked menus are accessible for the selected user.</p>
            </div>
            <div class="authorization-actions">
                <button type="button" class="btn btn-sm authorization-secondary-btn" id="allowAllButton">Check All</button>
                <button type="button" class="btn btn-sm authorization-secondary-btn" id="clearAllButton">Clear All</button>
            </div>
        </div>

        <div class="authorization-body">
            <div class="authorization-toolbar">
                <input type="search" class="form-control package-input authorization-search" id="menuSearch" placeholder="Search menu or key...">
                <div class="authorization-filter" aria-label="Permission filter">
                    <button type="button" class="is-active" data-permission-filter="all">Show All</button>
                    <button type="button" data-permission-filter="allowed">Show Allowed</button>
                    <button type="button" data-permission-filter="not-allowed">Show Not Allowed</button>
                </div>
                <div class="authorization-actions">
                    <button type="submit" class="btn package-btn-primary">Save Authorization</button>
                </div>
            </div>

            <div class="authorization-table-wrap">
                @if ($permissionRows->isEmpty())
                    <div class="authorization-empty">No master menus are available in SANDI3.</div>
                @else
                    <table class="authorization-table">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Code</th>
                                <th>Menu</th>
                                <th style="width: 145px;">Access</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissionRows as $row)
                                <tr data-menu-row data-allowed="{{ $row['allowed'] ? '1' : '0' }}" data-search="{{ strtolower($row['ket'] . ' ' . $row['kunci']) }}">
                                    <td><span class="authorization-menu-code">{{ $row['code'] }}</span></td>
                                    <td>
                                        <span class="authorization-menu-title">{{ $row['label'] }}</span>
                                        <span class="authorization-menu-key">{{ $row['kunci'] }}</span>
                                    </td>
                                    <td>
                                        <label class="authorization-check">
                                            <input type="checkbox" name="permissions[]" value="{{ $row['ket'] }}" {{ $row['allowed'] ? 'checked' : '' }}>
                                            Allowed
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="authorization-savebar">
                <button type="submit" class="btn package-btn-primary">Save Authorization</button>
            </div>
        </div>
    </form>

    </div>

    <div class="authorization-tab-panel" data-authorization-panel="positions">
        <div class="authorization-card">
            <div class="authorization-head">
                <div>
                    <h3>Role Menu Defaults</h3>
                    <p>Select a role on the left to view its default accessible menus.</p>
                </div>
            </div>
            <div class="authorization-body">
                <div class="authorization-position-grid">
                    <div>
                        <div class="authorization-master-heading">
                            <span>Positions</span>
                            <span class="authorization-master-count">{{ $positionDefaults->count() }} positions</span>
                        </div>
                        <div class="authorization-position-list">
                            @foreach ($positionDefaults as $position)
                                <button
                                    type="button"
                                    class="authorization-position-item {{ $loop->first ? 'is-active' : '' }}"
                                    data-position-tab="{{ $loop->iteration }}"
                                    data-position-name="{{ $position['position'] }}"
                                >
                                    {{ $position['position'] }}
                                    <span class="authorization-master-count">{{ $position['user_count'] }} user</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        @foreach ($positionDefaults as $position)
                            <div class="authorization-position-panel {{ $loop->first ? 'is-active' : '' }}" data-position-panel="{{ $loop->iteration }}" {{ $loop->first ? '' : 'hidden' }}>
                                @include('settings.partials.position-default-panel', [
                                    'position' => $position,
                                    'allMenus' => $allMenus,
                                    'positionIndex' => $loop->iteration,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="authorization-tab-panel" data-authorization-panel="menus">
        <div class="authorization-card">
            <div class="authorization-head">
                <div>
                    <h3>Master Menu</h3>
                    <p>Add, edit, and delete master menu records in SANDI3.</p>
                </div>
            </div>
            <div class="authorization-body">
                <form method="POST" action="/settings/user-authorization/menus" class="authorization-master-toolbar" id="addMenuForm">
                    @csrf
                    <input type="hidden" name="selected_user" value="{{ $selectedKode }}">
                    <input type="hidden" name="original_ket" id="menu_original_ket" value="">
                    <div class="authorization-field">
                        <label for="new_ket">Menu Description</label>
                        <input type="text" name="ket" id="new_ket" class="form-control package-input" maxlength="50" placeholder="Example: 999 New Premium Menu" value="{{ old('ket') }}">
                    </div>
                    <div class="authorization-field">
                        <label for="new_kunci">Menu Key</label>
                        <input type="text" name="kunci" id="new_kunci" class="form-control package-input" maxlength="50" placeholder="Example: mnuNewPremiumMenu" value="{{ old('kunci') }}">
                    </div>
                    <button type="button" class="btn btn-sm authorization-secondary-btn" id="newMenuButton" hidden>New</button>
                    <button type="submit" class="btn package-btn-primary" id="saveMenuButton">Add Menu</button>
                </form>

                <div class="authorization-master-heading">
                    <span>Master Menu</span>
                    <span class="authorization-master-count">{{ $masterMenus->count() }} menu</span>
                </div>

                <div class="authorization-table-wrap">
                    @if ($masterMenus->isEmpty())
                        <div class="authorization-empty">No master menus are available in SANDI3.</div>
                    @else
                        <table class="authorization-table">
                            <thead>
                                <tr>
                                    <th style="width: 90px;">Code</th>
                                    <th>Menu Description</th>
                                    <th>Menu Key</th>
                                    <th style="width: 95px;">Used</th>
                                    <th style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($masterMenus as $menu)
                                    <tr
                                        class="authorization-master-row"
                                        data-master-menu-row
                                        data-ket="{{ $menu['ket'] }}"
                                        data-kunci="{{ $menu['kunci'] }}"
                                    >
                                        <td><span class="authorization-menu-code">{{ $menu['code'] }}</span></td>
                                        <td class="authorization-master-description">{{ $menu['ket'] }}</td>
                                        <td class="authorization-master-key">{{ $menu['kunci'] }}</td>
                                        <td><span class="authorization-usage-badge">{{ $menu['usage_count'] }}</span></td>
                                        <td>
                                            <div class="authorization-row-actions">
                                                <button type="button" class="btn btn-sm authorization-secondary-btn" data-edit-menu>Edit</button>
                                                <form method="POST" action="/settings/user-authorization/menus/delete" data-delete-menu-form>
                                                    @csrf
                                                    <input type="hidden" name="selected_user" value="{{ $selectedKode }}">
                                                    <input type="hidden" name="ket" value="{{ $menu['ket'] }}">
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm authorization-danger-btn"
                                                        {{ $menu['usage_count'] > 0 ? 'disabled' : '' }}
                                                        title="{{ $menu['usage_count'] > 0 ? 'Menu has already been used by a user.' : 'Delete menu' }}"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<div class="authorization-modal" id="addUserModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="addUserModalTitle">
    <div class="authorization-modal-backdrop" data-add-user-close></div>
    <form method="POST" action="/settings/user-authorization/users" class="authorization-modal-dialog" id="addUserForm">
        @csrf
        <div class="authorization-head">
            <div>
                <h3 id="addUserModalTitle">Add New User</h3>
                <p>Create a user and load menu access from the selected role defaults.</p>
            </div>
            <button type="button" class="btn btn-sm authorization-secondary-btn" data-add-user-close>Close</button>
        </div>
        <div class="authorization-body">
            <div class="authorization-modal-warning" id="addUserWarning" hidden></div>
            <div class="authorization-form-grid">
                <div class="authorization-field">
                    <label for="add_user_kode">User Code / Name</label>
                    <input type="text" name="kode" id="add_user_kode" class="form-control package-input" maxlength="10" required placeholder="Example: BUDI">
                </div>
                <div class="authorization-field">
                    <label for="add_user_position">Position</label>
                    <select name="nama" id="add_user_position" class="form-control package-input" required>
                        @foreach ($positions as $position)
                            <option value="{{ $position }}">{{ $position }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="authorization-field">
                    <label for="add_user_password">Password</label>
                    <input type="text" name="password" id="add_user_password" class="form-control package-input" maxlength="30" required>
                </div>
                <div class="authorization-field">
                    <label for="add_user_kasir">Cashier Code</label>
                    <input type="text" name="kode_kasir" id="add_user_kasir" class="form-control package-input" maxlength="4" inputmode="numeric" required value="{{ $suggestedCashierCode }}">
                </div>
                <div class="authorization-field">
                    <label for="add_user_sheet">Sheet</label>
                    <select name="sheet" id="add_user_sheet" class="form-control package-input" required>
                        <option value="I">I</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                        <option value="IV">IV</option>
                    </select>
                </div>
                <div class="authorization-field">
                    <label>Active</label>
                    <label class="authorization-check authorization-stat">
                        <input type="checkbox" name="active" value="1" checked>
                        Active user
                    </label>
                </div>
                <div class="authorization-field authorization-form-wide">
                    <label>Default Menu Preview</label>
                    <div class="authorization-modal-preview" id="addUserMenuPreview"></div>
                </div>
            </div>
            <div class="authorization-savebar">
                <button type="button" class="btn btn-sm authorization-secondary-btn" data-add-user-close>Cancel</button>
                <button type="submit" class="btn package-btn-primary">Create User</button>
            </div>
        </div>
    </form>
</div>

<div class="authorization-confirm" id="authorizationConfirm" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="authorizationConfirmTitle">
    <div class="authorization-confirm-backdrop" data-confirm-cancel></div>
    <div class="authorization-confirm-dialog">
        <div class="authorization-confirm-head">
            <span class="authorization-confirm-icon"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span>
            <h3 class="authorization-confirm-title" id="authorizationConfirmTitle">Confirm Action</h3>
        </div>
        <div class="authorization-confirm-body" id="authorizationConfirmMessage">Are you sure?</div>
        <div class="authorization-confirm-actions">
            <button type="button" class="authorization-confirm-cancel" data-confirm-cancel>Cancel</button>
            <button type="button" class="authorization-confirm-ok" data-confirm-ok>Confirm</button>
        </div>
    </div>
</div>

@php
    $positionDefaultMenuPayload = $positionDefaults->mapWithKeys(function ($position) {
        return [
            $position['position'] => $position['menus']->map(function ($menu) {
                return [
                    'code' => $menu['code'],
                    'ket' => $menu['ket'],
                ];
            })->values(),
        ];
    });
@endphp

<script>
    (function () {
        const userSelect = document.getElementById('user');
        const userForm = document.getElementById('userSelectorForm');
        const searchInput = document.getElementById('menuSearch');
        const rows = Array.from(document.querySelectorAll('[data-menu-row]'));
        const checkboxes = Array.from(document.querySelectorAll('input[name="permissions[]"]'));
        const allowedCounter = document.getElementById('allowedCounter');
        const allowAllButton = document.getElementById('allowAllButton');
        const clearAllButton = document.getElementById('clearAllButton');
        const filterButtons = Array.from(document.querySelectorAll('[data-permission-filter]'));
        const tabButtons = Array.from(document.querySelectorAll('[data-authorization-tab]'));
        const tabPanels = Array.from(document.querySelectorAll('[data-authorization-panel]'));
        const positionButtons = Array.from(document.querySelectorAll('[data-position-tab]'));
        const positionPanels = Array.from(document.querySelectorAll('[data-position-panel]'));
        const addMenuForm = document.getElementById('addMenuForm');
        const menuKetInput = document.getElementById('new_ket');
        const menuKunciInput = document.getElementById('new_kunci');
        const menuOriginalKetInput = document.getElementById('menu_original_ket');
        const saveMenuButton = document.getElementById('saveMenuButton');
        const newMenuButton = document.getElementById('newMenuButton');
        const masterMenuRows = Array.from(document.querySelectorAll('[data-master-menu-row]'));
        const editMenuButtons = Array.from(document.querySelectorAll('[data-edit-menu]'));
        const deleteMenuForms = Array.from(document.querySelectorAll('[data-delete-menu-form]'));
        const openAddUserModalButton = document.getElementById('openAddUserModal');
        const addUserModal = document.getElementById('addUserModal');
        const addUserForm = document.getElementById('addUserForm');
        const addUserPosition = document.getElementById('add_user_position');
        const addUserMenuPreview = document.getElementById('addUserMenuPreview');
        const addUserWarning = document.getElementById('addUserWarning');
        const addUserCloseButtons = addUserModal ? Array.from(addUserModal.querySelectorAll('[data-add-user-close]')) : [];
        const confirmModal = document.getElementById('authorizationConfirm');
        const confirmTitle = document.getElementById('authorizationConfirmTitle');
        const confirmMessage = document.getElementById('authorizationConfirmMessage');
        const confirmOkButton = confirmModal ? confirmModal.querySelector('[data-confirm-ok]') : null;
        const confirmCancelButtons = confirmModal ? Array.from(confirmModal.querySelectorAll('[data-confirm-cancel]')) : [];
        const positionDefaultMenus = @json($positionDefaultMenuPayload);
        const existingUserCodes = @json($users->pluck('kode')->map(fn ($kode) => strtoupper(trim((string) $kode)))->values());
        const existingCashierCodes = @json($users->pluck('kode_kasir')->map(fn ($kode) => trim((string) $kode))->filter()->values());
        let activeFilter = 'all';

        function premiumConfirm(message, options = {}) {
            if (!confirmModal || !confirmMessage || !confirmOkButton) {
                return Promise.resolve(window.confirm(message));
            }

            return new Promise((resolve) => {
                const title = options.title || 'Confirm Action';
                const okText = options.okText || 'Confirm';

                confirmTitle.textContent = title;
                confirmMessage.textContent = message;
                confirmOkButton.textContent = okText;
                confirmModal.classList.add('is-open');
                confirmModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                confirmOkButton.focus();

                const cleanup = (answer) => {
                    confirmModal.classList.remove('is-open');
                    confirmModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    confirmOkButton.removeEventListener('click', onConfirm);
                    confirmCancelButtons.forEach((button) => button.removeEventListener('click', onCancel));
                    document.removeEventListener('keydown', onKeydown);

                    resolve(answer);
                };
                const onConfirm = () => cleanup(true);
                const onCancel = () => cleanup(false);
                const onKeydown = (keyboardEvent) => {
                    if (keyboardEvent.key === 'Escape') {
                        cleanup(false);
                    }
                };

                confirmOkButton.addEventListener('click', onConfirm);
                confirmCancelButtons.forEach((button) => button.addEventListener('click', onCancel));
                document.addEventListener('keydown', onKeydown);
            });
        }

        function syncCounter() {
            if (!allowedCounter) {
                return;
            }

            allowedCounter.textContent = String(checkboxes.filter((checkbox) => checkbox.checked).length);
        }

        function setVisibleCheckboxes(checked) {
            rows.forEach((row) => {
                if (row.hidden) {
                    return;
                }

                const checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = checked;
                }
            });
            syncCounter();
        }

        function syncRows() {
            const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

            rows.forEach((row) => {
                const matchesSearch = query === '' || row.dataset.search.includes(query);
                const matchesFilter =
                    activeFilter === 'all' ||
                    (activeFilter === 'allowed' && row.dataset.allowed === '1') ||
                    (activeFilter === 'not-allowed' && row.dataset.allowed !== '1');

                row.hidden = !matchesSearch || !matchesFilter;
            });
        }

        function setMenuFormMode(menu, shouldFocus = false) {
            if (!addMenuForm || !menuKetInput || !menuKunciInput || !menuOriginalKetInput || !saveMenuButton) {
                return;
            }

            const isEdit = Boolean(menu);
            const ket = menu ? menu.ket : '';
            const kunci = menu ? menu.kunci : '';

            addMenuForm.action = isEdit
                ? '/settings/user-authorization/menus/update'
                : '/settings/user-authorization/menus';
            menuOriginalKetInput.value = ket;
            menuKetInput.value = ket;
            menuKunciInput.value = kunci;
            menuKunciInput.readOnly = isEdit;
            saveMenuButton.textContent = isEdit ? 'Save Menu' : 'Add Menu';

            if (newMenuButton) {
                newMenuButton.hidden = !isEdit;
            }

            masterMenuRows.forEach((row) => {
                row.classList.toggle('is-selected', isEdit && row.dataset.ket === ket);
            });

            if (shouldFocus) {
                menuKetInput.focus();
            }
        }

        function menuFromRow(row) {
            return {
                ket: row.dataset.ket || '',
                kunci: row.dataset.kunci || '',
            };
        }

        function selectFirstMasterMenu() {
            const firstRow = masterMenuRows[0] || null;

            if (firstRow) {
                setMenuFormMode(menuFromRow(firstRow));
            } else {
                setMenuFormMode(null);
            }
        }

        function activateAuthorizationTab(tab) {
            const targetTab = tabButtons.some((button) => button.dataset.authorizationTab === tab) ? tab : 'user';

            tabButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.authorizationTab === targetTab);
            });

            tabPanels.forEach((panel) => {
                const isActive = panel.dataset.authorizationPanel === targetTab;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });

            if (targetTab === 'menus') {
                selectFirstMasterMenu();
            }
        }

        function activatePositionTab(tab) {
            positionButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.positionTab === tab);
            });

            positionPanels.forEach((panel) => {
                const isActive = panel.dataset.positionPanel === tab;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });
        }

        function activatePositionByName(positionName) {
            const normalizedPosition = (positionName || '').trim().toUpperCase();
            const button = positionButtons.find((item) => (item.dataset.positionName || '').trim().toUpperCase() === normalizedPosition);

            if (button) {
                activatePositionTab(button.dataset.positionTab || '1');
            }
        }

        function resetConfirmModalState() {
            if (confirmModal) {
                confirmModal.classList.remove('is-open');
                confirmModal.setAttribute('aria-hidden', 'true');
            }

            document.body.style.overflow = '';
        }

        function refreshPositionPanelControls(panel) {
            if (!panel) {
                return;
            }

            const select = panel.querySelector('select[name="menu_ket"]');

            if (select && select.options.length > 0 && select.options[0].value !== '') {
                select.disabled = false;
            }
        }

        function renderAddUserMenuPreview() {
            if (!addUserPosition || !addUserMenuPreview) {
                return;
            }

            const menus = positionDefaultMenus[addUserPosition.value] || [];

            if (menus.length === 0) {
                addUserMenuPreview.innerHTML = `
                    <div class="authorization-modal-preview-head">
                        <span>Default Menus for ${addUserPosition.value}</span>
                        <span class="authorization-modal-preview-count">0 menus</span>
                    </div>
                    <div class="authorization-empty">No default menus are recorded for this position.</div>
                `;
                return;
            }

            addUserMenuPreview.innerHTML = `
                <div class="authorization-modal-preview-head">
                    <span>Default Menus for ${addUserPosition.value}</span>
                    <span class="authorization-modal-preview-count">${menus.length} menus</span>
                </div>
                ${menus.map((menu) => `
                    <div class="authorization-modal-preview-row">
                        <span class="authorization-menu-code">${menu.code || '-'}</span>
                        <span>${menu.ket || ''}</span>
                    </div>
                `).join('')}
            `;
        }

        function openAddUserModal() {
            if (!addUserModal) {
                return;
            }

            if (addUserForm) {
                addUserForm.reset();
            }

            if (addUserWarning) {
                addUserWarning.hidden = true;
                addUserWarning.textContent = '';
            }

            renderAddUserMenuPreview();
            addUserModal.classList.add('is-open');
            addUserModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            const firstInput = addUserModal.querySelector('input:not([type="hidden"]), select, button');

            if (firstInput) {
                firstInput.focus();
            }
        }

        function closeAddUserModal() {
            if (!addUserModal) {
                return;
            }

            addUserModal.classList.remove('is-open');
            addUserModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function clearAddUserWarning() {
            if (!addUserWarning) {
                return;
            }

            addUserWarning.hidden = true;
            addUserWarning.textContent = '';
        }

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activateAuthorizationTab(button.dataset.authorizationTab || 'user');
            });
        });

        positionButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activatePositionTab(button.dataset.positionTab || '1');
            });
        });

        const pageParams = new URLSearchParams(window.location.search);

        activateAuthorizationTab(pageParams.get('tab') || 'user');
        activatePositionByName(pageParams.get('position') || '');

        if (userSelect && userForm) {
            userSelect.addEventListener('change', () => userForm.submit());
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                syncRows();
            });
        }

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const row = checkbox.closest('[data-menu-row]');
                if (row) {
                    row.dataset.allowed = checkbox.checked ? '1' : '0';
                }
                syncCounter();
                syncRows();
            });
        });

        if (allowAllButton) {
            allowAllButton.addEventListener('click', () => setVisibleCheckboxes(true));
        }

        if (clearAllButton) {
            clearAllButton.addEventListener('click', () => setVisibleCheckboxes(false));
        }

        if (openAddUserModalButton) {
            openAddUserModalButton.addEventListener('click', openAddUserModal);
        }

        addUserCloseButtons.forEach((button) => {
            button.addEventListener('click', closeAddUserModal);
        });

        if (addUserForm) {
            addUserForm.querySelectorAll('input, select').forEach((field) => {
                field.addEventListener('input', clearAddUserWarning);
                field.addEventListener('change', clearAddUserWarning);
            });

            addUserForm.addEventListener('submit', (event) => {
                const kodeInput = addUserForm.querySelector('[name="kode"]');
                const cashierInput = addUserForm.querySelector('[name="kode_kasir"]');
                const kode = kodeInput ? kodeInput.value.trim().toUpperCase() : '';
                const kodeKasir = cashierInput ? cashierInput.value.trim() : '';
                const warnings = [];

                if (kodeInput) {
                    kodeInput.value = kode;
                }

                if (cashierInput) {
                    cashierInput.value = kodeKasir;
                }

                if (existingUserCodes.includes(kode)) {
                    warnings.push('User code already exists. The user cannot be saved.');
                }

                if (existingCashierCodes.includes(kodeKasir)) {
                    warnings.push('Cashier code is already used by another user. The user cannot be saved.');
                }

                if (warnings.length === 0) {
                    return;
                }

                event.preventDefault();

                if (addUserWarning) {
                    addUserWarning.hidden = false;
                    addUserWarning.textContent = warnings.join(' ');
                }
            });
        }

        if (addUserPosition) {
            addUserPosition.addEventListener('change', renderAddUserMenuPreview);
            renderAddUserMenuPreview();
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && addUserModal && addUserModal.classList.contains('is-open')) {
                closeAddUserModal();
            }
        });

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.permissionFilter || 'all';
                filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                syncRows();
            });
        });

        deleteMenuForms.forEach((form) => {
            form.addEventListener('submit', (event) => {
                const ketInput = form.querySelector('[name="ket"]');
                const ket = ketInput ? ketInput.value : 'this menu';

                if (!window.confirm(`Delete ${ket}?`)) {
                    event.preventDefault();
                }
            });
        });

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-position-ajax-form]');

            if (!form) {
                return;
            }

            event.preventDefault();

            const panel = form.closest('[data-position-panel]');
            const content = form.closest('[data-position-content]');
            const submitter = event.submitter || form.querySelector('[type="submit"]');
            const modeInput = form.querySelector('[data-position-menu-mode]');
            const confirmMessage = form.dataset.confirmDelete || '';
            const syncConfirmMessage = form.dataset.confirmSync || '';
            const originalText = submitter ? submitter.textContent : '';

            if (confirmMessage) {
                const confirmed = await premiumConfirm(confirmMessage, {
                    title: 'Delete Default Menu',
                    okText: 'Delete',
                });

                if (!confirmed) {
                    return;
                }
            }

            if (syncConfirmMessage) {
                const confirmed = await premiumConfirm(syncConfirmMessage, {
                    title: 'Sync Users',
                    okText: 'Sync Now',
                });

                if (!confirmed) {
                    return;
                }
            }

            if (modeInput) {
                modeInput.value = submitter && submitter.matches('[data-add-all-position-menus]') ? 'all' : 'selected';
            }

            if (submitter) {
                submitter.disabled = true;
                submitter.textContent = confirmMessage ? 'Deleting...' : (syncConfirmMessage ? 'Syncing...' : 'Saving...');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });
                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Unable to save default menu.');
                }

                if (content && payload.data && payload.data.html) {
                    content.outerHTML = payload.data.html;
                }

                resetConfirmModalState();
                refreshPositionPanelControls(panel);

                const refreshedFeedback = panel ? panel.querySelector('[data-position-feedback]') : null;

                if (refreshedFeedback) {
                    refreshedFeedback.hidden = false;
                    refreshedFeedback.textContent = payload.message || 'Saved.';
                }
            } catch (error) {
                const feedback = panel ? panel.querySelector('[data-position-feedback]') : null;

                if (feedback) {
                    feedback.hidden = false;
                    feedback.textContent = error.message || 'Unable to save default menu.';
                } else {
                    window.alert(error.message || 'Unable to save default menu.');
                }

                if (submitter) {
                    submitter.disabled = false;
                    submitter.textContent = originalText;
                }
            }
        });

        masterMenuRows.forEach((row) => {
            const selectRow = (event) => {
                if (event.target.closest('[data-delete-menu-form]')) {
                    return;
                }

                setMenuFormMode(menuFromRow(row), event.type === 'click');
            };

            row.addEventListener('mouseenter', selectRow);
            row.addEventListener('click', selectRow);
        });

        editMenuButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                const row = button.closest('[data-master-menu-row]');

                event.preventDefault();

                if (row) {
                    setMenuFormMode(menuFromRow(row), true);
                }
            });
        });

        if (newMenuButton) {
            newMenuButton.addEventListener('click', () => setMenuFormMode(null));
        }

    }());
</script>
@endsection
