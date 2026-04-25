@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="rcr-topbar-brand">
        <div class="rcr-topbar-title">Reception Customer Recaptulation</div>
        <div class="rcr-topbar-subtitle">PB1 final posting, fiscal recap, and department revenue control.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="rcr-topbar-tools">
        <div class="rcr-topbar-pill">
            <small>Period</small>
            <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
        </div>
        <div class="rcr-topbar-pill">
            <small>Source</small>
            <strong>{{ $summary['source'] }}</strong>
        </div>
        <a href="{{ $printUrl }}" target="_blank" class="btn package-btn-secondary rcr-print-link">
            <i class="fas fa-print mr-1"></i> Print
        </a>
    </div>
@endsection

@section('content')
@include('partials.crud-package-theme')

@php
    $money = fn ($value) => number_format((float) $value, 2, '.', ',');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $dateValue = fn ($value) => \Carbon\Carbon::parse($value)->format('Y-m-d');
    $activeTab = in_array(request('active_tab'), ['summary', 'detail'], true) ? request('active_tab') : 'summary';
    $sortUrl = function (string $grid, string $sort) use ($summarySort, $summaryDir, $detailSort, $detailDir) {
        $query = request()->query();
        $query['active_tab'] = $grid;

        if ($grid === 'summary') {
            $query['summary_sort'] = $sort;
            $query['summary_dir'] = $summarySort === $sort && $summaryDir === 'asc' ? 'desc' : 'asc';
            unset($query['summary_page']);
        } else {
            $query['detail_sort'] = $sort;
            $query['detail_dir'] = $detailSort === $sort && $detailDir === 'asc' ? 'desc' : 'asc';
            unset($query['page']);
        }

        return request()->url() . '?' . http_build_query($query);
    };
    $sortMark = function (string $grid, string $sort) use ($summarySort, $summaryDir, $detailSort, $detailDir) {
        $currentSort = $grid === 'summary' ? $summarySort : $detailSort;
        $currentDir = $grid === 'summary' ? $summaryDir : $detailDir;

        return $currentSort === $sort ? ($currentDir === 'asc' ? ' ↑' : ' ↓') : '';
    };
@endphp

