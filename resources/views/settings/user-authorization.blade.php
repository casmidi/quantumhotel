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
        grid-template-columns: minmax(260px, 1fr) repeat(3, minmax(120px, 0.35fr));
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

    .authorization-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1080;
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
        background: rgba(5, 12, 20, 0.52);
        backdrop-filter: blur(3px);
    }

    .authorization-modal-dialog {
        position: relative;
        width: min(760px, 100%);
        max-height: calc(100vh - 2.5rem);
        overflow: auto;
        border-radius: 8px;
        box-shadow: 0 28px 70px rgba(4, 10, 18, 0.28);
    }

    .authorization-modal-close {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--package-input-border);
        border-radius: 999px;
        background: var(--package-button-secondary-bg);
        color: var(--package-button-secondary-text);
        font-weight: 900;
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

    <div class="authorization-card">
        <div class="authorization-head">
            <div>
                <h3>User Profile</h3>
                <p>Select a user, then configure which menus they can access.</p>
            </div>
            <div class="authorization-head-actions">
                <button type="button" class="authorization-head-action" data-modal-target="addUserModal">Add User</button>
                <button type="button" class="authorization-head-action" data-modal-target="addMenuModal">Add Menu</button>
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

    <div class="authorization-modal" id="addUserModal" aria-hidden="true">
        <div class="authorization-modal-backdrop" data-modal-close></div>
        <form method="POST" action="/settings/user-authorization/users" class="authorization-card authorization-modal-dialog" id="addUserForm">
            @csrf
            <div class="authorization-head">
                <div>
                    <h3>Add User</h3>
                    <p>New user records are saved to the SANDI table.</p>
                </div>
                <button type="button" class="authorization-modal-close" data-modal-close aria-label="Close">x</button>
            </div>
            <div class="authorization-body">
                <div class="alert authorization-alert" id="addUserWarning" hidden></div>
                <div class="authorization-form-grid">
                    <div class="authorization-field">
                        <label for="new_kode">User Code</label>
                        <input type="text" name="kode" id="new_kode" class="form-control package-input" maxlength="10" value="{{ old('kode') }}">
                    </div>
                    <div class="authorization-field">
                        <label for="new_kode_kasir">Cashier Code</label>
                        <input type="text" name="kode_kasir" id="new_kode_kasir" class="form-control package-input" maxlength="4" inputmode="numeric" value="{{ old('kode_kasir', $suggestedCashierCode) }}">
                    </div>
                    <div class="authorization-field">
                        <label for="new_nama">Position</label>
                        <select name="nama" id="new_nama" class="form-control package-input">
                            @foreach (['OWNER', 'MANAGER', 'KEUANGAN', 'SUPERVISOR', 'RESEPSIONIS', 'HOUSE KEEPING', 'TEKNISI', 'KASIR', 'KEPALA GUDANG', 'STAFF GUDANG', 'ADMINISTRASI', 'F&B'] as $position)
                                <option value="{{ $position }}" {{ old('nama') === $position ? 'selected' : '' }}>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="authorization-field">
                        <label for="new_sheet">Sheet</label>
                        <select name="sheet" id="new_sheet" class="form-control package-input">
                            @foreach (['I', 'II', 'III', 'IV'] as $sheet)
                                <option value="{{ $sheet }}" {{ old('sheet', 'I') === $sheet ? 'selected' : '' }}>{{ $sheet }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="authorization-field authorization-form-wide">
                        <label for="new_password">Password</label>
                        <input type="password" name="password" id="new_password" class="form-control package-input" maxlength="30" autocomplete="new-password">
                    </div>
                    <div class="authorization-field authorization-form-wide">
                        <label class="authorization-check" for="new_active">
                            <input type="checkbox" name="active" id="new_active" value="1" {{ old('active', '1') ? 'checked' : '' }}>
                            Active user
                        </label>
                    </div>
                </div>
                <div class="authorization-savebar">
                    <button type="submit" class="btn package-btn-primary">Add User</button>
                </div>
            </div>
        </form>
    </div>

    <div class="authorization-modal" id="addMenuModal" aria-hidden="true">
        <div class="authorization-modal-backdrop" data-modal-close></div>
        <form method="POST" action="/settings/user-authorization/menus" class="authorization-card authorization-modal-dialog" id="addMenuForm">
            @csrf
            <input type="hidden" name="selected_user" value="{{ $selectedKode }}">
            <div class="authorization-head">
                <div>
                    <h3>Add Menu</h3>
                    <p>New master menu records are saved to SANDI3.</p>
                </div>
                <button type="button" class="authorization-modal-close" data-modal-close aria-label="Close">x</button>
            </div>
            <div class="authorization-body">
                <div class="authorization-form-grid">
                    <div class="authorization-field authorization-form-wide">
                        <label for="new_ket">Menu Description</label>
                        <input type="text" name="ket" id="new_ket" class="form-control package-input" maxlength="50" placeholder="Example: 999 New Premium Menu" value="{{ old('ket') }}">
                    </div>
                    <div class="authorization-field authorization-form-wide">
                        <label for="new_kunci">Menu Key</label>
                        <input type="text" name="kunci" id="new_kunci" class="form-control package-input" maxlength="50" placeholder="Example: mnuNewPremiumMenu" value="{{ old('kunci') }}">
                    </div>
                </div>
                <div class="authorization-savebar">
                    <button type="submit" class="btn package-btn-primary">Add Menu</button>
                </div>
            </div>
        </form>
    </div>
</section>

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
        const modalTriggers = Array.from(document.querySelectorAll('[data-modal-target]'));
        const modals = Array.from(document.querySelectorAll('.authorization-modal'));
        const addUserForm = document.getElementById('addUserForm');
        const addUserWarning = document.getElementById('addUserWarning');
        const existingUserCodes = @json($users->pluck('kode')->map(fn ($kode) => strtoupper(trim((string) $kode)))->values());
        const existingCashierCodes = @json($users->pluck('kode_kasir')->map(fn ($kode) => trim((string) $kode))->filter()->values());
        let activeFilter = 'all';

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

        if (addUserForm) {
            addUserForm.querySelectorAll('input, select').forEach((field) => {
                field.addEventListener('input', () => {
                    if (!addUserWarning) {
                        return;
                    }

                    addUserWarning.hidden = true;
                    addUserWarning.textContent = '';
                });
            });

            addUserForm.addEventListener('submit', (event) => {
                const kodeInput = addUserForm.querySelector('[name="kode"]');
                const cashierInput = addUserForm.querySelector('[name="kode_kasir"]');
                const kode = kodeInput ? kodeInput.value.trim().toUpperCase() : '';
                const kodeKasir = cashierInput ? cashierInput.value.trim() : '';

                if (kodeInput) {
                    kodeInput.value = kode;
                }

                if (cashierInput) {
                    cashierInput.value = kodeKasir;
                }

                const warnings = [];

                if (existingUserCodes.includes(kode)) {
                    warnings.push('User code already exists.');
                }

                if (existingCashierCodes.includes(kodeKasir)) {
                    warnings.push('Cashier code is already used by another user.');
                }

                if (warnings.length === 0) {
                    return;
                }

                event.preventDefault();

                if (addUserWarning) {
                    addUserWarning.hidden = false;
                    addUserWarning.textContent = warnings.join(' ');
                } else {
                    window.alert(warnings.join(' '));
                }
            });
        }

        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeFilter = button.dataset.permissionFilter || 'all';
                filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                syncRows();
            });
        });

        function closeModal(modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            if (modal.id === 'addUserModal' && addUserWarning) {
                addUserWarning.hidden = true;
                addUserWarning.textContent = '';
            }
        }

        function openModal(modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            if (modal.id === 'addUserModal' && addUserWarning) {
                addUserWarning.hidden = true;
                addUserWarning.textContent = '';
            }

            const firstInput = modal.querySelector('input:not([type="hidden"]), select, button');
            if (firstInput) {
                firstInput.focus();
            }
        }

        modalTriggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const modal = document.getElementById(trigger.dataset.modalTarget || '');
                if (modal) {
                    openModal(modal);
                }
            });
        });

        modals.forEach((modal) => {
            modal.querySelectorAll('[data-modal-close]').forEach((closeButton) => {
                closeButton.addEventListener('click', () => closeModal(modal));
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            modals.filter((modal) => modal.classList.contains('is-open')).forEach(closeModal);
        });
    }());
</script>
@endsection
