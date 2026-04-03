@extends('layouts.app')

@section('title', 'Stock Package')

@section('content')

@php
    $avgSellingPrice = $packages->avg('Hj') ?? 0;
    $process = $processResult ?? [
        'room_code' => '',
        'room_name' => 'Room',
        'room_amount' => 0,
        'restaurant_code' => '',
        'restaurant_name' => 'Restaurant',
        'restaurant_amount' => 0,
        'package_code' => old('PackageCode', ''),
        'expired' => old('Expired', now()->format('Y-m-d')),
        'formula' => old('Formula', 'GROUP'),
        'nofak' => '',
    ];
@endphp

<style>
    .content-wrapper {
        background:
            radial-gradient(circle at top right, rgba(183, 148, 92, 0.12), transparent 22%),
            radial-gradient(circle at left top, rgba(17, 24, 39, 0.08), transparent 28%),
            linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%);
        min-height: 100vh;
    }

    .content-wrapper > h3 {
        display: none;
    }

    .package-page {
        padding: 0 0 2rem;
        color: #10233b;
    }

    .package-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #10233b 0%, #19395f 55%, #b38a51 140%);
        border-radius: 24px;
        color: #fff;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 24px 60px rgba(16, 35, 59, 0.2);
    }

    .package-hero::after {
        content: '';
        position: absolute;
        top: -80px;
        right: -20px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
    }

    .package-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .package-hero h1 {
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1.1;
        margin: 0 0 0.75rem;
    }

    .package-hero p {
        max-width: 760px;
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
        font-size: 1rem;
    }

    .package-summary {
        margin-top: 1.5rem;
    }

    .package-stat {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 18px;
        padding: 1rem 1.1rem;
        backdrop-filter: blur(12px);
        min-height: 100%;
    }

    .package-stat-label {
        display: block;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 0.5rem;
    }

    .package-stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #fff;
    }

    .package-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .package-shell {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 18px 50px rgba(16, 35, 59, 0.1);
        backdrop-filter: blur(16px);
        border-radius: 24px;
        overflow: hidden;
    }

    .package-shell-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
    }

    .package-shell-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #10233b;
    }

    .package-shell-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.9rem;
        color: #5f6f84;
    }

    .package-shell-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        background: rgba(179, 138, 81, 0.12);
        color: #8b6232;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .package-shell-body {
        padding: 1.5rem;
    }

    .package-label {
        display: block;
        font-size: 0.84rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #5f6f84;
        margin-bottom: 0.55rem;
    }

    .package-input,
    .package-select {
        height: calc(2.6rem + 2px);
        border-radius: 14px;
        border: 1px solid rgba(16, 35, 59, 0.12);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        background: rgba(255, 255, 255, 0.92);
        color: #10233b;
        font-weight: 600;
    }

    .package-input:focus,
    .package-select:focus {
        border-color: rgba(179, 138, 81, 0.78);
        box-shadow: 0 0 0 0.2rem rgba(179, 138, 81, 0.14);
    }

    .package-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .package-btn-primary {
        border: 0;
        border-radius: 999px;
        padding: 0.75rem 1.4rem;
        font-weight: 700;
        background: linear-gradient(135deg, #173761 0%, #1e4b80 55%, #b38a51 150%);
        box-shadow: 0 12px 26px rgba(23, 55, 97, 0.2);
        color: #fff;
    }

    .package-btn-secondary {
        border-radius: 999px;
        padding: 0.75rem 1.3rem;
        font-weight: 700;
        border: 1px solid rgba(16, 35, 59, 0.12);
        background: rgba(255, 255, 255, 0.78);
        color: #173761;
    }

    .package-alert,
    .package-error {
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1.15rem;
        box-shadow: 0 14px 30px rgba(16, 35, 59, 0.08);
    }

    .package-alert {
        background: linear-gradient(135deg, rgba(33, 150, 83, 0.16), rgba(33, 150, 83, 0.08));
        color: #1c6b40;
    }

    .package-error {
        background: linear-gradient(135deg, rgba(179, 52, 70, 0.16), rgba(179, 52, 70, 0.08));
        color: #8f2435;
    }

    .formula-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    .formula-option {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.9rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(16, 35, 59, 0.1);
        background: rgba(255, 255, 255, 0.72);
        cursor: pointer;
    }

    .formula-option input {
        margin: 0;
    }

    .formula-option strong {
        display: block;
        color: #10233b;
        font-size: 0.92rem;
    }

    .formula-option span {
        display: block;
        color: #6b7b90;
        font-size: 0.78rem;
    }

    .package-preview {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .preview-card {
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(16, 35, 59, 0.04), rgba(16, 35, 59, 0.08));
        border: 1px solid rgba(16, 35, 59, 0.08);
        padding: 1rem;
    }

    .preview-card h4 {
        margin: 0 0 0.75rem;
        font-size: 0.96rem;
        font-weight: 700;
        color: #173761;
    }

    .preview-meta {
        display: grid;
        gap: 0.65rem;
    }

    .preview-field small {
        display: block;
        color: #6b7b90;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        margin-bottom: 0.25rem;
    }

    .preview-field strong {
        display: block;
        color: #10233b;
        font-size: 0.95rem;
        word-break: break-word;
    }

    .process-notes {
        margin: 1rem 0 0;
        padding-left: 1.1rem;
        color: #5f6f84;
    }

    .process-notes li + li {
        margin-top: 0.45rem;
    }

    .package-table-wrap {
        border-radius: 0 0 24px 24px;
        overflow: hidden;
    }

    .package-table {
        margin-bottom: 0;
    }

    .package-table thead th {
        border-top: 0;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
        background: linear-gradient(180deg, rgba(16, 35, 59, 0.02), rgba(16, 35, 59, 0.06));
        color: #53657d;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 1rem 1.2rem;
    }

    .package-table tbody tr {
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        cursor: pointer;
    }

    .package-table tbody tr:nth-child(odd) {
        background: rgba(16, 35, 59, 0.045);
    }

    .package-table tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.96);
    }

    .package-table tbody tr:hover {
        background: rgba(179, 138, 81, 0.06);
        transform: translateY(-1px);
        box-shadow: inset 4px 0 0 #b38a51;
    }

    .package-table tbody td {
        border-top: 1px solid rgba(16, 35, 59, 0.06);
        padding: 1rem 1.2rem;
        vertical-align: middle;
        color: #10233b;
    }

    .package-code {
        display: inline-flex;
        align-items: center;
        min-width: 88px;
        justify-content: center;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: rgba(23, 55, 97, 0.08);
        color: #173761;
        font-weight: 700;
        letter-spacing: 0.06em;
    }

    .kind-pill {
        display: inline-flex;
        padding: 0.42rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .kind-room {
        background: rgba(30, 140, 88, 0.12);
        color: #126b45;
    }

    .kind-restaurant {
        background: rgba(196, 134, 27, 0.14);
        color: #9b6616;
    }

    .package-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(178, 34, 34, 0.08);
        color: #aa2f2f;
        border: 1px solid rgba(178, 34, 34, 0.12);
        text-decoration: none;
        font-size: 1rem;
        transition: all 0.18s ease;
    }

    .package-delete:hover {
        background: #aa2f2f;
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .package-empty {
        text-align: center;
        padding: 2.2rem 1rem;
        color: #6b7b90;
    }

    @media (max-width: 1199.98px) {
        .package-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .formula-grid,
        .package-preview {
            grid-template-columns: 1fr;
        }

        .package-hero {
            padding: 1.5rem;
        }

        .package-hero h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container-fluid package-page">
    @if(session('success'))
    <div class="alert package-alert mb-4" id="successAlert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert package-error mb-4">
        {{ session('error') }}
    </div>
    @endif

    <section class="package-hero">
        <div class="package-kicker">
            <span>Quantum Hotel</span>
            <span>Stock Package</span>
        </div>
        <h1>Stock Package Master & Automation</h1>
        <p>Manage package inventory items and run the automatic package process that splits package value into room and restaurant components, then writes the result into StockPackage, Package, and PackageD.</p>

        <div class="row package-summary">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="package-stat">
                    <span class="package-stat-label">Total Items</span>
                    <span class="package-stat-value">{{ number_format($summary['total'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="package-stat">
                    <span class="package-stat-label">Room Items</span>
                    <span class="package-stat-value">{{ number_format($summary['room'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="package-stat">
                    <span class="package-stat-label">Average Selling Price</span>
                    <span class="package-stat-value">Rp {{ number_format($avgSellingPrice, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="package-grid">
        <div class="package-shell">
            <div class="package-shell-header">
                <div>
                    <h2 class="package-shell-title">Stock Package Input</h2>
                    <p class="package-shell-subtitle">Direct CRUD for package inventory items stored in the StockPackage table.</p>
                </div>
                <span class="package-shell-badge">Master CRUD</span>
            </div>
            <div class="package-shell-body">
                <form method="POST" action="/stock-package" id="formStockPackage">
                    @csrf
                    <div class="form-group">
                        <label class="package-label" for="KodeBrg">Item Code</label>
                        <input type="text" name="KodeBrg" id="KodeBrg" class="form-control package-input" required>
                    </div>

                    <div class="form-group">
                        <label class="package-label" for="NamaBrg">Item Name</label>
                        <input type="text" name="NamaBrg" id="NamaBrg" class="form-control package-input" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="package-label" for="Satuan">Unit</label>
                            <input type="text" name="Satuan" id="Satuan" class="form-control package-input" value="PAX">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="package-label" for="Kind">Category</label>
                            <select name="Kind" id="Kind" class="form-control package-select">
                                <option value="ROOM">ROOM</option>
                                <option value="RESTAURANT">RESTAURANT</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="package-label" for="Hj">Selling Price</label>
                        <input type="text" name="Hj" id="Hj" class="form-control package-input text-right" inputmode="numeric" required>
                    </div>

                    <div class="package-actions">
                        <button class="btn package-btn-primary" id="saveButton">Save Item</button>
                        <button type="button" class="btn package-btn-secondary" id="resetButton">Reset Form</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="package-shell">
            <div class="package-shell-header">
                <div>
                    <h2 class="package-shell-title">Automatic Package Process</h2>
                    <p class="package-shell-subtitle">Converted from the legacy VB6 formula flow to split package value and create the related package records automatically.</p>
                </div>
                <span class="package-shell-badge">VB6 Logic</span>
            </div>
            <div class="package-shell-body">
                <div class="package-preview">
                    <div class="preview-card">
                        <h4>Room Item Output</h4>
                        <div class="preview-meta">
                            <div class="preview-field">
                                <small>Room Item Code</small>
                                <strong id="previewRoomCode">{{ $process['room_code'] ?: '-' }}</strong>
                            </div>
                            <div class="preview-field">
                                <small>Item Name 1</small>
                                <strong id="previewRoomName">{{ $process['room_name'] ?: 'Room' }}</strong>
                            </div>
                            <div class="preview-field">
                                <small>Room Amount</small>
                                <strong id="previewRoomAmount">Rp {{ number_format($process['room_amount'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="preview-card">
                        <h4>Restaurant Item Output</h4>
                        <div class="preview-meta">
                            <div class="preview-field">
                                <small>Restaurant Item Code</small>
                                <strong id="previewRestaurantCode">{{ $process['restaurant_code'] ?: '-' }}</strong>
                            </div>
                            <div class="preview-field">
                                <small>Item Name 2</small>
                                <strong id="previewRestaurantName">{{ $process['restaurant_name'] ?: 'Restaurant' }}</strong>
                            </div>
                            <div class="preview-field">
                                <small>Restaurant Amount</small>
                                <strong id="previewRestaurantAmount">Rp {{ number_format($process['restaurant_amount'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="/stock-package/process" id="formProcessPackage">
                    @csrf

                    <label class="package-label">Purpose Of The Formula</label>
                    <div class="formula-grid">
                        <label class="formula-option">
                            <input type="radio" name="Formula" value="GROUP" {{ ($process['formula'] ?? 'GROUP') === 'GROUP' ? 'checked' : '' }}>
                            <div>
                                <strong>GROUP [60 - 40]</strong>
                                <span>Room 60% and Restaurant 40%</span>
                            </div>
                        </label>
                        <label class="formula-option">
                            <input type="radio" name="Formula" value="ROOM_ONLY" {{ ($process['formula'] ?? '') === 'ROOM_ONLY' ? 'checked' : '' }}>
                            <div>
                                <strong>ROOM ONLY [100 - 0]</strong>
                                <span>Full amount goes to room</span>
                            </div>
                        </label>
                        <label class="formula-option">
                            <input type="radio" name="Formula" value="OTA" {{ ($process['formula'] ?? '') === 'OTA' ? 'checked' : '' }}>
                            <div>
                                <strong>OTA [100K]</strong>
                                <span>Restaurant keeps 100,000</span>
                            </div>
                        </label>
                        <label class="formula-option">
                            <input type="radio" name="Formula" value="EXECUTIVE" {{ ($process['formula'] ?? '') === 'EXECUTIVE' ? 'checked' : '' }}>
                            <div>
                                <strong>EXECUTIVE [200K]</strong>
                                <span>Restaurant keeps 200,000</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="package-label" for="Nominal">Nominal Package</label>
                            <input type="text" name="Nominal" id="Nominal" class="form-control package-input text-right" inputmode="numeric" value="{{ old('Nominal') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="package-label" for="PackageCode">Package Code</label>
                            <input type="text" name="PackageCode" id="PackageCode" class="form-control package-input" value="{{ old('PackageCode', $process['package_code']) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="package-label" for="Expired">Expired</label>
                            <input type="date" name="Expired" id="Expired" class="form-control package-input" value="{{ old('Expired', $process['expired']) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="package-label">Generated Package Invoice</label>
                            <input type="text" class="form-control package-input" value="{{ $process['nofak'] ?: 'Generated after process' }}" readonly>
                        </div>
                    </div>

                    <div class="package-actions">
                        <button class="btn package-btn-primary" id="processButton">Process Package</button>
                    </div>
                </form>

                <ol class="process-notes">
                    <li>Default expired date is today and can be changed as needed.</li>
                    <li>Package code is auto-generated from nominal package and can still be edited manually.</li>
                    <li>Processing will reject a package when nominal and expired date already exist in the same combination.</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="package-shell">
        <div class="package-shell-header">
            <div>
                <h2 class="package-shell-title">Stock Package Directory</h2>
                <p class="package-shell-subtitle">Click a row to load the StockPackage item into the left CRUD form and continue in update mode.</p>
            </div>
            <span class="package-shell-badge">{{ number_format($summary['total'], 0, ',', '.') }} Records</span>
        </div>

        <div class="package-table-wrap">
            <div class="table-responsive">
                <table class="table package-table" id="tableStockPackage">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th>Category</th>
                            <th class="text-right">Selling Price</th>
                            <th class="text-center" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $item)
                        <tr data-kode="{{ $item->KodeBrg }}"
                            data-nama="{{ $item->NamaBrg }}"
                            data-satuan="{{ $item->Satuan }}"
                            data-kind="{{ $item->Kind }}"
                            data-hj="{{ $item->Hj }}">
                            <td><span class="package-code">{{ $item->KodeBrg }}</span></td>
                            <td>{{ $item->NamaBrg }}</td>
                            <td>{{ $item->Satuan }}</td>
                            <td>
                                <span class="kind-pill {{ strtoupper(trim($item->Kind)) === 'RESTAURANT' ? 'kind-restaurant' : 'kind-room' }}">
                                    {{ $item->Kind }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format($item->Hj ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="/stock-package/{{ $item->KodeBrg }}/delete" class="package-delete" title="Delete" aria-label="Delete" data-confirm-delete="Are you sure you want to delete this stock package item?">&#128465;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="package-empty">No stock package items yet. Create the first one or run the automatic package process.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
function normalizeNumber(value) {
    if (value === null || value === undefined) {
        return '';
    }

    const raw = value.toString().trim();

    if (raw === '') {
        return '';
    }

    if (raw.includes('.')) {
        const [integerPart, decimalPart = ''] = raw.split('.');
        const cleanInteger = integerPart.replace(/\D/g, '');

        if (/^0+$/.test(decimalPart)) {
            return cleanInteger;
        }
    }

    return raw.replace(/\D/g, '');
}

function formatRibuan(value) {
    const normalized = normalizeNumber(value);

    if (!normalized) {
        return '';
    }

    return normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function unformat(value) {
    return (value || '').toString().replace(/\./g, '');
}

function normalizeCode(value) {
    return (value || '').toString().trim().toUpperCase();
}

function defaultPackageCodeFromNominal() {
    const nominal = formatRibuan(nominalField.value);

    if (!nominal) {
        return '';
    }

    return 'P- ' + nominal;
}

const formStockPackage = document.getElementById('formStockPackage');
const kodeBrgField = document.getElementById('KodeBrg');
const namaBrgField = document.getElementById('NamaBrg');
const satuanField = document.getElementById('Satuan');
const kindField = document.getElementById('Kind');
const hjField = document.getElementById('Hj');
const saveButton = document.getElementById('saveButton');
const resetButton = document.getElementById('resetButton');
const stockRows = Array.from(document.querySelectorAll('#tableStockPackage tbody tr[data-kode]'));

const nominalField = document.getElementById('Nominal');
const packageCodeField = document.getElementById('PackageCode');
const processButton = document.getElementById('processButton');

function findExistingStockRowByCode(kode) {
    const normalizedCode = normalizeCode(kode);
    return stockRows.find((row) => normalizeCode(row.dataset.kode) === normalizedCode) || null;
}

function loadStockRowIntoForm(row) {
    kodeBrgField.value = row.dataset.kode;
    namaBrgField.value = row.dataset.nama;
    satuanField.value = row.dataset.satuan || 'PAX';
    kindField.value = normalizeCode(row.dataset.kind || 'ROOM');
    hjField.value = formatRibuan(row.dataset.hj || '');
    kodeBrgField.readOnly = true;
    formStockPackage.action = '/stock-package/' + row.dataset.kode + '/update';
    saveButton.textContent = 'Update Item';
    resetButton.textContent = 'Cancel Edit';
    namaBrgField.focus();
}

hjField.addEventListener('input', function () {
    this.value = formatRibuan(this.value.replace(/\D/g, ''));
});

nominalField.addEventListener('input', function () {
    this.value = formatRibuan(this.value.replace(/\D/g, ''));

    if (!packageCodeField.dataset.manual || packageCodeField.dataset.manual === '0') {
        packageCodeField.value = defaultPackageCodeFromNominal();
    }
});

packageCodeField.addEventListener('input', function () {
    this.dataset.manual = this.value.trim() !== '' ? '1' : '0';
});

[kodeBrgField, namaBrgField, satuanField, hjField].forEach((field, index, allFields) => {
    field.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        if (field === kodeBrgField) {
            const existingRow = findExistingStockRowByCode(kodeBrgField.value);

            if (existingRow) {
                loadStockRowIntoForm(existingRow);
                return;
            }

            kodeBrgField.value = normalizeCode(kodeBrgField.value);
        }

        if (index < allFields.length - 1) {
            allFields[index + 1].focus();
            if (typeof allFields[index + 1].select === 'function') {
                allFields[index + 1].select();
            }
            return;
        }

        formStockPackage.requestSubmit();
    });
});

document.querySelector('#tableStockPackage tbody').addEventListener('click', function (event) {
    if (event.target.closest('a')) {
        return;
    }

    const row = event.target.closest('tr');
    if (!row || !row.dataset.kode) {
        return;
    }

    loadStockRowIntoForm(row);
});

resetButton.addEventListener('click', function () {
    formStockPackage.reset();
    kodeBrgField.readOnly = false;
    formStockPackage.action = '/stock-package';
    saveButton.textContent = 'Save Item';
    resetButton.textContent = 'Reset Form';
    satuanField.value = 'PAX';
    kindField.value = 'ROOM';
    kodeBrgField.focus();
});

formStockPackage.addEventListener('submit', function () {
    kodeBrgField.value = normalizeCode(kodeBrgField.value);
    namaBrgField.value = normalizeCode(namaBrgField.value);
    satuanField.value = normalizeCode(satuanField.value);
    kindField.value = normalizeCode(kindField.value);
    hjField.value = unformat(hjField.value);
});

if (packageCodeField.value.trim() !== '') {
    packageCodeField.dataset.manual = '1';
} else {
    packageCodeField.value = defaultPackageCodeFromNominal();
    packageCodeField.dataset.manual = packageCodeField.value.trim() !== '' ? '0' : '0';
}

if (nominalField.value) {
    nominalField.value = formatRibuan(nominalField.value);
}

processButton.addEventListener('click', function () {
    nominalField.value = unformat(nominalField.value);
    packageCodeField.value = packageCodeField.value.trim().toUpperCase();
});

const successAlert = document.getElementById('successAlert');
if (successAlert) {
    setTimeout(() => {
        successAlert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        successAlert.style.opacity = '0';
        successAlert.style.transform = 'translateY(-8px)';
        setTimeout(() => successAlert.remove(), 300);
    }, 3000);
}

kodeBrgField.focus();
</script>

@endsection