<style>
    .rcr-topbar-brand {
        display: grid;
        gap: 0.22rem;
    }

    .rcr-topbar-title {
        color: var(--package-title, #173761);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 2rem;
        line-height: 1;
        font-weight: 600;
    }

    .rcr-topbar-subtitle {
        color: var(--package-muted, #516783);
        font-size: 0.93rem;
        font-weight: 650;
    }

    .rcr-topbar-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .rcr-topbar-pill {
        min-width: 170px;
        padding: 0.75rem 0.9rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 28px rgba(16, 35, 59, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .rcr-topbar-pill small,
    .rcr-label,
    .rcr-stat small {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--package-label);
    }

    .rcr-topbar-pill strong {
        display: block;
        color: var(--package-title);
        font-size: 1rem;
        line-height: 1.1;
    }

    .rcr-page {
        display: grid;
        gap: 1rem;
        padding: 0 0 2rem;
        color: var(--package-text);
    }

    .rcr-filter-band,
    .rcr-table-band,
    .rcr-alert {
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        overflow: hidden;
    }

    .rcr-alert {
        padding: 0.9rem 1rem;
        color: #7c2d12;
        background: #fff7ed;
        border-color: #fed7aa;
        font-weight: 800;
    }

    .rcr-alert.critical {
        color: #8a1f1f;
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .rcr-filter-band {
        padding: 1rem;
    }

    .rcr-filter {
        display: grid;
        grid-template-columns: 170px 170px 180px minmax(220px, 1fr) 130px auto auto;
        gap: 0.75rem;
        align-items: end;
    }

    .rcr-field {
        display: grid;
        gap: 0.4rem;
    }

    .rcr-input,
    .rcr-select {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid var(--package-input-border);
        background: var(--package-input-bg);
        color: var(--package-text);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        font-weight: 700;
    }

    .rcr-input:focus,
    .rcr-select:focus {
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
    }

    .rcr-date-control {
        position: relative;
        display: flex;
        align-items: center;
    }

    .rcr-date-control .rcr-input {
        width: 100%;
        padding-right: 2.65rem;
    }

    .rcr-calendar-button {
        position: absolute;
        right: 0.35rem;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--package-muted);
    }

    .rcr-calendar-button:hover,
    .rcr-calendar-button:focus {
        color: var(--package-title);
        background: rgba(23, 55, 97, 0.08);
        outline: 0;
    }

    .rcr-stats {
        display: grid;
        grid-template-columns: repeat(8, minmax(110px, 1fr));
        gap: 0.5rem;
    }

    .rcr-stat {
        min-height: 78px;
        padding: 0.72rem 0.8rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 26px rgba(16, 35, 59, 0.05);
        min-width: 0;
    }

    .rcr-stat strong {
        display: block;
        margin-top: 0.3rem;
        color: var(--package-title);
        font-size: clamp(0.86rem, 0.95vw, 1.02rem);
        line-height: 1.15;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: clip;
    }

    .rcr-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 1rem 1.15rem;
        background: var(--package-header-bg);
        border-bottom: 1px solid var(--package-shell-border);
    }

    .rcr-table-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.05rem;
        font-weight: 900;
    }

    .rcr-table-head span {
        color: var(--package-muted);
        font-size: 0.86rem;
        font-weight: 750;
    }

    .rcr-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .rcr-tabs {
        display: flex;
        gap: 0.5rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--package-shell-border);
        background: #f8fafc;
    }

    .rcr-tab-button {
        min-width: 110px;
        min-height: 34px;
        border: 1px solid #b8c9dc;
        border-radius: 8px;
        background: #fff;
        color: #173761;
        font-weight: 900;
        cursor: pointer;
    }

    .rcr-tab-button.is-active {
        background: #173761;
        border-color: #173761;
        color: #fff;
    }

    .rcr-tab-panel {
        display: none;
    }

    .rcr-tab-panel.is-active {
        display: block;
    }

    .rcr-table {
        min-width: 1320px;
        margin: 0;
        border-collapse: collapse;
        font-family: "Times New Roman", Times, serif;
    }

    .rcr-table thead th {
        display: table-cell;
        padding: 0.35rem 0.35rem;
        background: #fff;
        color: #000;
        text-transform: none;
        letter-spacing: 0;
        font-size: 0.82rem;
        font-weight: 700;
        border: 1px solid #d9d9d9;
        white-space: nowrap;
    }

    .rcr-sort-link {
        color: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
        width: 100%;
        text-decoration: none;
    }

    .rcr-sort-link:hover {
        color: #173761;
        text-decoration: underline;
    }

    .rcr-table tbody td {
        padding: 0.32rem 0.35rem;
        vertical-align: middle;
        border: 1px solid #e3e3e3;
        color: #000;
        font-size: 0.82rem;
    }

    .rcr-table tbody tr {
        background: #fff;
    }

    .rcr-table tbody tr.rcr-subtotal-row td {
        background: #f8fafc;
        color: #000080;
        font-weight: 900;
        border-top: 2px solid #b8c9dc;
    }

    .rcr-table tbody tr.rcr-breakdown-row {
        display: none;
    }

    .rcr-table tbody tr.rcr-breakdown-row.is-open {
        display: table-row;
    }

    .rcr-breakdown-cell {
        background: #f8fafc;
        padding: 0.65rem !important;
    }

    .rcr-breakdown-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        margin-right: 0.4rem;
        border: 1px solid #173761;
        border-radius: 6px;
        background: #fff;
        color: #173761;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
    }

    .rcr-row-count {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        margin-left: 0.55rem;
        padding: 0.15rem 0.45rem;
        border: 1px solid #b8c9dc;
        border-radius: 6px;
        background: #f8fafc;
        color: #173761;
        font-family: Arial, sans-serif;
        font-size: 0.72rem;
        font-weight: 900;
        vertical-align: middle;
    }

    .rcr-summary-table {
        min-width: 1180px;
    }

    .rcr-table tfoot td {
        background: #fff;
        color: #000080;
        font-weight: 900;
        border: 1px solid #d9d9d9;
        border-top: 2px solid #000080;
        padding: 0.35rem;
        font-size: 0.82rem;
    }

    .rcr-guest {
        max-width: 280px;
        overflow: hidden;
        text-overflow: clip;
        white-space: nowrap;
    }

    .rcr-guest strong {
        color: #000;
        font-weight: 400;
    }

    .rcr-money,
    .rcr-number {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .rcr-nowrap {
        white-space: nowrap;
    }

    .rcr-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0.32rem 0.52rem;
        border-radius: 999px;
        background: #e8f7f1;
        color: #17624a;
        font-size: 0.72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .rcr-badge.warning {
        background: #fff1f2;
        color: #9f1f1f;
    }

    .rcr-empty {
        padding: 1.8rem;
        color: var(--package-muted);
        font-weight: 800;
        text-align: center;
    }

    .rcr-pagination {
        padding: 1rem;
        border-top: 1px solid var(--package-shell-border);
        background: var(--package-shell-bg);
    }

    .rcr-pagination svg {
        width: 1rem;
        height: 1rem;
    }

    @media (max-width: 1500px) {
        .rcr-stats {
            grid-template-columns: repeat(4, minmax(125px, 1fr));
        }

        .rcr-filter {
            grid-template-columns: repeat(3, minmax(150px, 1fr));
        }
    }

    @media (max-width: 780px) {
        .rcr-topbar-title {
            font-size: 1.55rem;
        }

        .rcr-topbar-tools,
        .rcr-table-head {
            justify-content: flex-start;
        }

        .rcr-topbar-pill,
        .rcr-print-link {
            width: 100%;
        }

        .rcr-filter,
        .rcr-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="rcr-page">
    @if(!$schemaReady)
        <div class="rcr-alert critical">
            PB1 table is not ready. Missing columns: {{ implode(', ', $missingColumns) }}.
        </div>
    @endif

    @if($periodWarning)
        <div class="rcr-alert">{{ $periodWarning }}</div>
    @endif

    @if($summary['variance_count'] > 0 || abs((float) $summary['variance_total']) > 0.05)
        <div class="rcr-alert critical">
            Ditemukan {{ $number($summary['variance_count']) }} baris dengan selisih DPP + Tax + Service terhadap Total.
            Total variance: Rp {{ $money($summary['variance_total']) }}.
        </div>
    @endif

    <section class="rcr-filter-band">
        <form method="GET" action="/reception-customer-recaptulation" class="rcr-filter">
            <label class="rcr-field">
                <span class="rcr-label">Start Date</span>
                <span class="rcr-date-control">
                    <input type="date" name="start_date" value="{{ $dateValue($startDate) }}" class="form-control rcr-input" id="rcr-start-date">
                    <button type="button" class="rcr-calendar-button" data-date-target="rcr-start-date" title="Select date" aria-label="Select start date">
                        <i class="fas fa-calendar-alt"></i>
                    </button>
                </span>
            </label>
            <label class="rcr-field">
                <span class="rcr-label">End Date</span>
                <span class="rcr-date-control">
                    <input type="date" name="end_date" value="{{ $dateValue($endDate) }}" class="form-control rcr-input" id="rcr-end-date">
                    <button type="button" class="rcr-calendar-button" data-date-target="rcr-end-date" title="Select date" aria-label="Select end date">
                        <i class="fas fa-calendar-alt"></i>
                    </button>
                </span>
            </label>
            <label class="rcr-field">
                <span class="rcr-label">RegNo</span>
                <input type="text" name="regno" value="{{ $regno }}" class="form-control rcr-input" placeholder="All">
            </label>
            <label class="rcr-field">
                <span class="rcr-label">Search</span>
                <input type="text" name="search" value="{{ $search }}" class="form-control rcr-input" placeholder="Invoice, room, guest">
            </label>
            <label class="rcr-field">
                <span class="rcr-label">Rows</span>
                <select name="per_page" class="form-control rcr-select">
                    @foreach([25, 50, 100, 150, 300] as $option)
                        <option value="{{ $option }}" {{ (int) $perPage === $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn package-btn-primary">
                <i class="fas fa-search mr-1"></i> Process
            </button>
            <a href="/reception-customer-recaptulation" class="btn package-btn-secondary">
                <i class="fas fa-sync-alt mr-1"></i> Reset
            </a>
        </form>
    </section>

    <section class="rcr-stats">
        <div class="rcr-stat"><small>Rows</small><strong>{{ $number($summary['total_rows']) }}</strong></div>
        <div class="rcr-stat"><small>Invoices</small><strong>{{ $number($summary['invoices']) }}</strong></div>
        <div class="rcr-stat"><small>Room</small><strong>Rp {{ $money($summary['totals']['room']) }}</strong></div>
        <div class="rcr-stat"><small>F&B + Meeting</small><strong>Rp {{ $money($summary['totals']['cafe']) }}</strong></div>
        <div class="rcr-stat"><small>Other</small><strong>Rp {{ $money($summary['totals']['other']) }}</strong></div>
        <div class="rcr-stat"><small>DPP</small><strong>Rp {{ $money($summary['totals']['dpp']) }}</strong></div>
        <div class="rcr-stat"><small>Tax</small><strong>Rp {{ $money($summary['totals']['tax']) }}</strong></div>
        <div class="rcr-stat"><small>Total</small><strong>Rp {{ $money($summary['totals']['total']) }}</strong></div>
    </section>

    <section class="rcr-table-band">
        <div class="rcr-table-head">
            <div>
                <h3>RECEPTIONIST CUSTOMER RECAPTULATION</h3>
                <span>
                    Period : {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} Until : {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                    @if($postingStatus)
                        / Posting {{ $postingStatus->period_code }}: {{ $postingStatus->is_posted ? 'Posted' : 'Review' }}
                    @endif
                    @if($auditBatch)
                        / Audit: {{ $auditBatch->audit_no ?? '-' }}
                    @endif
                </span>
            </div>
            <span>{{ $summary['mode'] }} / {{ $summary['source'] }}</span>
        </div>

        <div class="rcr-tabs" role="tablist" aria-label="RCR views">
            <button type="button" class="rcr-tab-button {{ $activeTab === 'summary' ? 'is-active' : '' }}" data-rcr-tab="summary">Summary</button>
            <button type="button" class="rcr-tab-button {{ $activeTab === 'detail' ? 'is-active' : '' }}" data-rcr-tab="detail">Detail</button>
        </div>

        <div class="rcr-tab-panel {{ $activeTab === 'summary' ? 'is-active' : '' }}" data-rcr-panel="summary">
            <div class="rcr-table-wrap">
                <table class="table rcr-table rcr-summary-table">
                    <thead>
                        <tr>
                            <th><a href="{{ $sortUrl('summary', 'regno') }}" class="rcr-sort-link" data-rcr-grid-link>RegNo{{ $sortMark('summary', 'regno') }}</a></th>
                            <th><a href="{{ $sortUrl('summary', 'guest') }}" class="rcr-sort-link" data-rcr-grid-link>Guest{{ $sortMark('summary', 'guest') }}</a></th>
                            <th class="rcr-number"><a href="{{ $sortUrl('summary', 'rows') }}" class="rcr-sort-link" data-rcr-grid-link>Rows{{ $sortMark('summary', 'rows') }}</a></th>
                            <th class="rcr-number"><a href="{{ $sortUrl('summary', 'pax') }}" class="rcr-sort-link" data-rcr-grid-link>Pax{{ $sortMark('summary', 'pax') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'room') }}" class="rcr-sort-link" data-rcr-grid-link>ROOM{{ $sortMark('summary', 'room') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'rest') }}" class="rcr-sort-link" data-rcr-grid-link>REST{{ $sortMark('summary', 'rest') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'other') }}" class="rcr-sort-link" data-rcr-grid-link>Other{{ $sortMark('summary', 'other') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'vila') }}" class="rcr-sort-link" data-rcr-grid-link>VILA{{ $sortMark('summary', 'vila') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'dpp') }}" class="rcr-sort-link" data-rcr-grid-link>DPP{{ $sortMark('summary', 'dpp') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'service') }}" class="rcr-sort-link" data-rcr-grid-link>Service{{ $sortMark('summary', 'service') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'tax') }}" class="rcr-sort-link" data-rcr-grid-link>Tax{{ $sortMark('summary', 'tax') }}</a></th>
                            <th class="rcr-money"><a href="{{ $sortUrl('summary', 'total') }}" class="rcr-sort-link" data-rcr-grid-link>Total{{ $sortMark('summary', 'total') }}</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $summaryRegnoGroups = collect(method_exists($summaryDirectory, 'items') ? $summaryDirectory->items() : $summaryDirectory);
                        @endphp
                        @if($summaryRegnoGroups->isEmpty())
                            <tr>
                                <td colspan="12" class="rcr-empty">No RCR summary is available for this period.</td>
                            </tr>
                        @else
                            @foreach($summaryRegnoGroups as $summaryGroup)
                                @php
                                    $groupRegno = $summaryGroup->regno;
                                    $groupRows = $summaryGroup->rows;
                                    $breakdownId = 'rcr-breakdown-' . $summaryDirectory->currentPage() . '-' . $loop->iteration;
                                    $firstRow = $groupRows->first();
                                @endphp
                                <tr>
                                    <td class="rcr-nowrap">
                                        <button type="button" class="rcr-breakdown-toggle" data-breakdown-target="{{ $breakdownId }}" aria-expanded="false">+</button>
                                        {{ $groupRegno }}
                                        <span class="rcr-row-count">{{ $number($groupRows->count()) }} row</span>
                                    </td>
                                    <td>
                                        <div class="rcr-guest">
                                            <strong>{{ $firstRow->Guest ?: '-' }}</strong>
                                        </div>
                                    </td>
                                    <td class="rcr-number">{{ $number($groupRows->count()) }}</td>
                                    <td class="rcr-number">{{ $number($groupRows->sum('Person')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Room1')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Cafe1')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Other1')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Vila')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Dpp')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Service')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Tax')) }}</td>
                                    <td class="rcr-money">{{ $money($groupRows->sum('Total')) }}</td>
                                </tr>
                                <tr class="rcr-breakdown-row" id="{{ $breakdownId }}">
                                    <td class="rcr-nowrap rcr-breakdown-cell">INVOICE / ROOM / DATE</td>
                                    <td class="rcr-breakdown-cell">Guest</td>
                                    <td class="rcr-number rcr-breakdown-cell">Rows</td>
                                    <td class="rcr-number rcr-breakdown-cell">Pax</td>
                                    <td class="rcr-money rcr-breakdown-cell">ROOM</td>
                                    <td class="rcr-money rcr-breakdown-cell">REST</td>
                                    <td class="rcr-money rcr-breakdown-cell">Other</td>
                                    <td class="rcr-money rcr-breakdown-cell">VILA</td>
                                    <td class="rcr-money rcr-breakdown-cell">DPP</td>
                                    <td class="rcr-money rcr-breakdown-cell">Service</td>
                                    <td class="rcr-money rcr-breakdown-cell">Tax</td>
                                    <td class="rcr-money rcr-breakdown-cell">Total</td>
                                </tr>
                                @foreach($groupRows as $row)
                                    <tr class="rcr-breakdown-row" data-breakdown-row="{{ $breakdownId }}">
                                        <td class="rcr-nowrap rcr-breakdown-cell">
                                            {{ $row->Invoice2 ?: '-' }} / {{ $row->Kode ?: '-' }} / {{ $row->TanggalDisplay ?: '-' }}
                                        </td>
                                        <td class="rcr-breakdown-cell">
                                            <div class="rcr-guest">
                                                <strong>{{ $row->Guest ?: '-' }}</strong>
                                            </div>
                                        </td>
                                        <td class="rcr-number rcr-breakdown-cell">1</td>
                                        <td class="rcr-number rcr-breakdown-cell">{{ $number($row->Person) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Room1) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Cafe1) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Other1) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Vila) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Dpp) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Service) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Tax) }}</td>
                                        <td class="rcr-money rcr-breakdown-cell">{{ $money($row->Total) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                    @if($summaryRegnoGroups->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="4">GRAND TOTAL</td>
                                <td class="rcr-money">{{ $money($summary['totals']['room']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['cafe']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['other']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['vila']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['dpp']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['service']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['tax']) }}</td>
                                <td class="rcr-money">{{ $money($summary['totals']['total']) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="rcr-pagination">
                {{ $summaryDirectory->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>

        <div class="rcr-tab-panel {{ $activeTab === 'detail' ? 'is-active' : '' }}" data-rcr-panel="detail">
        <div class="rcr-table-wrap">
            <table class="table rcr-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('detail', 'invoice') }}" class="rcr-sort-link" data-rcr-grid-link>INVOICE{{ $sortMark('detail', 'invoice') }}</a></th>
                        <th><a href="{{ $sortUrl('detail', 'room_no') }}" class="rcr-sort-link" data-rcr-grid-link>Room #{{ $sortMark('detail', 'room_no') }}</a></th>
                        <th><a href="{{ $sortUrl('detail', 'guest') }}" class="rcr-sort-link" data-rcr-grid-link>Guest{{ $sortMark('detail', 'guest') }}</a></th>
                        <th class="rcr-number"><a href="{{ $sortUrl('detail', 'pax') }}" class="rcr-sort-link" data-rcr-grid-link>Pax{{ $sortMark('detail', 'pax') }}</a></th>
                        <th><a href="{{ $sortUrl('detail', 'tgl_in') }}" class="rcr-sort-link" data-rcr-grid-link>C/I Date{{ $sortMark('detail', 'tgl_in') }}</a></th>
                        <th><a href="{{ $sortUrl('detail', 'tgl_out') }}" class="rcr-sort-link" data-rcr-grid-link>C/O Date{{ $sortMark('detail', 'tgl_out') }}</a></th>
                        <th><a href="{{ $sortUrl('detail', 'tanggal') }}" class="rcr-sort-link" data-rcr-grid-link>Date #{{ $sortMark('detail', 'tanggal') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'room') }}" class="rcr-sort-link" data-rcr-grid-link>ROOM{{ $sortMark('detail', 'room') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'rest') }}" class="rcr-sort-link" data-rcr-grid-link>REST{{ $sortMark('detail', 'rest') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'other') }}" class="rcr-sort-link" data-rcr-grid-link>Other{{ $sortMark('detail', 'other') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'vila') }}" class="rcr-sort-link" data-rcr-grid-link>VILA{{ $sortMark('detail', 'vila') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'dpp') }}" class="rcr-sort-link" data-rcr-grid-link>DPP{{ $sortMark('detail', 'dpp') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'service') }}" class="rcr-sort-link" data-rcr-grid-link>Service{{ $sortMark('detail', 'service') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'tax') }}" class="rcr-sort-link" data-rcr-grid-link>Tax{{ $sortMark('detail', 'tax') }}</a></th>
                        <th class="rcr-money"><a href="{{ $sortUrl('detail', 'total') }}" class="rcr-sort-link" data-rcr-grid-link>Total{{ $sortMark('detail', 'total') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $visibleRows = collect(method_exists($directory, 'items') ? $directory->items() : $directory);
                        $regnoGroups = $visibleRows->groupBy(fn ($row) => trim((string) ($row->Regno ?? '')) ?: '-');
                        $showGrandTotal = method_exists($directory, 'onLastPage') && $directory->onLastPage() && $visibleRows->isNotEmpty();
                    @endphp
                    @if($regnoGroups->isEmpty())
                        <tr>
                            <td colspan="15" class="rcr-empty">No RCR data is available for this period.</td>
                        </tr>
                    @else
                        @foreach($regnoGroups as $groupRegno => $groupRows)
                            @foreach($groupRows as $row)
                                <tr>
                                    <td class="rcr-nowrap">{{ $row->Invoice2 ?: '-' }}</td>
                                    <td class="rcr-nowrap">{{ $row->Kode ?: '-' }}</td>
                                    <td>
                                        <div class="rcr-guest">
                                            <strong>{{ $row->Guest ?: '-' }}</strong>
                                        </div>
                                    </td>
                                    <td class="rcr-number">{{ $number($row->Person) }}</td>
                                    <td class="rcr-nowrap">{{ $row->TglInDisplay ?: '-' }}</td>
                                    <td class="rcr-nowrap">{{ $row->TglOutDisplay ?: '-' }}</td>
                                    <td class="rcr-nowrap">{{ $row->TanggalDisplay ?: '-' }}</td>
                                    <td class="rcr-money">{{ $money($row->Room1) }}</td>
                                    <td class="rcr-money">{{ $money($row->Cafe1) }}</td>
                                    <td class="rcr-money">{{ $money($row->Other1) }}</td>
                                    <td class="rcr-money">{{ $money($row->Vila) }}</td>
                                    <td class="rcr-money">{{ $money($row->Dpp) }}</td>
                                    <td class="rcr-money">{{ $money($row->Service) }}</td>
                                    <td class="rcr-money">{{ $money($row->Tax) }}</td>
                                    <td class="rcr-money">{{ $money($row->Total) }}</td>
                                </tr>
                            @endforeach
                            <tr class="rcr-subtotal-row">
                                <td colspan="7">SUBTOTAL REGNO {{ $groupRegno }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Room1')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Cafe1')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Other1')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Vila')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Dpp')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Service')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Tax')) }}</td>
                                <td class="rcr-money">{{ $money($groupRows->sum('Total')) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                @if($showGrandTotal)
                    <tfoot>
                        <tr>
                            <td colspan="7">GRAND TOTAL</td>
                            <td class="rcr-money">{{ $money($summary['totals']['room']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['cafe']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['other']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['vila']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['dpp']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['service']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['tax']) }}</td>
                            <td class="rcr-money">{{ $money($summary['totals']['total']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="rcr-pagination">
            {{ $directory->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
        </div>
    </section>
</div>

<script>
    const setRcrActiveTab = (target) => {
        document.querySelectorAll('[data-rcr-tab]').forEach((tabButton) => {
            tabButton.classList.toggle('is-active', tabButton.dataset.rcrTab === target);
        });

        document.querySelectorAll('[data-rcr-panel]').forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.rcrPanel === target);
        });
    };

    const currentRcrTab = () => document.querySelector('[data-rcr-tab].is-active')?.dataset.rcrTab || 'summary';

    const refreshRcrGrid = async (href) => {
        const url = new URL(href, window.location.href);
        url.searchParams.set('active_tab', currentRcrTab());

        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            window.location.href = url.toString();
            return;
        }

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextGrid = doc.querySelector('.rcr-table-band');
        const currentGrid = document.querySelector('.rcr-table-band');

        if (!nextGrid || !currentGrid) {
            window.location.href = url.toString();
            return;
        }

        currentGrid.replaceWith(nextGrid);
        window.history.pushState({}, '', url.toString());
    };

    document.addEventListener('click', (event) => {
        const tabButton = event.target.closest('[data-rcr-tab]');
        if (tabButton) {
            setRcrActiveTab(tabButton.dataset.rcrTab);
            return;
        }

        const button = event.target.closest('[data-breakdown-target]');
        if (button) {
            const targetId = button.dataset.breakdownTarget;
            const rows = document.querySelectorAll(`#${targetId}, [data-breakdown-row="${targetId}"]`);
            if (!rows.length) return;

            const isOpen = !rows[0].classList.contains('is-open');
            rows.forEach((row) => row.classList.toggle('is-open', isOpen));
            button.textContent = isOpen ? '-' : '+';
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            return;
        }

        const gridLink = event.target.closest('.rcr-table-band a[data-rcr-grid-link], .rcr-table-band .rcr-pagination a[href]');
        if (gridLink) {
            event.preventDefault();
            refreshRcrGrid(gridLink.href);
        }
    });

    document.querySelectorAll('[data-date-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.dateTarget);
            if (!input) return;
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
            }
        });
    });
</script>
@endsection
