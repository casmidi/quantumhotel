@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="ed-topbar-brand">
        <div class="ed-topbar-title">Expected Departure</div>
        <div class="ed-topbar-subtitle">Departure readiness, overdue stay control, and front office follow-up list.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="ed-topbar-tools">
        <div class="ed-topbar-pill">
            <small>Period</small>
            <strong>{{ \Carbon\Carbon::parse($businessDate)->format('d M Y') }}</strong>
        </div>
        <div class="ed-topbar-pill">
            <small>Source</small>
            <strong>{{ $summary['source'] }}</strong>
        </div>
        <a href="{{ $printUrl }}" target="_blank" class="btn package-btn-secondary ed-print-link">
            <i class="fas fa-print mr-1"></i> Print
        </a>
    </div>
@endsection

@section('content')
@include('partials.crud-package-theme')

@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
@endphp

<style>
    .ed-topbar-brand {
        display: grid;
        gap: 0.22rem;
    }

    .ed-topbar-title {
        color: var(--package-title, #173761);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 2rem;
        line-height: 1;
        font-weight: 600;
    }

    .ed-topbar-subtitle {
        color: var(--package-muted, #516783);
        font-size: 0.93rem;
        font-weight: 650;
    }

    .ed-topbar-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .ed-topbar-pill {
        min-width: 150px;
        padding: 0.75rem 0.9rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 28px rgba(16, 35, 59, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .ed-topbar-pill small,
    .ed-label,
    .ed-stat small {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--package-label);
    }

    .ed-topbar-pill strong {
        display: block;
        color: var(--package-title);
        font-size: 1rem;
        line-height: 1.1;
    }

    .ed-page {
        display: grid;
        gap: 1rem;
        padding: 0 0 2rem;
        color: var(--package-text);
    }

    .ed-filter-band,
    .ed-table-band {
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        overflow: hidden;
    }

    .ed-filter-band {
        padding: 1rem;
    }

    .ed-filter {
        display: grid;
        grid-template-columns: 190px minmax(220px, 1fr) 140px auto auto;
        gap: 0.75rem;
        align-items: end;
    }

    .ed-field {
        display: grid;
        gap: 0.4rem;
    }

    .ed-input,
    .ed-select {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid var(--package-input-border);
        background: var(--package-input-bg);
        color: var(--package-text);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        font-weight: 700;
    }

    .ed-input:focus,
    .ed-select:focus {
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
    }

    .ed-stats {
        display: grid;
        grid-template-columns: repeat(6, minmax(130px, 1fr));
        gap: 0.75rem;
    }

    .ed-stat {
        min-height: 92px;
        padding: 0.9rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 26px rgba(16, 35, 59, 0.05);
    }

    .ed-stat strong {
        display: block;
        margin-top: 0.35rem;
        color: var(--package-title);
        font-size: 1.26rem;
        line-height: 1.1;
        font-weight: 900;
    }

    .ed-payment-strip {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .ed-payment-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 34px;
        padding: 0.45rem 0.68rem;
        border-radius: 8px;
        background: var(--package-badge-bg);
        color: var(--package-badge-text);
        border: 1px solid var(--package-shell-border);
        font-size: 0.82rem;
        font-weight: 850;
    }

    .ed-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 1rem 1.15rem;
        background: var(--package-header-bg);
        border-bottom: 1px solid var(--package-shell-border);
    }

    .ed-table-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.05rem;
        font-weight: 900;
    }

    .ed-table-head span {
        color: var(--package-muted);
        font-size: 0.86rem;
        font-weight: 750;
    }

    .ed-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .ed-table {
        min-width: 1080px;
        margin: 0;
    }

    .ed-table thead th {
        display: table-cell;
        padding: 0.82rem 0.75rem;
        background: var(--package-table-head-bg);
        color: var(--package-title);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        font-weight: 900;
        border-top: 0;
        border-bottom: 1px solid var(--package-shell-border);
        white-space: nowrap;
    }

    .ed-table tbody td {
        padding: 0.72rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid var(--package-shell-border);
        color: var(--package-text);
        font-size: 0.88rem;
    }

    .ed-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .ed-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .ed-table tbody tr:hover {
        background: var(--package-table-hover);
        box-shadow: inset 4px 0 0 var(--package-table-hover-accent);
    }

    .ed-room,
    .ed-guest {
        display: grid;
        gap: 0.15rem;
    }

    .ed-room strong,
    .ed-guest strong {
        color: var(--package-title);
        font-weight: 900;
    }

    .ed-room small,
    .ed-guest small,
    .ed-muted {
        color: var(--package-muted);
        font-weight: 700;
    }

    .ed-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.56rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .ed-status.is-due {
        color: #17624a;
        background: #e8f7f1;
    }

    .ed-status.is-overdue {
        color: #9f1f1f;
        background: #fde8e8;
    }

    .ed-flag {
        display: inline-flex;
        margin-top: 0.28rem;
        padding: 0.2rem 0.42rem;
        border-radius: 999px;
        color: #75530d;
        background: #fff3d8;
        font-size: 0.67rem;
        font-weight: 900;
    }

    .ed-pagination {
        display: flex;
        justify-content: flex-end;
        padding: 0.9rem 1rem;
        border-top: 1px solid var(--package-shell-border);
        background: rgba(255, 255, 255, 0.58);
    }

    .ed-empty,
    .ed-warning {
        border-radius: 8px;
        padding: 1rem;
        background: var(--package-heading-bg);
        border: 1px dashed var(--package-shell-border);
        color: var(--package-muted);
        font-weight: 750;
    }

    .text-money {
        font-weight: 900;
        color: var(--package-table-hover-accent);
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .ed-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ed-filter .btn,
        .ed-print-link {
            width: 100%;
            justify-content: center;
        }

        .ed-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .ed-topbar-title {
            font-size: 1.45rem;
        }

        .ed-filter,
        .ed-stats {
            grid-template-columns: 1fr;
        }

        .ed-table-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .ed-pagination {
            justify-content: center;
            overflow-x: auto;
        }
    }
</style>

<section class="ed-page">
    @if(!$schemaReady)
        <div class="ed-warning">
            Tabel DATA2 atau ROOM belum tersedia, sehingga Expected Departure belum bisa ditampilkan.
        </div>
    @endif

    <div class="ed-filter-band">
        <form method="GET" action="/expected-departure" class="ed-filter">
            <div class="ed-field">
                <label class="ed-label" for="business_date">Period</label>
                <input type="date" id="business_date" name="business_date" value="{{ $businessDate }}" class="form-control ed-input" list="audit-date-list">
                <datalist id="audit-date-list">
                    @foreach($auditBatches as $batch)
                        <option value="{{ \Carbon\Carbon::parse($batch->business_date)->format('Y-m-d') }}">{{ $batch->audit_no }} - {{ $batch->status }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="ed-field">
                <label class="ed-label" for="search">Search</label>
                <input type="search" id="search" name="search" value="{{ $search }}" class="form-control ed-input" placeholder="Room, guest, city, payment, remark">
            </div>
            <div class="ed-field">
                <label class="ed-label" for="per_page">Rows</label>
                <select id="per_page" name="per_page" class="custom-select ed-select">
                    @foreach([25, 50, 100, 150] as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn package-btn-primary">
                <i class="fas fa-magnifying-glass mr-1"></i> Preview
            </button>
            <a href="{{ $printUrl }}" target="_blank" class="btn package-btn-secondary">
                <i class="fas fa-print mr-1"></i> Print Report
            </a>
        </form>
    </div>

    <div class="ed-stats">
        <div class="ed-stat"><small>Rooms</small><strong>{{ $number($summary['rooms']) }}</strong></div>
        <div class="ed-stat"><small>Departure Rows</small><strong>{{ $number($summary['total_rows']) }}</strong></div>
        <div class="ed-stat"><small>Due Today</small><strong>{{ $number($summary['due_today']) }}</strong></div>
        <div class="ed-stat"><small>Overdue</small><strong>{{ $number($summary['overdue']) }}</strong></div>
        <div class="ed-stat"><small>Pax</small><strong>{{ $number($summary['pax']) }}</strong></div>
        <div class="ed-stat"><small>Room Rate</small><strong>{{ $money($summary['rate_total']) }}</strong></div>
    </div>

    @if($summary['payment_groups']->isNotEmpty())
        <div class="ed-payment-strip">
            @foreach($summary['payment_groups'] as $payment)
                <span class="ed-payment-chip">
                    <i class="fas fa-credit-card"></i>
                    {{ $payment->payment }}: {{ $number($payment->rooms) }} rooms / {{ $number($payment->pax) }} pax
                </span>
            @endforeach
        </div>
    @endif

    <div class="ed-table-band">
        <div class="ed-table-head">
            <h3><i class="fas fa-calendar-check mr-1"></i> Expected Departure Report</h3>
            <span>{{ $summary['audit_no'] ? 'Audit No: ' . $summary['audit_no'] : 'Live preview' }} / Generated {{ \Carbon\Carbon::parse($summary['generated_at'])->format('d M Y H:i') }}</span>
        </div>
        <div class="ed-table-wrap">
            <table class="table ed-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Kota</th>
                        <th>Guest</th>
                        <th>Payment</th>
                        <th>Remark</th>
                        <th>TglIn</th>
                        <th>JamIn</th>
                        <th>TglKeluar</th>
                        <th class="text-right">Pax</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($directory as $row)
                        <tr>
                            <td>
                                <span class="ed-room">
                                    <strong>{{ $row->Kode ?: '-' }}</strong>
                                    <small>{{ $row->RoomClass ?: '-' }}</small>
                                </span>
                            </td>
                            <td>{{ $row->Kota ?: '-' }}</td>
                            <td>
                                <span class="ed-guest">
                                    <strong>{{ $row->GuestDisplay ?: '-' }}</strong>
                                    <small>{{ $row->RegNo ?: '-' }} / {{ $row->RegNo2 ?: '-' }}</small>
                                </span>
                            </td>
                            <td>{{ $row->Payment ?: '-' }}</td>
                            <td>{{ $row->Remark ?: '-' }}</td>
                            <td>{{ $row->TglInDisplay ?: '-' }}</td>
                            <td>{{ $row->JamInDisplay ?: '-' }}</td>
                            <td>{{ $row->TglKeluarDisplay ?: '-' }}</td>
                            <td class="text-right">{{ $number($row->Pax) }}</td>
                            <td>
                                <span class="ed-status {{ $row->departure_status === 'Overdue' ? 'is-overdue' : 'is-due' }}">{{ $row->departure_status }}</span>
                                @if($row->control_flag)
                                    <span class="ed-flag">{{ $row->control_flag }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="ed-empty">Tidak ada Expected Departure untuk period ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ed-pagination">
            {{ $directory->links() }}
        </div>
    </div>
</section>
@endsection
