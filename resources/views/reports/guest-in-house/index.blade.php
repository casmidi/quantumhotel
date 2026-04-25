@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="gih-topbar-brand">
        <div class="gih-topbar-title">Guest In House</div>
        <div class="gih-topbar-subtitle">House count, stay-over control, and night audit guest list.</div>
    </div>
@endsection

@section('topbar_tools')
    <div class="gih-topbar-tools">
        <div class="gih-topbar-pill">
            <small>Business Date</small>
            <strong>{{ \Carbon\Carbon::parse($businessDate)->format('d M Y') }}</strong>
        </div>
        <div class="gih-topbar-pill">
            <small>Source</small>
            <strong>{{ $summary['source'] }}</strong>
        </div>
        <a href="{{ $printUrl }}" target="_blank" class="btn package-btn-secondary gih-print-link">
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
    .gih-topbar-brand {
        display: grid;
        gap: 0.22rem;
    }

    .gih-topbar-title {
        color: var(--package-title, #173761);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 2rem;
        line-height: 1;
        font-weight: 600;
    }

    .gih-topbar-subtitle {
        color: var(--package-muted, #516783);
        font-size: 0.93rem;
        font-weight: 650;
    }

    .gih-topbar-tools {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .gih-topbar-pill {
        min-width: 150px;
        padding: 0.75rem 0.9rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 28px rgba(16, 35, 59, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.65);
    }

    .gih-topbar-pill small,
    .gih-label,
    .gih-stat small {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.72rem;
        font-weight: 900;
        color: var(--package-label);
    }

    .gih-topbar-pill strong {
        display: block;
        color: var(--package-title);
        font-size: 1rem;
        line-height: 1.1;
    }

    .gih-page {
        display: grid;
        gap: 1rem;
        padding: 0 0 2rem;
        color: var(--package-text);
    }

    .gih-filter-band,
    .gih-table-band {
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.65);
        overflow: hidden;
    }

    .gih-filter-band {
        padding: 1rem;
    }

    .gih-filter {
        display: grid;
        grid-template-columns: 190px minmax(220px, 1fr) 140px auto auto;
        gap: 0.75rem;
        align-items: end;
    }

    .gih-field {
        display: grid;
        gap: 0.4rem;
    }

    .gih-input,
    .gih-select {
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid var(--package-input-border);
        background: var(--package-input-bg);
        color: var(--package-text);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        font-weight: 700;
    }

    .gih-input:focus,
    .gih-select:focus {
        border-color: var(--package-input-focus);
        box-shadow: var(--package-input-focus-shadow);
    }

    .gih-stats {
        display: grid;
        grid-template-columns: repeat(6, minmax(130px, 1fr));
        gap: 0.75rem;
    }

    .gih-stat {
        min-height: 92px;
        padding: 0.9rem;
        border-radius: 8px;
        background: var(--package-shell-bg);
        border: 1px solid var(--package-shell-border);
        box-shadow: 0 12px 26px rgba(16, 35, 59, 0.05);
    }

    .gih-stat strong {
        display: block;
        margin-top: 0.35rem;
        color: var(--package-title);
        font-size: 1.26rem;
        line-height: 1.1;
        font-weight: 900;
    }

    .gih-floor-strip {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .gih-floor-chip {
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

    .gih-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 1rem 1.15rem;
        background: var(--package-header-bg);
        border-bottom: 1px solid var(--package-shell-border);
    }

    .gih-table-head h3 {
        margin: 0;
        color: var(--package-title);
        font-size: 1.05rem;
        font-weight: 900;
    }

    .gih-table-head span {
        color: var(--package-muted);
        font-size: 0.86rem;
        font-weight: 750;
    }

    .gih-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .gih-table {
        min-width: 1420px;
        margin: 0;
    }

    .gih-table thead th {
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

    .gih-table tbody td {
        padding: 0.72rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid var(--package-shell-border);
        color: var(--package-text);
        font-size: 0.88rem;
    }

    .gih-table tbody tr:nth-child(odd) {
        background: var(--package-table-odd);
    }

    .gih-table tbody tr:nth-child(even) {
        background: var(--package-table-even);
    }

    .gih-table tbody tr:hover {
        background: var(--package-table-hover);
        box-shadow: inset 4px 0 0 var(--package-table-hover-accent);
    }

    .gih-room {
        display: grid;
        gap: 0.15rem;
        color: var(--package-title);
        font-weight: 900;
    }

    .gih-room small,
    .gih-guest small,
    .gih-muted {
        color: var(--package-muted);
        font-weight: 700;
    }

    .gih-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.32rem 0.56rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .gih-status.is-in {
        color: #17624a;
        background: #e8f7f1;
    }

    .gih-status.is-out {
        color: #75530d;
        background: #fff3d8;
    }

    .gih-flag {
        display: inline-flex;
        margin-top: 0.28rem;
        padding: 0.2rem 0.42rem;
        border-radius: 999px;
        color: #9f1f1f;
        background: #fde8e8;
        font-size: 0.67rem;
        font-weight: 900;
    }

    .gih-pagination {
        display: flex;
        justify-content: flex-end;
        padding: 0.9rem 1rem;
        border-top: 1px solid var(--package-shell-border);
        background: rgba(255, 255, 255, 0.58);
    }

    .gih-empty,
    .gih-warning {
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
        .gih-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .gih-filter .btn,
        .gih-print-link {
            width: 100%;
            justify-content: center;
        }

        .gih-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .gih-topbar-title {
            font-size: 1.45rem;
        }

        .gih-topbar-tools,
        .gih-filter,
        .gih-stats {
            grid-template-columns: 1fr;
        }

        .gih-table-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .gih-pagination {
            justify-content: center;
            overflow-x: auto;
        }
    }
</style>

<section class="gih-page">
    @if(!$schemaReady)
        <div class="gih-warning">
            Tabel DATA2 atau ROOM belum tersedia, sehingga Guest In House belum bisa ditampilkan.
        </div>
    @endif

    <div class="gih-filter-band">
        <form method="GET" action="/guest-in-house" class="gih-filter">
            <div class="gih-field">
                <label class="gih-label" for="business_date">Business Date</label>
                <input type="date" id="business_date" name="business_date" value="{{ $businessDate }}" class="form-control gih-input" list="audit-date-list">
                <datalist id="audit-date-list">
                    @foreach($auditBatches as $batch)
                        <option value="{{ \Carbon\Carbon::parse($batch->business_date)->format('Y-m-d') }}">{{ $batch->audit_no }} - {{ $batch->status }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="gih-field">
                <label class="gih-label" for="search">Search</label>
                <input type="search" id="search" name="search" value="{{ $search }}" class="form-control gih-input" placeholder="Room, guest, city, company, nationality">
            </div>
            <div class="gih-field">
                <label class="gih-label" for="per_page">Rows</label>
                <select id="per_page" name="per_page" class="custom-select gih-select">
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

    <div class="gih-stats">
        <div class="gih-stat"><small>Rooms</small><strong>{{ $number($summary['rooms']) }}</strong></div>
        <div class="gih-stat"><small>Guest Rows</small><strong>{{ $number($summary['total_rows']) }}</strong></div>
        <div class="gih-stat"><small>In House</small><strong>{{ $number($summary['in_house']) }}</strong></div>
        <div class="gih-stat"><small>Checked Out</small><strong>{{ $number($summary['checked_out']) }}</strong></div>
        <div class="gih-stat"><small>Pax</small><strong>{{ $number($summary['pax']) }}</strong></div>
        <div class="gih-stat"><small>Room Rate</small><strong>{{ $money($summary['rate_total']) }}</strong></div>
    </div>

    @if($summary['floors']->isNotEmpty())
        <div class="gih-floor-strip">
            @foreach($summary['floors'] as $floor)
                <span class="gih-floor-chip">
                    <i class="fas fa-building"></i>
                    Floor {{ $floor->floor }}: {{ $number($floor->rooms) }} rooms / {{ $number($floor->pax) }} pax
                </span>
            @endforeach
        </div>
    @endif

    <div class="gih-table-band">
        <div class="gih-table-head">
            <h3><i class="fas fa-users mr-1"></i> Guest In House Report</h3>
            <span>{{ $summary['audit_no'] ? 'Audit No: ' . $summary['audit_no'] : 'Live preview' }} / Generated {{ \Carbon\Carbon::parse($summary['generated_at'])->format('d M Y H:i') }}</span>
        </div>
        <div class="gih-table-wrap">
            <table class="table gih-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>GuestName</th>
                        <th>Kota</th>
                        <th>Person</th>
                        <th>TglIn</th>
                        <th>JamIn</th>
                        <th>TglKeluar</th>
                        <th>Usaha</th>
                        <th class="text-right">Rate1</th>
                        <th>Remark</th>
                        <th class="text-right">Day</th>
                        <th>KodeNegara</th>
                        <th>Pst</th>
                        <th>Officer</th>
                        <th>Lantai</th>
                        <th class="text-right">BF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($directory as $row)
                        <tr>
                            <td>
                                <span class="gih-room">
                                    {{ $row->Kode ?: '-' }}
                                    <small>{{ $row->RoomClass ?: '-' }}</small>
                                </span>
                            </td>
                            <td class="gih-guest">
                                <strong>{{ $row->Guest ?: '-' }}</strong>
                                <small class="d-block">{{ $row->RegNo ?: '-' }} / {{ $row->RegNo2 ?: '-' }}</small>
                            </td>
                            <td>{{ $row->Kota ?: '-' }}</td>
                            <td>{{ $number($row->Person) }}</td>
                            <td>{{ $row->TglInDisplay ?: '-' }}</td>
                            <td>{{ $row->JamInDisplay ?: '-' }}</td>
                            <td>{{ $row->TglKeluarDisplay ?: '-' }}</td>
                            <td>{{ $row->Usaha ?: '-' }}</td>
                            <td class="text-right text-money">{{ $money($row->Rate1) }}</td>
                            <td>{{ $row->Remark ?: '-' }}</td>
                            <td class="text-right">{{ $number($row->Day) }}</td>
                            <td>{{ $row->KodeNegara ?: '-' }}</td>
                            <td>
                                <span class="gih-status {{ $row->PST === 'C/O' ? 'is-out' : 'is-in' }}">{{ $row->PST }}</span>
                                @if($row->control_flag)
                                    <span class="gih-flag">{{ $row->control_flag }}</span>
                                @endif
                            </td>
                            <td>{{ $row->Officer ?: '-' }}</td>
                            <td>{{ $row->Lantai ?: '-' }}</td>
                            <td class="text-right">{{ $number($row->BF) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16">
                                <div class="gih-empty">Tidak ada data Guest In House untuk business date ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="gih-pagination">
            {{ $directory->links() }}
        </div>
    </div>
</section>
@endsection
