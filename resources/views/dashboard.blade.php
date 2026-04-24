@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="dashboard-topbar-brand">
        <span class="dashboard-topbar-icon"><i class="fas fa-chart-line"></i></span>
        <span>Dashboard</span>
    </div>
@endsection

@section('topbar_tools')
    <div class="dashboard-topbar-tools">
        <a href="/checkin" class="dashboard-topbar-action">
            <i class="fas fa-user-plus"></i>
            <span>New Check-In</span>
        </a>
        <a href="/room" class="dashboard-topbar-action dashboard-topbar-action-secondary">
            <i class="fas fa-bed"></i>
            <span>Room Directory</span>
        </a>
    </div>
@endsection

@section('content')
    @include('partials.crud-package-theme')

    @php
        $metricsByKey = $metrics->keyBy('key');
        $metricCount = fn($key) => (int) ($metricsByKey->get($key)['count'] ?? 0);
        $formatCount = fn($value) => number_format((int) $value, 0, ',', '.');
        $formatPercent = fn($value) => number_format((float) $value, 2, ',', '.');
        $formatMoney = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $formatSnapshotValue = function (array $row) use ($formatCount, $formatPercent, $formatMoney) {
            $value = $row['value'] ?? null;

            if ($value === null || $value === '') {
                return '-';
            }

            return match ($row['format'] ?? 'number') {
                'money' => $formatMoney($value),
                'percent' => $formatPercent($value) . '%',
                default => $formatCount($value),
            };
        };

        $occupiedCount = $metricCount('occupied');
        $complimentaryCount = $metricCount('complimentary');
        $ownerUnitCount = $metricCount('owner_unit');
        $vacantReadyCount = $metricCount('vacant_ready');
        $vacantCleanCount = $metricCount('vacant_clean');
        $vacantDirtyCount = $metricCount('vacant_dirty');
        $renovatedCount = $metricCount('renovated');
        $outOfOrderCount = $metricCount('out_of_order');
        $inHouseCount = $occupiedCount + $complimentaryCount + $ownerUnitCount;
        $availableCount = $vacantReadyCount + $vacantCleanCount;
        $restrictedCount = $renovatedCount + $outOfOrderCount;
        $sellableRooms = max((int) $operationalBase, 0);
        $totalBase = max((int) $totalRooms, 1);
        $occupancyRate = $totalRooms > 0 ? round(($inHouseCount / $totalBase) * 100, 2) : 0;
        $availableRate = $totalRooms > 0 ? round(($availableCount / $totalBase) * 100, 2) : 0;

        $occupiedBreakdownByLabel = $occupiedBreakdown->keyBy('label');
        $occupiedCleanCount = (int) ($occupiedBreakdownByLabel->get('Occupied Clean')['count'] ?? 0);
        $occupiedDirtyCount = (int) ($occupiedBreakdownByLabel->get('Occupied Dirty')['count'] ?? 0);
        $statusBarTotal = max($vacantReadyCount + $vacantCleanCount + $vacantDirtyCount + $occupiedCount + $complimentaryCount + $ownerUnitCount + $restrictedCount, 1);

        $dashboardCards = [
            [
                'label' => 'Total Rooms',
                'value' => $totalRooms,
                'note' => 'Inventory aktif',
                'icon' => 'fa-hotel',
                'tone' => 'primary',
            ],
            [
                'label' => 'Sellable Rooms',
                'value' => $sellableRooms,
                'note' => 'Base operasional',
                'icon' => 'fa-door-open',
                'tone' => 'sky',
            ],
            [
                'label' => 'In House',
                'value' => $inHouseCount,
                'note' => $formatPercent($occupancyRate) . '% occupancy',
                'icon' => 'fa-bed',
                'tone' => 'occupied',
            ],
            [
                'label' => 'Available',
                'value' => $availableCount,
                'note' => $formatPercent($availableRate) . '% dari total',
                'icon' => 'fa-key',
                'tone' => 'ready',
            ],
            [
                'label' => 'Vacant Dirty',
                'value' => $vacantDirtyCount,
                'note' => 'Perlu housekeeping',
                'icon' => 'fa-broom',
                'tone' => 'dirty',
            ],
            [
                'label' => 'Restricted',
                'value' => $restrictedCount,
                'note' => 'Renovated / OOO',
                'icon' => 'fa-triangle-exclamation',
                'tone' => 'restricted',
            ],
        ];

        $summaryRows = $todaySnapshot['rows'] ?? [];

        $statusBars = [
            ['label' => 'Ready', 'value' => $vacantReadyCount, 'class' => 'is-ready'],
            ['label' => 'Clean', 'value' => $vacantCleanCount, 'class' => 'is-clean'],
            ['label' => 'Dirty', 'value' => $vacantDirtyCount, 'class' => 'is-dirty'],
            ['label' => 'Occupied', 'value' => $occupiedCount, 'class' => 'is-occupied'],
            ['label' => 'Comp', 'value' => $complimentaryCount + $ownerUnitCount, 'class' => 'is-complimentary'],
            ['label' => 'Restricted', 'value' => $restrictedCount, 'class' => 'is-restricted'],
        ];

        $statusLabels = [
            'occupied' => 'Occupied',
            'complimentary' => 'Complimentary',
            'owner_unit' => 'Owner Unit',
            'vacant_ready' => 'Vacant Ready',
            'vacant_clean' => 'Vacant Clean',
            'vacant_dirty' => 'Vacant Dirty',
            'renovated' => 'Renovated',
            'out_of_order' => 'Out Of Order',
        ];

        $statusMeta = [
            'occupied' => ['icon' => 'fa-bed', 'tone' => 'occupied'],
            'complimentary' => ['icon' => 'fa-gift', 'tone' => 'complimentary'],
            'owner_unit' => ['icon' => 'fa-crown', 'tone' => 'owner'],
            'vacant_ready' => ['icon' => 'fa-key', 'tone' => 'ready'],
            'vacant_clean' => ['icon' => 'fa-circle-check', 'tone' => 'clean'],
            'vacant_dirty' => ['icon' => 'fa-broom', 'tone' => 'dirty'],
            'renovated' => ['icon' => 'fa-hammer', 'tone' => 'restricted'],
            'out_of_order' => ['icon' => 'fa-triangle-exclamation', 'tone' => 'restricted'],
        ];

        $statusGridOrder = [
            'occupied',
            'vacant_ready',
            'vacant_clean',
            'vacant_dirty',
            'complimentary',
            'owner_unit',
            'renovated',
            'out_of_order',
        ];
    @endphp

    <style>
        .dashboard-topbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            min-width: 0;
            color: var(--package-title, #173761);
            font-size: 1.55rem;
            line-height: 1;
            font-weight: 900;
            font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
        }

        .dashboard-topbar-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 8px;
            background: var(--package-badge-bg, rgba(30, 75, 128, 0.14));
            color: var(--package-badge-text, #173761);
            border: 1px solid var(--package-shell-border, rgba(30, 75, 128, 0.18));
            font-size: 0.95rem;
        }

        .dashboard-topbar-tools {
            display: flex;
            justify-content: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .dashboard-topbar-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 2.35rem;
            padding: 0.62rem 0.95rem;
            border-radius: 8px;
            background: var(--package-button-primary, linear-gradient(135deg, #173761 0%, #1e4b80 100%));
            color: #fff;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            border: 0;
            box-shadow: 0 12px 24px rgba(16, 35, 59, 0.14);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .dashboard-topbar-action:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(16, 35, 59, 0.18);
            text-decoration: none;
        }

        .dashboard-topbar-action-secondary {
            background: var(--package-button-secondary-bg, #fff);
            color: var(--package-button-secondary-text, #173761);
            border: 1px solid var(--package-input-border, rgba(30, 75, 128, 0.18));
            box-shadow: none;
        }

        .dashboard-topbar-action-secondary:hover {
            color: var(--package-button-secondary-text, #173761);
        }

        .dashboard-page {
            color: var(--package-text);
        }

        .dashboard-hero-shell {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: stretch;
            gap: 1rem;
            padding: 1.25rem;
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            background: var(--package-shell-bg);
            box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .dashboard-hero-main {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .dashboard-hero-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 4rem;
            height: 4rem;
            border-radius: 8px;
            background: var(--package-button-primary);
            color: #fff;
            box-shadow: 0 18px 34px rgba(16, 35, 59, 0.18);
            font-size: 1.35rem;
        }

        .dashboard-hero-copy {
            min-width: 0;
        }

        .dashboard-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.35rem;
            color: var(--package-label);
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .dashboard-hero-title {
            margin: 0;
            color: var(--package-title);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 2.2rem;
            line-height: 1;
            font-weight: 500;
        }

        .dashboard-hero-subtitle {
            margin: 0.45rem 0 0;
            color: var(--package-muted);
            font-size: 0.96rem;
            line-height: 1.45;
        }

        .dashboard-hero-date {
            display: grid;
            align-content: center;
            min-width: 190px;
            padding: 0.95rem 1.05rem;
            border-radius: 8px;
            background: var(--package-heading-bg);
            border: 1px solid var(--package-heading-border);
            color: var(--package-text);
            text-align: right;
        }

        .dashboard-date-label {
            color: var(--package-muted);
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .dashboard-date-value {
            margin-top: 0.2rem;
            color: var(--package-title);
            font-size: 1rem;
            font-weight: 900;
        }

        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(140px, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .dashboard-kpi-card {
            position: relative;
            overflow: hidden;
            min-height: 126px;
            display: grid;
            align-content: space-between;
            padding: 1rem;
            border-radius: 8px;
            background: var(--package-shell-bg);
            border: 1px solid var(--package-shell-border);
            box-shadow: 0 12px 28px rgba(16, 35, 59, 0.07), inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }

        .dashboard-kpi-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-top: 4px solid var(--status-accent, var(--package-table-hover-accent));
            opacity: 0.95;
        }

        .dashboard-kpi-top {
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .dashboard-kpi-label {
            margin: 0;
            color: var(--package-muted);
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.25;
        }

        .dashboard-kpi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 8px;
            background: color-mix(in srgb, var(--status-accent, #1e4b80) 14%, white);
            color: var(--status-accent, #1e4b80);
            border: 1px solid color-mix(in srgb, var(--status-accent, #1e4b80) 24%, white);
        }

        .dashboard-kpi-value {
            position: relative;
            margin-top: 1.05rem;
            color: var(--package-text);
            font-size: 2.35rem;
            line-height: 0.92;
            font-weight: 300;
        }

        .dashboard-kpi-note {
            position: relative;
            margin-top: 0.45rem;
            color: var(--package-muted);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .dashboard-tone-primary {
            --status-accent: var(--package-table-hover-accent, #1e4b80);
        }

        .dashboard-tone-sky {
            --status-accent: #0ea5e9;
        }

        .dashboard-tone-occupied {
            --status-accent: #2563eb;
        }

        .dashboard-tone-ready {
            --status-accent: #059669;
        }

        .dashboard-tone-clean {
            --status-accent: #14b8a6;
        }

        .dashboard-tone-dirty {
            --status-accent: #d97706;
        }

        .dashboard-tone-restricted {
            --status-accent: #dc2626;
        }

        .dashboard-tone-complimentary {
            --status-accent: #a855f7;
        }

        .dashboard-tone-owner {
            --status-accent: #64748b;
        }

        .dashboard-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 0.33fr);
            gap: 1rem;
            margin-top: 1rem;
            align-items: start;
        }

        .dashboard-panel {
            overflow: hidden;
            border-radius: 8px;
            background: var(--package-shell-bg);
            border: 1px solid var(--package-shell-border);
            box-shadow: 0 14px 30px rgba(16, 35, 59, 0.07), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .dashboard-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.15rem;
            background: var(--package-header-bg);
            border-bottom: 1px solid var(--package-shell-border);
        }

        .dashboard-panel-title {
            margin: 0;
            color: var(--package-title);
            font-size: 1rem;
            font-weight: 900;
        }

        .dashboard-panel-meta {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.65rem;
            border-radius: 8px;
            background: var(--package-badge-bg);
            border: 1px solid var(--package-shell-border);
            color: var(--package-badge-text);
            font-size: 0.74rem;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dashboard-panel-body {
            padding: 1.15rem;
        }

        .dashboard-status-chart {
            display: grid;
            gap: 0.95rem;
        }

        .dashboard-chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem 0.8rem;
        }

        .dashboard-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            color: var(--package-muted);
            font-size: 0.78rem;
            font-weight: 800;
        }

        .dashboard-legend-dot {
            width: 0.62rem;
            height: 0.62rem;
            border-radius: 999px;
            background: var(--bar-color);
        }

        .dashboard-stacked-bar {
            display: flex;
            width: 100%;
            min-height: 2.4rem;
            overflow: hidden;
            border-radius: 8px;
            background: rgba(16, 35, 59, 0.08);
            border: 1px solid var(--package-shell-border);
        }

        .dashboard-bar-segment {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 2px;
            width: max(var(--bar-width), 2px);
            background: var(--bar-color);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 900;
            text-shadow: 0 1px 1px rgba(16, 35, 59, 0.24);
        }

        .dashboard-bar-segment.is-empty {
            min-width: 0;
            width: 0;
        }

        .dashboard-chart-scale {
            display: flex;
            justify-content: space-between;
            color: var(--package-muted);
            font-size: 0.76rem;
            font-weight: 700;
        }

        .dashboard-breakdown-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .dashboard-breakdown-card {
            padding: 0.9rem;
            border-radius: 8px;
            background: var(--package-heading-bg);
            border: 1px solid var(--package-heading-border);
        }

        .dashboard-breakdown-label {
            color: var(--package-muted);
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .dashboard-breakdown-value {
            margin-top: 0.35rem;
            color: var(--package-text);
            font-size: 1.45rem;
            line-height: 1;
            font-weight: 800;
        }

        .dashboard-summary-list {
            display: grid;
        }

        .dashboard-summary-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) max-content;
            gap: 1rem;
            align-items: center;
            min-width: 0;
            padding: 0.66rem 0;
        }

        .dashboard-summary-row + .dashboard-summary-row {
            border-top: 1px solid var(--package-shell-border);
        }

        .dashboard-summary-label {
            min-width: 0;
            color: var(--package-text);
            font-size: 0.86rem;
            font-weight: 700;
        }

        .dashboard-summary-value {
            color: var(--status-accent, var(--package-title));
            font-size: 0.9rem;
            font-weight: 900;
            text-align: right;
            white-space: nowrap;
        }

        .dashboard-directory-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .dashboard-room-card {
            overflow: hidden;
            border-radius: 8px;
            background: var(--package-shell-bg);
            border: 1px solid var(--package-shell-border);
            box-shadow: 0 10px 24px rgba(16, 35, 59, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.72);
        }

        .dashboard-room-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            padding: 0.85rem 0.95rem;
            border-bottom: 1px solid var(--package-shell-border);
            background: var(--package-table-head-bg);
        }

        .dashboard-room-title {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
            color: var(--package-title);
            font-size: 0.92rem;
            font-weight: 900;
        }

        .dashboard-room-title i {
            color: var(--status-accent, var(--package-title));
        }

        .dashboard-room-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.35rem;
            padding: 0.28rem 0.55rem;
            border-radius: 8px;
            background: color-mix(in srgb, var(--status-accent, #1e4b80) 14%, white);
            border: 1px solid color-mix(in srgb, var(--status-accent, #1e4b80) 24%, white);
            color: var(--status-accent, var(--package-title));
            font-size: 0.82rem;
            font-weight: 900;
        }

        .dashboard-room-body {
            padding: 0.9rem;
        }

        .dashboard-room-list {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.42rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .dashboard-room-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 3.15rem;
            min-height: 2rem;
            padding: 0.32rem 0.6rem;
            border-radius: 8px;
            background: color-mix(in srgb, var(--status-accent, #1e4b80) 10%, white);
            border: 1px solid color-mix(in srgb, var(--status-accent, #1e4b80) 20%, white);
            color: var(--package-text);
            font-size: 0.82rem;
            font-weight: 900;
        }

        .dashboard-room-pill.is-extra {
            display: none;
        }

        .dashboard-room-list.is-expanded .dashboard-room-pill.is-extra {
            display: inline-flex;
        }

        .dashboard-room-empty {
            color: var(--package-muted);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .dashboard-room-toggle-wrap {
            display: none;
        }

        .dashboard-room-toggle-wrap.is-visible {
            display: inline-flex;
        }

        .dashboard-room-toggle {
            min-height: 2rem;
            padding: 0.32rem 0.65rem;
            border-radius: 8px;
            border: 1px dashed var(--package-input-border);
            background: var(--package-input-bg);
            color: var(--package-title);
            font-size: 0.78rem;
            font-weight: 900;
            cursor: pointer;
        }

        .dashboard-room-toggle:hover {
            background: var(--package-badge-bg);
        }

        .is-ready {
            --bar-color: #059669;
        }

        .is-clean {
            --bar-color: #14b8a6;
        }

        .is-dirty {
            --bar-color: #d97706;
        }

        .is-occupied {
            --bar-color: #2563eb;
        }

        .is-complimentary {
            --bar-color: #a855f7;
        }

        .is-restricted {
            --bar-color: #dc2626;
        }

        @media (max-width: 1199.98px) {
            .dashboard-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .dashboard-main-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .dashboard-hero-shell {
                grid-template-columns: 1fr;
            }

            .dashboard-hero-date {
                text-align: left;
            }

            .dashboard-directory-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .dashboard-topbar-brand {
                font-size: 1.12rem;
            }

            .dashboard-topbar-tools {
                justify-content: stretch;
            }

            .dashboard-topbar-action {
                flex: 1 1 160px;
            }

            .dashboard-hero-shell {
                padding: 0.9rem;
            }

            .dashboard-hero-main {
                align-items: flex-start;
            }

            .dashboard-hero-mark {
                width: 3.2rem;
                height: 3.2rem;
            }

            .dashboard-hero-title {
                font-size: 1.65rem;
            }

            .dashboard-kpi-grid,
            .dashboard-breakdown-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-kpi-card {
                min-height: 112px;
            }

            .dashboard-panel-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <div class="dashboard-page">
        <section class="dashboard-hero-shell">
            <div class="dashboard-hero-main">
                <div class="dashboard-hero-mark">
                    <i class="fas fa-desktop"></i>
                </div>
                <div class="dashboard-hero-copy">
                    <div class="dashboard-eyebrow">
                        <i class="fas fa-bolt"></i>
                        Front Office Live View
                    </div>
                    <h1 class="dashboard-hero-title">Room Status Dashboard</h1>
                    <p class="dashboard-hero-subtitle">
                        Snapshot okupansi, availability, dan housekeeping room untuk pengambilan keputusan cepat.
                    </p>
                </div>
            </div>
            <div class="dashboard-hero-date">
                <span class="dashboard-date-label">Business Date</span>
                <span class="dashboard-date-value">{{ now()->format('d M Y') }}</span>
            </div>
        </section>

        <section class="dashboard-kpi-grid" aria-label="Dashboard metrics">
            @foreach ($dashboardCards as $card)
                <article class="dashboard-kpi-card dashboard-tone-{{ $card['tone'] }}">
                    <div class="dashboard-kpi-top">
                        <p class="dashboard-kpi-label">{{ $card['label'] }}</p>
                        <span class="dashboard-kpi-icon"><i class="fas {{ $card['icon'] }}"></i></span>
                    </div>
                    <div>
                        <div class="dashboard-kpi-value">{{ $formatCount($card['value']) }}</div>
                        <div class="dashboard-kpi-note">{{ $card['note'] }}</div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-main-grid">
            <div class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h2 class="dashboard-panel-title">Clean / Dirty Status</h2>
                    <span class="dashboard-panel-meta">
                        <i class="fas fa-layer-group"></i>
                        {{ $formatCount($statusBarTotal) }} tracked
                    </span>
                </div>
                <div class="dashboard-panel-body">
                    <div class="dashboard-status-chart">
                        <div class="dashboard-chart-legend">
                            @foreach ($statusBars as $bar)
                                <span class="dashboard-legend-item {{ $bar['class'] }}">
                                    <span class="dashboard-legend-dot"></span>
                                    {{ $bar['label'] }} {{ $formatCount($bar['value']) }}
                                </span>
                            @endforeach
                        </div>
                        <div class="dashboard-stacked-bar" aria-label="Room status chart">
                            @foreach ($statusBars as $bar)
                                @php
                                    $barWidth = $bar['value'] > 0 ? round(($bar['value'] / $statusBarTotal) * 100, 4) : 0;
                                @endphp
                                <div class="dashboard-bar-segment {{ $bar['class'] }} {{ $bar['value'] === 0 ? 'is-empty' : '' }}"
                                    style="--bar-width: {{ $barWidth }}%;"
                                    title="{{ $bar['label'] }}: {{ $formatCount($bar['value']) }}">
                                    @if ($barWidth >= 7)
                                        {{ $formatCount($bar['value']) }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="dashboard-chart-scale">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>

                    <div class="dashboard-breakdown-grid">
                        <div class="dashboard-breakdown-card">
                            <div class="dashboard-breakdown-label">Occupied Clean</div>
                            <div class="dashboard-breakdown-value">{{ $formatCount($occupiedCleanCount) }}</div>
                        </div>
                        <div class="dashboard-breakdown-card">
                            <div class="dashboard-breakdown-label">Occupied Dirty</div>
                            <div class="dashboard-breakdown-value">{{ $formatCount($occupiedDirtyCount) }}</div>
                        </div>
                        <div class="dashboard-breakdown-card">
                            <div class="dashboard-breakdown-label">Ready + Clean</div>
                            <div class="dashboard-breakdown-value">{{ $formatCount($availableCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h2 class="dashboard-panel-title">Today Snapshot</h2>
                    <span class="dashboard-panel-meta">
                        <i class="fas fa-circle-info"></i>
                        {{ $todaySnapshot['source_label'] ?? 'Live DATA2' }}
                    </span>
                </div>
                <div class="dashboard-panel-body">
                    <div class="dashboard-summary-list">
                        @foreach ($summaryRows as $row)
                            <div class="dashboard-summary-row dashboard-tone-{{ $row['tone'] }}">
                                <div class="dashboard-summary-label">{{ $row['label'] }}</div>
                                <div class="dashboard-summary-value">{{ $formatSnapshotValue($row) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </section>

        <section class="dashboard-directory-grid" aria-label="Room status directory">
            @foreach ($statusGridOrder as $statusKey)
                @php
                    $rooms = collect($statusRoomLists[$statusKey] ?? [])->filter()->values();
                    $meta = $statusMeta[$statusKey] ?? ['icon' => 'fa-square', 'tone' => 'primary'];
                @endphp
                <article class="dashboard-room-card dashboard-tone-{{ $meta['tone'] }}">
                    <div class="dashboard-room-head">
                        <div class="dashboard-room-title">
                            <i class="fas {{ $meta['icon'] }}"></i>
                            <span>{{ $statusLabels[$statusKey] ?? $statusKey }}</span>
                        </div>
                        <span class="dashboard-room-count">{{ $formatCount($rooms->count()) }}</span>
                    </div>
                    <div class="dashboard-room-body">
                        @if ($rooms->isEmpty())
                            <div class="dashboard-room-empty">No rooms</div>
                        @else
                            <ul class="dashboard-room-list is-collapsed" data-room-list data-status="{{ $statusKey }}">
                                @foreach ($rooms as $roomCode)
                                    <li class="dashboard-room-pill">{{ $roomCode }}</li>
                                @endforeach
                                @if ($rooms->count() > 1)
                                    <li class="dashboard-room-toggle-wrap">
                                        <button type="button" class="dashboard-room-toggle" data-toggle="room-list"
                                            data-status="{{ $statusKey }}" data-more-label="more +"
                                            data-less-label="less -" aria-expanded="false">
                                            <span class="btn-text">more +</span>
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const moreButtons = document.querySelectorAll('[data-toggle="room-list"]');
            const roomLists = document.querySelectorAll('[data-room-list]');
            const DESKTOP_LIMIT = 18;

            const updateButtonLabel = (button, expanded) => {
                const label = expanded ? button.dataset.lessLabel : button.dataset.moreLabel;
                button.querySelector('.btn-text').textContent = label;
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            };

            const syncRoomLists = () => {
                roomLists.forEach(list => {
                    const statusKey = list.getAttribute('data-status');
                    const button = document.querySelector(
                        `[data-toggle="room-list"][data-status="${statusKey}"]`);

                    if (!button) {
                        return;
                    }

                    const expanded = list.classList.contains('is-expanded');
                    const items = Array.from(list.querySelectorAll('.dashboard-room-pill'));
                    const buttonWrap = button.closest('.dashboard-room-toggle-wrap');
                    let hasExtra = false;

                    list.classList.remove('is-collapsed', 'is-expanded');
                    items.forEach(item => item.classList.remove('is-extra'));

                    if (items.length > DESKTOP_LIMIT) {
                        items.slice(DESKTOP_LIMIT).forEach(item => item.classList.add('is-extra'));
                        hasExtra = true;
                    }

                    if (!hasExtra) {
                        buttonWrap.classList.remove('is-visible');
                        updateButtonLabel(button, false);
                        return;
                    }

                    buttonWrap.classList.add('is-visible');
                    list.classList.toggle('is-expanded', expanded);
                    list.classList.toggle('is-collapsed', !expanded);
                    updateButtonLabel(button, expanded);
                });
            };

            moreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const statusKey = this.getAttribute('data-status');
                    const roomList = document.querySelector(
                        `[data-room-list][data-status="${statusKey}"]`);

                    if (!roomList) {
                        return;
                    }

                    const expanded = roomList.classList.contains('is-expanded');
                    roomList.classList.toggle('is-expanded', !expanded);
                    roomList.classList.toggle('is-collapsed', expanded);
                    updateButtonLabel(this, !expanded);
                });
            });

            syncRoomLists();
            window.addEventListener('resize', syncRoomLists);
        });
    </script>
@endsection
