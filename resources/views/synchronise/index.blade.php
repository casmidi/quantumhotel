@extends('layouts.app')

@section('title', 'Synchronise')

@section('content')
@php
    $results = session('sync_results', []);
    $formatCount = function ($value) {
        return is_null($value) ? '-' : number_format((int) $value);
    };
@endphp

<style>
    .sync-shell {
        display: grid;
        gap: 1rem;
    }

    .sync-panel {
        border: 1px solid rgba(17, 24, 39, 0.1);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
    }

    .sync-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid rgba(17, 24, 39, 0.08);
    }

    .sync-panel-title {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin: 0;
        color: #182230;
        font-size: 1rem;
        font-weight: 800;
    }

    .sync-panel-title i {
        color: #b98b2d;
    }

    .sync-panel-body {
        padding: 1rem;
    }

    .sync-route-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 0.85rem;
        align-items: stretch;
    }

    .sync-endpoint {
        min-width: 0;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 8px;
        background: linear-gradient(180deg, #fbfcfd 0%, #f5f7fa 100%);
        padding: 0.85rem;
    }

    .sync-endpoint-label {
        margin-bottom: 0.55rem;
        color: #667085;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .sync-endpoint-name {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.45rem;
        color: #101828;
        font-weight: 800;
    }

    .sync-meta {
        display: grid;
        gap: 0.25rem;
        color: #475467;
        font-size: 0.88rem;
    }

    .sync-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        border: 1px solid rgba(185, 139, 45, 0.28);
        border-radius: 8px;
        background: rgba(185, 139, 45, 0.08);
        color: #9a6c1d;
        font-size: 1.15rem;
    }

    .sync-options-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .sync-option {
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 8px;
        padding: 0.85rem;
        background: #fbfcfd;
    }

    .sync-option label {
        margin-bottom: 0.45rem;
        color: #344054;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .sync-table-list {
        display: grid;
        gap: 0.6rem;
    }

    .sync-table-row {
        display: grid;
        grid-template-columns: minmax(220px, 1.2fr) minmax(160px, 0.7fr) minmax(110px, 0.45fr) minmax(110px, 0.45fr);
        gap: 0.75rem;
        align-items: center;
        padding: 0.78rem 0.85rem;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 8px;
        background: #fff;
    }

    .sync-table-row.is-head {
        background: #f6f7f9;
        color: #475467;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sync-table-name {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        min-width: 0;
    }

    .sync-table-name strong,
    .sync-table-name span {
        display: block;
    }

    .sync-table-name strong {
        color: #101828;
        line-height: 1.2;
    }

    .sync-table-name span {
        margin-top: 0.18rem;
        color: #667085;
        font-size: 0.82rem;
        line-height: 1.25;
    }

    .sync-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        min-height: 28px;
        padding: 0.22rem 0.55rem;
        border-radius: 6px;
        background: #eef2f6;
        color: #344054;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .sync-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .sync-wizard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1rem;
    }

    .sync-wizard-page {
        border: 1px solid #b8b8b8;
        border-radius: 3px;
        background: #f2f2f2;
        color: #111;
        overflow: hidden;
    }

    .sync-wizard-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.8rem 0.9rem;
        border-bottom: 1px solid #c8c8c8;
        background: #fff;
    }

    .sync-wizard-heading strong {
        display: block;
        font-size: 0.98rem;
        line-height: 1.2;
    }

    .sync-wizard-heading span {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.82rem;
    }

    .sync-wizard-icon {
        color: #c7a345;
        font-size: 1.9rem;
    }

    .sync-wizard-body {
        display: grid;
        gap: 0.55rem;
        padding: 0.95rem;
    }

    .sync-wizard-row {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 0.65rem;
        align-items: center;
    }

    .sync-wizard-row label {
        margin: 0;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .sync-wizard-control {
        min-height: 30px;
        border: 1px solid #999;
        border-radius: 0;
        background: #fff;
        color: #111;
        font-size: 0.86rem;
    }

    .sync-auth-box {
        display: grid;
        gap: 0.45rem;
        padding: 0.55rem 0.7rem;
        border: 1px solid #b7b7b7;
        background: #f6f6f6;
    }

    .sync-auth-box legend {
        width: auto;
        margin: 0;
        padding: 0 0.25rem;
        color: #111;
        font-size: 0.82rem;
    }

    .sync-results {
        display: grid;
        gap: 0.55rem;
    }

    .sync-result-row {
        display: grid;
        grid-template-columns: 130px repeat(4, minmax(80px, 1fr)) minmax(180px, 1.2fr);
        gap: 0.55rem;
        align-items: center;
        padding: 0.65rem 0.75rem;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 8px;
        background: #fff;
        font-size: 0.88rem;
    }

    .sync-result-row.is-head {
        background: #f6f7f9;
        color: #475467;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    @media (max-width: 991.98px) {
        .sync-route-grid,
        .sync-wizard-grid,
        .sync-options-grid,
        .sync-table-row,
        .sync-result-row {
            grid-template-columns: 1fr;
        }

        .sync-arrow {
            width: 100%;
            min-height: 42px;
        }

        .sync-wizard-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="sync-shell">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="sync-wizard-grid">
        <div class="sync-wizard-page">
            <div class="sync-wizard-heading">
                <span>
                    <strong>Choose a Data Source</strong>
                    <span>Select the source from which to copy data.</span>
                </span>
                <i class="fas fa-database sync-wizard-icon"></i>
            </div>
            <div class="sync-wizard-body">
                <div class="sync-wizard-row">
                    <label>Data source:</label>
                    <input class="form-control sync-wizard-control" value="{{ $source['driver'] }}" readonly>
                </div>
                <div class="sync-wizard-row">
                    <label>Server name:</label>
                    <input class="form-control sync-wizard-control" value="{{ $source['host'] }}{{ $source['port'] ? ',' . $source['port'] : '' }}" readonly>
                </div>
                <fieldset class="sync-auth-box">
                    <legend>Authentication</legend>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" checked disabled>
                        <label class="custom-control-label">{{ $source['authentication'] }}</label>
                    </div>
                    <div class="sync-wizard-row">
                        <label>User name:</label>
                        <input class="form-control sync-wizard-control" value="{{ $source['username'] }}" readonly>
                    </div>
                    <div class="sync-wizard-row">
                        <label>Password:</label>
                        <input class="form-control sync-wizard-control" value="********" readonly>
                    </div>
                </fieldset>
                <div class="sync-wizard-row">
                    <label>Database:</label>
                    <input class="form-control sync-wizard-control" value="{{ $source['database'] }}" readonly>
                </div>
            </div>
        </div>

        <div class="sync-wizard-page">
            <div class="sync-wizard-heading">
                <span>
                    <strong>Choose a Destination</strong>
                    <span>Specify where to copy data to.</span>
                </span>
                <i class="fas fa-laptop-code sync-wizard-icon"></i>
            </div>
            <div class="sync-wizard-body">
                <div class="sync-wizard-row">
                    <label>Destination:</label>
                    <input class="form-control sync-wizard-control" value="{{ $destination['driver'] }}" readonly>
                </div>
                <div class="sync-wizard-row">
                    <label>Server name:</label>
                    <input class="form-control sync-wizard-control" value="{{ $destination['host'] }}{{ $destination['port'] ? ',' . $destination['port'] : '' }}" readonly>
                </div>
                <fieldset class="sync-auth-box">
                    <legend>Authentication</legend>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" checked disabled>
                        <label class="custom-control-label">{{ $destination['authentication'] }}</label>
                    </div>
                    <div class="sync-wizard-row">
                        <label>User name:</label>
                        <input class="form-control sync-wizard-control" value="{{ $destination['username'] }}" readonly>
                    </div>
                    <div class="sync-wizard-row">
                        <label>Password:</label>
                        <input class="form-control sync-wizard-control" value="********" readonly>
                    </div>
                </fieldset>
                <div class="sync-wizard-row">
                    <label>Database:</label>
                    <input class="form-control sync-wizard-control" value="{{ $destination['database'] }}" readonly>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($results))
        <div class="sync-panel">
            <div class="sync-panel-header">
                <h4 class="sync-panel-title">
                    <i class="fas fa-clipboard-check"></i>
                    Import Result
                </h4>
            </div>
            <div class="sync-panel-body">
                <div class="sync-results">
                    <div class="sync-result-row is-head">
                        <span>Table</span>
                        <span>Source</span>
                        <span>Local</span>
                        <span>Insert</span>
                        <span>Update</span>
                        <span>Status</span>
                    </div>
                    @foreach ($results as $result)
                        <div class="sync-result-row">
                            <strong>{{ $result['table'] }}</strong>
                            <span>{{ $formatCount($result['source_count']) }}</span>
                            <span>{{ $formatCount($result['destination_count']) }}</span>
                            <span>{{ $formatCount($result['inserted']) }}</span>
                            <span>{{ $formatCount($result['updated']) }}</span>
                            <span>
                                <span class="sync-badge">{{ ucfirst($result['status']) }}</span>
                                <small class="d-block text-muted mt-1">{{ $result['message'] }}</small>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('synchronise.run') }}" class="sync-panel" id="syncForm">
        @csrf

        <div class="sync-panel-header">
            <h4 class="sync-panel-title">
                <i class="fas fa-file-import"></i>
                Import Data
            </h4>
            <span class="sync-badge">Source Data to Local</span>
        </div>

        <div class="sync-panel-body">
            <div class="sync-options-grid mb-3">
                <div class="sync-option">
                    <label for="mode">Mode</label>
                    <select name="mode" id="mode" class="form-control">
                        <option value="upsert" selected>Update existing and insert new rows</option>
                        <option value="replace">Replace local table from source</option>
                    </select>
                </div>

                <div class="sync-option">
                    <label for="batch_size">Batch size</label>
                    <input type="number" name="batch_size" id="batch_size" class="form-control" value="500" min="50" max="5000" step="50">
                </div>

                <div class="sync-option">
                    <label>Execution</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="dry_run" value="1" class="custom-control-input" id="dry_run">
                        <label class="custom-control-label" for="dry_run">Preview only</label>
                    </div>
                    <div class="custom-control custom-checkbox mt-2" id="replaceConfirmWrap" style="display: none;">
                        <input type="checkbox" name="confirm_replace" value="1" class="custom-control-input" id="confirm_replace">
                        <label class="custom-control-label" for="confirm_replace">Confirm replace</label>
                    </div>
                </div>
            </div>

            <div class="sync-table-list">
                <div class="sync-table-row is-head">
                    <span>Table</span>
                    <span>Primary Key</span>
                    <span>Source Data</span>
                    <span>Local</span>
                </div>

                @foreach ($tables as $key => $table)
                    @continue($key === 'RES2D')
                    <label class="sync-table-row" for="table_{{ $key }}">
                        <span class="sync-table-name">
                            <input type="checkbox" name="tables[]" value="{{ $key }}" id="table_{{ $key }}" checked>
                            <span>
                                <strong>{{ $table['table'] }}</strong>
                                <span>{{ $table['label'] }}. {{ $table['description'] }}</span>
                            </span>
                        </span>
                        <span><span class="sync-badge">{{ implode(' + ', $table['keys']) }}</span></span>
                        <span>{{ $inspection ? $formatCount($inspection[$key]['source_count']) : '-' }}</span>
                        <span>{{ $inspection ? $formatCount($inspection[$key]['destination_count']) : '-' }}</span>
                    </label>
                @endforeach
            </div>

            <div class="sync-actions">
                <a href="{{ route('synchronise.index', ['inspect' => 1]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-table"></i>
                    Refresh Counts
                </a>
                <button type="submit" class="btn btn-outline-primary" onclick="document.getElementById('dry_run').checked = true;">
                    <i class="fas fa-eye"></i>
                    Preview
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play"></i>
                    Run Import
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    (function() {
        var mode = document.getElementById('mode');
        var confirmWrap = document.getElementById('replaceConfirmWrap');

        function syncModeState() {
            confirmWrap.style.display = mode.value === 'replace' ? 'block' : 'none';
        }

        mode.addEventListener('change', syncModeState);
        syncModeState();
    }());
</script>
@endsection
