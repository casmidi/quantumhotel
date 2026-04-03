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
        max-width: 720px;
        color: rgba(255,255,255,0.8);
        line-height: 1.7;
    }

    .dashboard-total {
        margin-top: 1.6rem;
        display: inline-flex;
        flex-direction: column;
        gap: 0.2rem;
        padding: 1rem 1.15rem;
        border-radius: 18px;
        background: rgba(255,255,255,0.11);
        border: 1px solid rgba(255,255,255,0.12);
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
    .metric-card.dirty { background: linear-gradient(135deg, #6d2b2b, #b14d4d); }
    .metric-card.clean { background: linear-gradient(135deg, #17634f, #2aa078); }
    .metric-card.renovated { background: linear-gradient(135deg, #6b4b18, #b88431); }
    .metric-card.out-of-order { background: linear-gradient(135deg, #4a3c6e, #785eb0); }

    .metric-label {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.14);
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        font-weight: 700;
        text-transform: uppercase;
    }

    .metric-icon {`r`n        font-size: 1rem;`r`n        line-height: 1;`r`n        display: inline-flex;`r`n        align-items: center;`r`n    }`r`n`r`n    .metric-value {
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

    @media (max-width: 991.98px) {
        .dashboard-hero h1 { font-size: 1.9rem; }
        .metric-icon {`r`n        font-size: 1rem;`r`n        line-height: 1;`r`n        display: inline-flex;`r`n        align-items: center;`r`n    }`r`n`r`n    .metric-value { font-size: 2.7rem; }
    }
</style>

<div class="container-fluid dashboard-page">
    <section class="dashboard-hero">
        <div class="dashboard-kicker">Quantum Hotel Dashboard</div>
        <h1>Room Status Overview</h1>
        <p>This dashboard follows the room-status logic from the legacy Visual Basic application and presents the result in a simpler executive format: count and percentage only.</p>
        <div class="dashboard-total">
            <span class="dashboard-total-label">Total Active Rooms</span>
            <span class="dashboard-total-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
        </div>
    </section>

    <section class="row">
        @foreach($metrics as $metric)
        <div class="col-xl col-md-6 mb-4">
            <div class="metric-card {{ $metric['tone'] }}">
                <span class="metric-label">@if($metric['key'] === 'occupied')<span class="metric-icon">&#128719;</span>@endif {{ $metric['label'] }}</span>
                <div class="metric-value">{{ number_format($metric['count'], 0, ',', '.') }}</div>
                <div class="metric-percent">{{ number_format($metric['percentage'], 2) }}%</div>
                <div class="metric-progress">
                    <div class="metric-progress-bar" style="width: {{ min($metric['percentage'], 100) }}%;"></div>
                </div>
                <p class="metric-copy">
                    {{ $metric['count'] }} room{{ $metric['count'] === 1 ? '' : 's' }} out of {{ number_format($totalRooms, 0, ',', '.') }} total active rooms.
                </p>
            </div>
        </div>
        @endforeach
    </section>
</div>
@endsection


