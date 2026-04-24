@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="na-topbar-brand">
        <div class="na-topbar-title">Night Audit</div>
        <div class="na-topbar-subtitle">Business date close, room control, revenue, cashier, and approval.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="na-topbar-tools">
        <div class="na-topbar-pill">
            <small>Business Date</small>
            <strong>{{ \Carbon\Carbon::parse($summary['business_date'])->format('d M Y') }}</strong>
        </div>
        <div class="na-topbar-pill">
            <small>Status</small>
            <strong>{{ $selectedBatch->status ?? 'Preview' }}</strong>
        </div>
    </div>
@endsection

@section('content')
@include('partials.crud-package-theme')

@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $number = fn ($value) => number_format((float) $value, 0, ',', '.');
    $statusClass = function (?string $status) {
        return match ($status) {
            'Approved' => 'is-approved',
            'Closed' => 'is-closed',
            'Draft' => 'is-draft',
            default => 'is-preview',
        };
    };
    $severityClass = function (?string $severity) {
        return match ($severity) {
            'Critical' => 'is-critical',
            'High' => 'is-high',
            'Medium' => 'is-medium',
            default => 'is-low',
        };
    };
@endphp

<style>
    .na-topbar-brand {
        display: grid;
        gap: 0.2rem;
    }

    .na-topbar-title {
        color: #173761;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        font-family: "Segoe UI", "Trebuchet MS", Arial, sans-serif;
    }

    .na-topbar-subtitle {
        color: #516783;
        font-size: 0.93rem;
        font-weight: 650;
    }

    .na-topbar-tools {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .na-topbar-pill,
    .na-stat,
    .na-panel,
    .na-batch-link,
    .na-empty,
    .na-mini-panel {
        border-radius: 8px;
    }

    .na-topbar-pill {
        min-width: 160px;
        padding: 0.75rem 0.9rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(137, 167, 214, 0.3);
    }

    .na-topbar-pill small,
    .na-stat small,
    .na-field label,
    .na-table thead th {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        font-weight: 900;
    }

    .na-topbar-pill small,
    .na-stat small,
    .na-field label {
        color: #6c7f98;
    }

    .na-topbar-pill strong {
        color: #173761;
        font-size: 1rem;
        line-height: 1.1;
    }

    .na-page {
        display: grid;
        gap: 1rem;
        color: #10233b;
    }

    .na-grid {
        display: grid;
        grid-template-columns: minmax(260px, 0.32fr) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .na-panel {
        background: #fff;
        border: 1px solid #dbe8ff;
        box-shadow: 0 10px 24px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .na-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1rem;
        background: #eef5ff;
        border-bottom: 1px solid rgba(137, 167, 214, 0.25);
    }

    .na-panel-header h3 {
        margin: 0;
        color: #173761;
        font-size: 1rem;
        font-weight: 900;
    }

    .na-panel-body {
        padding: 1rem;
    }

    .na-action-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .na-filter {
        display: grid;
        gap: 0.75rem;
    }

    .na-field {
        display: grid;
        gap: 0.35rem;
    }

    .na-field .form-control,
    .na-field .custom-select {
        min-height: 42px;
        border-radius: 8px;
        font-weight: 700;
    }

    .na-stat-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(140px, 1fr));
        gap: 0.75rem;
    }

    .na-stat {
        padding: 0.9rem;
        border: 1px solid rgba(137, 167, 214, 0.24);
        background: #fbfdff;
    }

    .na-stat strong {
        display: block;
        color: #173761;
        font-size: 1.22rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .na-stat.is-money strong {
        color: #1f6f52;
    }

    .na-status-badge,
    .na-severity,
    .na-risk {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.34rem 0.62rem;
        font-size: 0.72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .na-status-badge.is-approved {
        color: #17624a;
        background: #e8f7f1;
    }

    .na-status-badge.is-closed {
        color: #6c4d12;
        background: #fff3d8;
    }

    .na-status-badge.is-draft {
        color: #174ea6;
        background: #e8f0fe;
    }

    .na-status-badge.is-preview {
        color: #5d6677;
        background: #edf1f7;
    }

    .na-severity.is-critical,
    .na-severity.is-high {
        color: #9f1f1f;
        background: #fde8e8;
    }

    .na-severity.is-medium {
        color: #7a4b00;
        background: #fff0cf;
    }

    .na-severity.is-low,
    .na-risk {
        color: #31527a;
        background: #edf5ff;
    }

    .na-batch-list {
        display: grid;
        gap: 0.6rem;
        max-height: 620px;
        overflow: auto;
    }

    .na-batch-link {
        display: grid;
        gap: 0.35rem;
        padding: 0.8rem;
        color: #10233b;
        background: #f9fbff;
        border: 1px solid rgba(137, 167, 214, 0.24);
        text-decoration: none;
    }

    .na-batch-link:hover {
        color: #10233b;
        text-decoration: none;
        border-color: rgba(23, 55, 97, 0.35);
        background: #f1f7ff;
    }

    .na-batch-link.is-active {
        border-color: #7fa7df;
        box-shadow: inset 4px 0 0 #2c69b0;
    }

    .na-batch-link strong {
        color: #173761;
        font-size: 0.94rem;
    }

    .na-batch-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        color: #667991;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .na-tabs {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin: 0 0 0.85rem;
    }

    .na-tabs .nav-link {
        border-radius: 999px;
        border: 1px solid rgba(137, 167, 214, 0.32);
        color: #365a87;
        font-weight: 850;
        padding: 0.55rem 0.85rem;
    }

    .na-tabs .nav-link.active {
        color: #fff;
        background: #245b96;
        border-color: #245b96;
    }

    .na-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .na-table {
        min-width: 900px;
        margin: 0;
    }

    .na-table thead th {
        color: #173761;
        background: #f0f6ff;
        border-top: 0;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        padding: 0.75rem;
        white-space: nowrap;
    }

    .na-table tbody td {
        padding: 0.72rem;
        vertical-align: middle;
        border-top: 1px solid rgba(137, 167, 214, 0.16);
        font-size: 0.88rem;
    }

    .na-table tbody tr:nth-child(even) {
        background: #fbfdff;
    }

    .na-empty {
        padding: 1.35rem;
        background: #f7faff;
        border: 1px dashed rgba(137, 167, 214, 0.38);
        color: #667991;
        text-align: center;
        font-weight: 750;
    }

    .na-mini-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .na-mini-panel {
        border: 1px solid rgba(137, 167, 214, 0.24);
        background: #fbfdff;
        padding: 0.9rem;
    }

    .na-mini-panel h4 {
        margin: 0 0 0.65rem;
        color: #173761;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .na-checklist-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 150px 220px auto;
        gap: 0.55rem;
        align-items: center;
    }

    .na-checklist-title strong {
        display: block;
        color: #173761;
    }

    .na-checklist-title small {
        display: block;
        color: #667991;
        font-weight: 700;
    }

    .na-adjustment-form {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        align-items: end;
    }

    .na-adjustment-form .is-wide {
        grid-column: span 2;
    }

    @media (max-width: 1199.98px) {
        .na-grid,
        .na-mini-grid {
            grid-template-columns: 1fr;
        }

        .na-stat-grid {
            grid-template-columns: repeat(3, minmax(140px, 1fr));
        }

        .na-checklist-form,
        .na-adjustment-form {
            grid-template-columns: 1fr;
        }

        .na-adjustment-form .is-wide {
            grid-column: span 1;
        }
    }

    @media (max-width: 767.98px) {
        .na-topbar-title {
            font-size: 1.45rem;
        }

        .na-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .na-panel-header,
        .na-action-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .na-action-row .btn,
        .na-filter .btn {
            width: 100%;
        }
    }
</style>

<section class="na-page">
    @if(session('success'))
        <div class="alert alert-success package-alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger package-error">{{ session('error') }}</div>
    @endif

    @if(!$schemaReady)
        <div class="alert alert-warning">
            Tabel night audit belum dibuat. Jalankan migration terbaru sebelum menyimpan batch audit.
        </div>
    @endif

    <div class="na-grid">
        <aside class="na-panel">
            <div class="na-panel-header">
                <h3><i class="fas fa-calendar-check mr-1"></i> Audit Date</h3>
            </div>
            <div class="na-panel-body">
                <form method="GET" action="/night-audit" class="na-filter">
                    <div class="na-field">
                        <label for="audit_date">Business Date</label>
                        <input type="date" id="audit_date" name="audit_date" value="{{ $auditDate }}" class="form-control">
                    </div>
                    <button type="submit" class="btn package-btn-secondary">
                        <i class="fas fa-magnifying-glass mr-1"></i> Preview
                    </button>
                </form>

                <hr>

                @if(!$selectedBatch)
                    <form method="POST" action="/night-audit/start" class="na-filter">
                        @csrf
                        <input type="hidden" name="audit_date" value="{{ $auditDate }}">
                        <div class="na-field">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Night manager note"></textarea>
                        </div>
                        <button type="submit" class="btn package-btn-primary" @disabled(!$schemaReady)>
                            <i class="fas fa-play mr-1"></i> Start Batch
                        </button>
                    </form>
                @else
                    <div class="na-action-row">
                        @if($selectedBatch->status === 'Draft')
                            <form method="POST" action="/night-audit/{{ $selectedBatch->id }}/refresh">
                                @csrf
                                <button type="submit" class="btn package-btn-secondary">
                                    <i class="fas fa-rotate mr-1"></i> Refresh
                                </button>
                            </form>
                            <form method="POST" action="/night-audit/{{ $selectedBatch->id }}/close" onsubmit="return confirm('Tutup night audit untuk tanggal ini?')">
                                @csrf
                                <button type="submit" class="btn package-btn-primary">
                                    <i class="fas fa-lock mr-1"></i> Close Audit
                                </button>
                            </form>
                        @elseif($selectedBatch->status === 'Closed')
                            <form method="POST" action="/night-audit/{{ $selectedBatch->id }}/approve" onsubmit="return confirm('Approve final night audit ini?')">
                                @csrf
                                <button type="submit" class="btn package-btn-primary">
                                    <i class="fas fa-check-double mr-1"></i> Approve
                                </button>
                            </form>
                        @else
                            <span class="na-status-badge {{ $statusClass($selectedBatch->status) }}">
                                <i class="fas fa-shield-check"></i>{{ $selectedBatch->status }}
                            </span>
                        @endif
                    </div>
                @endif

                <hr>

                <div class="na-batch-list">
                    @forelse($batches as $batch)
                        <a
                            href="/night-audit?batch_id={{ $batch->id }}&audit_date={{ \Carbon\Carbon::parse($batch->business_date)->format('Y-m-d') }}"
                            class="na-batch-link {{ $selectedBatch && (int) $selectedBatch->id === (int) $batch->id ? 'is-active' : '' }}"
                        >
                            <strong>{{ $batch->audit_no }}</strong>
                            <span class="na-batch-meta">
                                <span>{{ \Carbon\Carbon::parse($batch->business_date)->format('d M Y') }}</span>
                                <span class="na-status-badge {{ $statusClass($batch->status) }}">{{ $batch->status }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="na-empty">Belum ada batch night audit.</div>
                    @endforelse
                </div>
            </div>
        </aside>

        <main class="na-page">
            <div class="na-panel">
                <div class="na-panel-header">
                    <h3><i class="fas fa-chart-line mr-1"></i> Daily Control Summary</h3>
                    <span class="na-status-badge {{ $statusClass($selectedBatch->status ?? null) }}">{{ $selectedBatch->status ?? 'Preview' }}</span>
                </div>
                <div class="na-panel-body">
                    <div class="na-stat-grid">
                        <div class="na-stat">
                            <small>Occupancy</small>
                            <strong>{{ number_format((float) $summary['occupancy_percent'], 2, ',', '.') }}%</strong>
                        </div>
                        <div class="na-stat">
                            <small>Occupied</small>
                            <strong>{{ $number($summary['occupied_rooms']) }} / {{ $number($summary['total_rooms']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>In House</small>
                            <strong>{{ $number($summary['in_house_count']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>Arrival / Departure</small>
                            <strong>{{ $number($summary['arrival_count']) }} / {{ $number($summary['departure_count']) }}</strong>
                        </div>
                        <div class="na-stat is-money">
                            <small>Room Revenue</small>
                            <strong>{{ $money($summary['room_revenue']) }}</strong>
                        </div>
                        <div class="na-stat is-money">
                            <small>Total Receipt</small>
                            <strong>{{ $money($summary['deposit_total']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>Vacant</small>
                            <strong>{{ $number($summary['vacant_rooms']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>OOO / Reno</small>
                            <strong>{{ $number($summary['out_of_order_rooms']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>Compl / House</small>
                            <strong>{{ $number($summary['complimentary_rooms']) }} / {{ $number($summary['house_use_rooms']) }}</strong>
                        </div>
                        <div class="na-stat is-money">
                            <small>Cash</small>
                            <strong>{{ $money($summary['cash_receipt_total']) }}</strong>
                        </div>
                        <div class="na-stat is-money">
                            <small>Non Cash</small>
                            <strong>{{ $money($summary['non_cash_receipt_total']) }}</strong>
                        </div>
                        <div class="na-stat">
                            <small>Exceptions</small>
                            <strong>{{ $number($summary['critical_exception_count']) }} / {{ $number($summary['exception_count']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="na-panel">
                <div class="na-panel-body">
                    <ul class="nav na-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#audit-overview" role="tab"><i class="fas fa-gauge-high mr-1"></i>Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#room-snapshot" role="tab"><i class="fas fa-bed mr-1"></i>Rooms</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#revenue-lines" role="tab"><i class="fas fa-receipt mr-1"></i>Revenue</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#cashier-control" role="tab"><i class="fas fa-cash-register mr-1"></i>Cashier</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#hk-exception" role="tab"><i class="fas fa-broom mr-1"></i>Discrepancy</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#checklist" role="tab"><i class="fas fa-list-check mr-1"></i>Checklist</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="audit-overview" role="tabpanel">
                            <div class="na-mini-grid">
                                <section class="na-mini-panel">
                                    <h4>Approval Flow</h4>
                                    <div class="na-table-wrap">
                                        <table class="table na-table" style="min-width: 520px">
                                            <thead>
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Approver</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($approvals as $approval)
                                                    <tr>
                                                        <td>{{ $approval->approval_level }}</td>
                                                        <td>{{ $approval->role_name }}</td>
                                                        <td><span class="na-status-badge {{ $statusClass($approval->status ?? null) }}">{{ $approval->status }}</span></td>
                                                        <td>{{ $approval->approver_name ?: '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </section>

                                <section class="na-mini-panel">
                                    <h4>Revenue Mix</h4>
                                    <div class="na-stat-grid" style="grid-template-columns: 1fr">
                                        <div class="na-stat is-money"><small>Gross</small><strong>{{ $money($summary['gross_revenue']) }}</strong></div>
                                        <div class="na-stat is-money"><small>City Ledger</small><strong>{{ $money($summary['city_ledger_total']) }}</strong></div>
                                    </div>
                                </section>

                                <section class="na-mini-panel">
                                    <h4>Control Flags</h4>
                                    <div class="na-stat-grid" style="grid-template-columns: 1fr">
                                        <div class="na-stat"><small>Walk In</small><strong>{{ $number($summary['walk_in_count']) }}</strong></div>
                                        <div class="na-stat"><small>Critical / High</small><strong>{{ $number($summary['critical_exception_count']) }}</strong></div>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="room-snapshot" role="tabpanel">
                            <div class="na-table-wrap">
                                <table class="table na-table">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>RegNo</th>
                                            <th>Segment</th>
                                            <th>Payment</th>
                                            <th>Package</th>
                                            <th>Stay</th>
                                            <th>Rate</th>
                                            <th>HK</th>
                                            <th>Flag</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($roomSnapshots as $room)
                                            <tr>
                                                <td><strong>{{ $room->room_code }}</strong><br><small>{{ $room->room_class ?: '-' }}</small></td>
                                                <td>{{ $room->guest_name ?: '-' }}</td>
                                                <td>{{ $room->regno ?: '-' }}<br><small>{{ $room->regno2 ?: '-' }}</small></td>
                                                <td>{{ $room->market_segment ?: '-' }}</td>
                                                <td>{{ $room->payment_method ?: '-' }}</td>
                                                <td>{{ $room->package_code ?: '-' }}</td>
                                                <td>{{ $number($room->stay_nights ?? 0) }} night</td>
                                                <td>{{ $money($room->net_room_rate ?? 0) }}</td>
                                                <td>{{ $room->housekeeping_status ?: '-' }}</td>
                                                <td>
                                                    @if($room->risk_flag)
                                                        <span class="na-risk">{{ $room->risk_flag }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="10" class="text-center">Tidak ada kamar in-house.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="revenue-lines" role="tabpanel">
                            <div class="na-table-wrap">
                                <table class="table na-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Dept</th>
                                            <th>Code</th>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>Description</th>
                                            <th class="text-right">Debit</th>
                                            <th class="text-right">Credit</th>
                                            <th class="text-right">Net</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($revenueLines as $line)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($line->transaction_date)->format('d-m-Y') }}</td>
                                                <td>{{ $line->department }}</td>
                                                <td>{{ $line->revenue_code ?: '-' }}</td>
                                                <td>{{ $line->room_code ?: '-' }}</td>
                                                <td>{{ $line->guest_name ?: '-' }}</td>
                                                <td>{{ $line->description }}</td>
                                                <td class="text-right">{{ $money($line->debit ?? 0) }}</td>
                                                <td class="text-right">{{ $money($line->credit ?? 0) }}</td>
                                                <td class="text-right"><strong>{{ $money($line->net_amount ?? 0) }}</strong></td>
                                                <td>
                                                    @if($line->risk_flag)
                                                        <span class="na-risk">{{ $line->risk_flag }}</span>
                                                    @else
                                                        {{ $line->status }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="10" class="text-center">Belum ada revenue preview.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="cashier-control" role="tabpanel">
                            <div class="na-table-wrap">
                                <table class="table na-table">
                                    <thead>
                                        <tr>
                                            <th>Cashier</th>
                                            <th>Shift</th>
                                            <th>Payment</th>
                                            <th class="text-right">Receipt</th>
                                            <th class="text-right">Refund</th>
                                            <th class="text-right">Cash Drop</th>
                                            <th class="text-right">Variance</th>
                                            <th>Trx</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($cashierSummaries as $cashier)
                                            <tr>
                                                <td>{{ $cashier->cashier_code }}</td>
                                                <td>{{ $cashier->shift_code ?: '-' }}</td>
                                                <td>{{ $cashier->payment_type }}</td>
                                                <td class="text-right">{{ $money($cashier->gross_receipt ?? 0) }}</td>
                                                <td class="text-right">{{ $money($cashier->refund_amount ?? 0) }}</td>
                                                <td class="text-right">{{ $money($cashier->cash_drop ?? 0) }}</td>
                                                <td class="text-right">{{ $money($cashier->variance_amount ?? 0) }}</td>
                                                <td>{{ $number($cashier->transaction_count ?? 0) }}</td>
                                                <td>{{ $cashier->settlement_status }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center">Belum ada transaksi kasir pada tanggal audit.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($selectedBatch && $selectedBatch->status === 'Draft')
                                <hr id="adjustments">
                                <form method="POST" action="/night-audit/{{ $selectedBatch->id }}/adjustments" class="na-adjustment-form">
                                    @csrf
                                    <div class="na-field">
                                        <label>Room</label>
                                        <input type="text" name="room_code" class="form-control" maxlength="20">
                                    </div>
                                    <div class="na-field">
                                        <label>RegNo</label>
                                        <input type="text" name="regno" class="form-control" maxlength="30">
                                    </div>
                                    <div class="na-field">
                                        <label>Department</label>
                                        <select name="department" class="custom-select" required>
                                            <option value="ROOM">ROOM</option>
                                            <option value="PACKAGE">PACKAGE</option>
                                            <option value="MINIBAR">MINIBAR</option>
                                            <option value="LAUNDRY">LAUNDRY</option>
                                            <option value="OTHER">OTHER</option>
                                        </select>
                                    </div>
                                    <div class="na-field">
                                        <label>Amount</label>
                                        <input type="number" name="amount" class="form-control" step="0.01" required>
                                    </div>
                                    <div class="na-field">
                                        <label>Reason</label>
                                        <input type="text" name="reason_code" class="form-control" maxlength="80" required>
                                    </div>
                                    <div class="na-field is-wide">
                                        <label>Description</label>
                                        <input type="text" name="description" class="form-control" maxlength="255" required>
                                    </div>
                                    <div class="na-field">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn package-btn-secondary">
                                            <i class="fas fa-plus mr-1"></i> Add Adjustment
                                        </button>
                                    </div>
                                </form>
                            @endif

                            @if($adjustments->isNotEmpty())
                                <hr>
                                <div class="na-table-wrap">
                                    <table class="table na-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Dept</th>
                                                <th>Room</th>
                                                <th>Description</th>
                                                <th class="text-right">Amount</th>
                                                <th>Status</th>
                                                <th>By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($adjustments as $adjustment)
                                                <tr>
                                                    <td>{{ $adjustment->adjustment_no }}</td>
                                                    <td>{{ $adjustment->department }}</td>
                                                    <td>{{ $adjustment->room_code ?: '-' }}</td>
                                                    <td>{{ $adjustment->description }}</td>
                                                    <td class="text-right">{{ $money($adjustment->amount ?? 0) }}</td>
                                                    <td>{{ $adjustment->approval_status }}</td>
                                                    <td>{{ $adjustment->requested_by ?: '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="hk-exception" role="tabpanel">
                            <div class="na-table-wrap">
                                <table class="table na-table">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>PMS</th>
                                            <th>HK</th>
                                            <th>Type</th>
                                            <th>Severity</th>
                                            <th>Owner</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($housekeepingExceptions as $exception)
                                            <tr>
                                                <td><strong>{{ $exception->room_code }}</strong></td>
                                                <td>{{ $exception->pms_status ?: '-' }}</td>
                                                <td>{{ $exception->housekeeping_status ?: '-' }}</td>
                                                <td>{{ $exception->exception_type }}</td>
                                                <td><span class="na-severity {{ $severityClass($exception->severity) }}">{{ $exception->severity }}</span></td>
                                                <td>{{ $exception->owner_department }}</td>
                                                <td>{{ $exception->action_status }}</td>
                                                <td>{{ $exception->notes ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" class="text-center">Tidak ada room discrepancy.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="checklist" role="tabpanel">
                            <div class="na-table-wrap">
                                <table class="table na-table">
                                    <thead>
                                        <tr>
                                            <th>Task</th>
                                            <th>Role</th>
                                            <th>Control</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($checklists as $item)
                                            <tr>
                                                <td colspan="4">
                                                    @if(!$isPreview && isset($item->id) && (!$selectedBatch || $selectedBatch->status !== 'Approved'))
                                                        <form method="POST" action="/night-audit/checklist/{{ $item->id }}" class="na-checklist-form">
                                                            @csrf
                                                            <div class="na-checklist-title">
                                                                <strong>{{ $item->sequence_no }}. {{ $item->task_name }}</strong>
                                                                <small>{{ $item->section }} - {{ $item->required_role ?: '-' }}</small>
                                                            </div>
                                                            <select name="status" class="custom-select">
                                                                @foreach($statusOptions as $option)
                                                                    <option value="{{ $option }}" @selected($item->status === $option)>{{ $option }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="text" name="remarks" class="form-control" value="{{ $item->remarks }}" placeholder="Remarks">
                                                            <button type="submit" class="btn package-btn-secondary">
                                                                <i class="fas fa-floppy-disk"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <div class="na-checklist-form" style="grid-template-columns: minmax(0, 1fr) 150px 220px auto">
                                                            <div class="na-checklist-title">
                                                                <strong>{{ $item->sequence_no }}. {{ $item->task_name }}</strong>
                                                                <small>{{ $item->section }} - {{ $item->required_role ?: '-' }}</small>
                                                            </div>
                                                            <span>{{ $item->control_level }}</span>
                                                            <span class="na-status-badge {{ $statusClass($item->status ?? null) }}">{{ $item->status }}</span>
                                                            <span>{{ ($item->completed_by ?? '') ?: '-' }}</span>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</section>
@endsection
