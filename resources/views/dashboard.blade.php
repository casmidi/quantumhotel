@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .content-wrapper {
        background:
            radial-gradient(circle at top right, rgba(179, 138, 81, 0.14), transparent 24%),
            radial-gradient(circle at left top, rgba(23, 55, 97, 0.1), transparent 28%),
            linear-gradient(180deg, #f8f4ec 0%, #edf2f8 52%, #e6edf6 100%);
        min-height: calc(100vh - 57px);
    }

    .content-wrapper > h3 {
        display: none;
    }

    .dashboard-page {
        padding: 1.2rem 0 2rem;
        color: #10233b;
    }

    .dashboard-hero {
        background: linear-gradient(135deg, #10233b 0%, #173761 55%, #b38a51 150%);
        border-radius: 28px;
        padding: 2rem;
        color: #fff;
        box-shadow: 0 24px 60px rgba(16, 35, 59, 0.2);
        margin-bottom: 1.5rem;
        overflow: hidden;
        position: relative;
    }

    .dashboard-hero::after {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        top: -120px;
        right: -70px;
        background: radial-gradient(circle, rgba(255,255,255,0.2), rgba(255,255,255,0));
    }

    .dashboard-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.18);
        background: rgba(255,255,255,0.1);
        font-size: 0.78rem;
        letter-spacing: 0.16em;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .dashboard-hero h1 {
        margin: 0 0 0.7rem;
        font-size: 2.35rem;
        font-weight: 700;
        line-height: 1.05;
    }

    .dashboard-hero p {
        margin: 0;
        max-width: 760px;
        color: rgba(255,255,255,0.8);
        line-height: 1.7;
    }

    .dashboard-totals {
        margin-top: 1.6rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .dashboard-total {
        display: inline-flex;
        flex-direction: column;
        gap: 0.2rem;
        padding: 1rem 1.15rem;
        border-radius: 18px;
        background: rgba(255,255,255,0.11);
        border: 1px solid rgba(255,255,255,0.12);
        min-width: 180px;
    }

    .dashboard-total-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255,255,255,0.72);
    }

    .dashboard-total-value {
        font-size: 1.9rem;
        font-weight: 700;
        color: #fff;
    }

    .metric-card {
        border-radius: 24px;
        padding: 1.5rem;
        color: #fff;
        box-shadow: 0 22px 48px rgba(16, 35, 59, 0.12);
        position: relative;
        overflow: hidden;
        min-height: 220px;
        height: 100%;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        right: -40px;
        bottom: -40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.12);
    }

    .metric-card.occupied { background: linear-gradient(135deg, #6f1f24, #b93a42 58%, #e07177 140%); }
    .metric-card.dirty { background: linear-gradient(135deg, #9b6a1a, #d8a23f 58%, #f4d47b 145%); }
    .metric-card.ready { background: linear-gradient(135deg, #0d6f4b, #25b37f 58%, #79e3ba 145%); }
    .metric-card.clean { background: linear-gradient(135deg, #17634f, #2aa078 58%, #65cfaa 140%); }
    .metric-card.renovated { background: linear-gradient(135deg, #5c3a7d, #8f63ba 58%, #c3a4ef 145%); }
    .metric-card.out-of-order { background: linear-gradient(135deg, #3f3072, #654db5 58%, #9d89ee 145%); }
    .metric-card.owner-unit { background: linear-gradient(135deg, #7e2830, #c85a63 58%, #ef9da4 145%); }
    .metric-card.complimentary { background: linear-gradient(135deg, #92404a, #d97b84 58%, #f5b6bc 145%); }

    .metric-label {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.14);
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        font-weight: 700;
        text-transform: uppercase;
    }

    .metric-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255,255,255,0.18);
        font-size: 1rem;
        line-height: 1;
    }

    .metric-value {
        position: relative;
        z-index: 1;
        margin-top: 1.1rem;
        font-size: 3.2rem;
        font-weight: 700;
        line-height: 1;
    }

    .metric-percent {
        position: relative;
        z-index: 1;
        margin-top: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: rgba(255,255,255,0.92);
    }

    .metric-progress {
        position: relative;
        z-index: 1;
        margin-top: 1.1rem;
        height: 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.18);
        overflow: hidden;
    }

    .metric-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: rgba(255,255,255,0.9);
    }

    .metric-copy {
        position: relative;
        z-index: 1;
        margin-top: 1rem;
        max-width: 240px;
        color: rgba(255,255,255,0.82);
        line-height: 1.6;
        font-size: 0.92rem;
    }

    .dashboard-shell {
        border: 1px solid rgba(255,255,255,0.55);
        border-radius: 24px;
        background: rgba(255,255,255,0.78);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(16, 35, 59, 0.08);
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .dashboard-shell-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
    }

    .dashboard-shell-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #10233b;
    }

    .dashboard-shell-subtitle {
        margin: 0.3rem 0 0;
        color: #66768d;
        font-size: 0.92rem;
    }

    .dashboard-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.5rem 0.8rem;
        background: rgba(179, 138, 81, 0.12);
        color: #8d6635;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(16, 35, 59, 0.06);
    }

    .breakdown-item:first-child {
        border-top: 0;
    }

    .breakdown-left {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
    }

    .breakdown-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.1rem;
        background: rgba(16, 35, 59, 0.08);
        flex-shrink: 0;
    }

    .breakdown-text strong {
        display: block;
        color: #10233b;
    }

    .breakdown-text span {
        display: block;
        color: #6b7b90;
        font-size: 0.88rem;
        margin-top: 0.18rem;
    }

    .breakdown-codes {
        margin-top: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .breakdown-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        padding: 0.35rem 0.55rem;
        border-radius: 999px;
        background: rgba(16, 35, 59, 0.08);
        color: #173761;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .breakdown-more {
        display: inline-flex;
        align-items: center;
        color: #6b7b90;
        font-size: 0.82rem;
        font-weight: 600;
        padding-left: 0.1rem;
    }

    .breakdown-right {
        text-align: right;
        min-width: 120px;
    }

    .breakdown-right strong {
        display: block;
        font-size: 1.05rem;
        color: #10233b;
    }

    .breakdown-right span {
        color: #6b7b90;
        font-size: 0.9rem;
    }

    @media (max-width: 991.98px) {
        .dashboard-hero h1 { font-size: 1.9rem; }
        .metric-value { font-size: 2.7rem; }
    }
</style>

@php
    $metricIcons = [
        'occupied' => '&#128719;',
        'vacant_dirty' => '&#129532;',
        'vacant_ready' => '&#10004;',
        'vacant_clean' => '&#9989;',
        'renovated' => '&#128736;',
        'out_of_order' => '&#9888;',
        'owner_unit' => '&#128081;',
        'complimentary' => '&#127873;',
    ];
@endphp

<div class="container-fluid dashboard-page">
    <section class="dashboard-hero">
        <div class="dashboard-kicker">Quantum Hotel Dashboard</div>
        <h1>Room Status Overview</h1>
        <p>This dashboard follows the full room-status logic from the legacy Visual Basic application, including occupied-clean and occupied-dirty behavior before and after two days of stay, while keeping the output focused on count and percentage only.</p>
        <div class="dashboard-totals">
            <div class="dashboard-total">
                <span class="dashboard-total-label">Total Active Rooms</span>
                <span class="dashboard-total-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
            </div>
            <div class="dashboard-total">
                <span class="dashboard-total-label">Operational Base</span>
                <span class="dashboard-total-value">{{ number_format($operationalBase, 0, ',', '.') }}</span>
            </div>
        </div>
    </section>

    <section class="row">
        @foreach($metrics as $metric)
        <div class="col-xl col-md-6 mb-4">
            <div class="metric-card {{ $metric['tone'] }}">
                <span class="metric-label">
                    <span class="metric-icon">{!! $metricIcons[$metric['key']] ?? '&#9632;' !!}</span>
                    <span>{{ $metric['label'] }}</span>
                </span>
                <div class="metric-value">{{ number_format($metric['count'], 0, ',', '.') }}</div>
                <div class="metric-percent">{{ number_format($metric['percentage'], 2) }}%</div>
                <div class="metric-progress">
                    <div class="metric-progress-bar" style="width: {{ min($metric['percentage'], 100) }}%;"></div>
                </div>
                <p class="metric-copy">
                    {{ $metric['count'] }} room{{ $metric['count'] === 1 ? '' : 's' }} recorded under {{ $metric['label'] }}.
                </p>
            </div>
        </div>
        @endforeach
    </section>

    <section class="dashboard-shell">
        <div class="dashboard-shell-header">
            <div>
                <h2 class="dashboard-shell-title">Occupied Breakdown</h2>
                <p class="dashboard-shell-subtitle">Converted from the VB6 logic that splits occupied rooms into clean/dirty and stay-length based groups.</p>
            </div>
            <span class="dashboard-badge">Count + Percentage</span>
        </div>

        <div>
            @foreach($occupiedBreakdown as $item)
            <div class="breakdown-item">
                <div class="breakdown-left">
                    <span class="breakdown-icon">{!! $item['icon'] !!}</span>
                    <div class="breakdown-text">
                        <strong>{{ $item['label'] }}</strong>
                        <span>Legacy occupied-room branch</span>
                        <div class="breakdown-codes">
                            @foreach(array_slice($item['rooms'], 0, 8) as $roomCode)
                            <span class="breakdown-code">{{ $roomCode }}</span>
                            @endforeach
                            @if(count($item['rooms']) > 8)
                            <span class="breakdown-more">+{{ count($item['rooms']) - 8 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="breakdown-right">
                    <strong>{{ number_format($item['count'], 0, ',', '.') }}</strong>
                    <span>{{ number_format($item['percentage'], 2) }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
