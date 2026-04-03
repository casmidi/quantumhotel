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
        width: 260px;
        height: 260px;
        border-radius: 50%;
        top: -110px;
        right: -60px;
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

    .dashboard-card {
        border: 1px solid rgba(255,255,255,0.55);
        border-radius: 24px;
        background: rgba(255,255,255,0.78);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(16, 35, 59, 0.08);
        overflow: hidden;
        height: 100%;
    }

    .status-card {
        padding: 1.35rem;
        position: relative;
        min-height: 180px;
    }

    .status-card::after {
        content: '';
        position: absolute;
        right: -40px;
        bottom: -40px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(255,255,255,0.12);
    }

    .status-label {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-value {
        position: relative;
        z-index: 1;
        margin-top: 1rem;
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
    }

    .status-copy {
        position: relative;
        z-index: 1;
        margin-top: 0.85rem;
        max-width: 240px;
        color: rgba(255,255,255,0.82);
        line-height: 1.6;
        font-size: 0.92rem;
    }

    .status-occupied { background: linear-gradient(135deg, #0f2742, #1c4f80); color: #fff; }
    .status-dirty { background: linear-gradient(135deg, #6d2b2b, #b14d4d); color: #fff; }
    .status-clean { background: linear-gradient(135deg, #17634f, #2aa078); color: #fff; }
    .status-renovated { background: linear-gradient(135deg, #6b4b18, #b88431); color: #fff; }
    .status-ooo { background: linear-gradient(135deg, #4a3c6e, #785eb0); color: #fff; }

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

    .breakdown-table,
    .highlight-table {
        margin-bottom: 0;
    }

    .breakdown-table thead th,
    .highlight-table thead th {
        border-top: 0;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
        background: rgba(16, 35, 59, 0.03);
        color: #5b6c82;
        font-size: 0.76rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        padding: 1rem 1.2rem;
    }

    .breakdown-table tbody td,
    .highlight-table tbody td {
        padding: 1rem 1.2rem;
        border-top: 1px solid rgba(16, 35, 59, 0.06);
        vertical-align: middle;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: rgba(16, 35, 59, 0.08);
        font-weight: 700;
        color: #173761;
    }

    .status-pill.occupied { background: rgba(23, 55, 97, 0.1); color: #173761; }
    .status-pill.vacant-dirty { background: rgba(177, 77, 77, 0.12); color: #9c3636; }
    .status-pill.vacant-clean { background: rgba(42, 160, 120, 0.14); color: #1e7f5d; }
    .status-pill.renovated { background: rgba(184, 132, 49, 0.16); color: #8d6115; }
    .status-pill.out-of-order { background: rgba(120, 94, 176, 0.14); color: #61459d; }

    .room-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: rgba(16, 35, 59, 0.08);
        color: #173761;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .dashboard-hero h1 { font-size: 1.9rem; }
    }
</style>

<div class="container-fluid dashboard-page">
    <section class="dashboard-hero">
        <div class="dashboard-kicker">Quantum Hotel Dashboard</div>
        <h1>Five-Star Room Status Control Center</h1>
        <p>The dashboard below follows the legacy VB6 room-status logic and converts it into a modern Laravel overview, so the operational counts stay familiar while the presentation becomes far more executive-ready.</p>
        <div class="dashboard-total">
            <span class="dashboard-total-label">Total Active Rooms</span>
            <span class="dashboard-total-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
        </div>
    </section>

    <section class="row">
        <div class="col-xl col-md-6 mb-4">
            <div class="dashboard-card status-card status-occupied">
                <span class="status-label">Occupied</span>
                <div class="status-value">{{ number_format($dashboardCounts['occupied'], 0, ',', '.') }}</div>
                <p class="status-copy">Rooms currently occupied by in-house guests based on the joined DATA2 stay records.</p>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-4">
            <div class="dashboard-card status-card status-dirty">
                <span class="status-label">Vacant Dirty</span>
                <div class="status-value">{{ number_format($dashboardCounts['vacant_dirty'], 0, ',', '.') }}</div>
                <p class="status-copy">Rooms that require housekeeping attention, including the legacy “Check Out” conversion.</p>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-4">
            <div class="dashboard-card status-card status-clean">
                <span class="status-label">Vacant Clean</span>
                <div class="status-value">{{ number_format($dashboardCounts['vacant_clean'], 0, ',', '.') }}</div>
                <p class="status-copy">Clean and ready inventory, including both “Vacant Clean” and “Vacant Ready” statuses.</p>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-4">
            <div class="dashboard-card status-card status-renovated">
                <span class="status-label">Renovated</span>
                <div class="status-value">{{ number_format($dashboardCounts['renovated'], 0, ',', '.') }}</div>
                <p class="status-copy">Inventory currently classified as renovated and not available for immediate selling.</p>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-4">
            <div class="dashboard-card status-card status-ooo">
                <span class="status-label">Out Of Order</span>
                <div class="status-value">{{ number_format($dashboardCounts['out_of_order'], 0, ',', '.') }}</div>
                <p class="status-copy">Rooms blocked operationally because they are marked as out of order in the room master.</p>
            </div>
        </div>
    </section>

    <section class="row">
        <div class="col-lg-5 mb-4">
            <div class="dashboard-shell h-100">
                <div class="dashboard-shell-header">
                    <div>
                        <h2 class="dashboard-shell-title">Status Breakdown</h2>
                        <p class="dashboard-shell-subtitle">Distribution of all room statuses returned by the converted legacy query.</p>
                    </div>
                    <span class="dashboard-badge">VB6 Logic</span>
                </div>

                <div class="table-responsive">
                    <table class="table breakdown-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-right">Rooms</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statusBreakdown as $item)
                            <tr>
                                <td>{{ $item['label'] }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($item['count'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No room status data available.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="dashboard-shell h-100">
                <div class="dashboard-shell-header">
                    <div>
                        <h2 class="dashboard-shell-title">Operational Highlights</h2>
                        <p class="dashboard-shell-subtitle">Priority rooms that are occupied or need operational attention right now.</p>
                    </div>
                    <span class="dashboard-badge">Top 8 Rooms</span>
                </div>

                <div class="table-responsive">
                    <table class="table highlight-table">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Guest</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roomHighlights as $room)
                            @php
                                $statusClass = strtolower(str_replace([' ', '/'], ['-', '-'], $room->Status));
                            @endphp
                            <tr>
                                <td><span class="room-code">{{ $room->Kode }}</span></td>
                                <td>{{ $room->Kelas }}</td>
                                <td><span class="status-pill {{ $statusClass }}">{{ $room->Status }}</span></td>
                                <td>{{ $room->Person ?: 'No active guest' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No highlighted rooms found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
