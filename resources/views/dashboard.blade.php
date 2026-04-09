@extends('layouts.app')

@section('title', '')

@section('content')
<style>
    .content-wrapper {
        background: radial-gradient(circle at top right, rgba(183,148,92,.12), transparent 22%), radial-gradient(circle at left top, rgba(17,24,39,.08), transparent 28%), linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%);
        min-height: 100vh;
    }

    .content-wrapper > h3 {
        display: none;
    }

    .dashboard-page {
        padding: 1rem 0 2rem;
        color: #1c2937;
    }

    .dashboard-hero {
        margin-bottom: 1.25rem;
        color: #1c2937;
    }


    .dashboard-hero h1 {
        margin: 0;
        font-size: 2.7rem;
        font-weight: 700;
        line-height: 1.04;
        color: #162536;
        letter-spacing: -0.03em;
    }

    .dashboard-summary-card {
        background: linear-gradient(180deg, #133a9c 0%, #1653c9 58%, #1d68e2 100%);
        box-shadow: 0 22px 48px rgba(6, 24, 72, 0.34);
        border: 1px solid rgba(255,255,255,0.10);
    }

    .dashboard-summary-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
        pointer-events: none;
    }

    .dashboard-summary-card::after {
        width: 170px;
        height: 170px;
        right: -34px;
        bottom: -58px;
        background: radial-gradient(circle, rgba(50, 180, 255, 0.92) 0%, rgba(50, 180, 255, 0.82) 48%, rgba(50, 180, 255, 0) 100%);
    }

    .dashboard-summary-stack {
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: space-between;
        position: relative;
        z-index: 1;
    }

    .dashboard-summary-block {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .dashboard-summary-block + .dashboard-summary-block {
        margin-top: 1.35rem;
        padding-top: 1.35rem;
        border-top: 1px solid rgba(255,255,255,0.10);
    }

    .dashboard-summary-label {
        font-size: 0.74rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.72);
        font-weight: 700;
    }

    .dashboard-summary-value {
        font-size: 2.45rem;
        line-height: 1;
        font-weight: 700;
        color: #fff;
    }

    .metric-card {
        border-radius: 24px;
        padding: 1.5rem;
        color: #fff;
        box-shadow: 0 22px 48px rgba(6, 12, 20, 0.3);
        position: relative;
        overflow: hidden;
        min-height: 220px;
        height: 100%;
        border: 1px solid rgba(255,255,255,0.05);
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

    .metric-card.dirty { background: linear-gradient(135deg, #9b6a1a, #d8a23f 58%, #f4d47b 145%); }
    .metric-card.ready { background: linear-gradient(135deg, #0d6f4b, #25b37f 58%, #79e3ba 145%); }
    .metric-card.clean { background: linear-gradient(135deg, #17634f, #2aa078 58%, #65cfaa 140%); }
    .metric-card.renovated { background: linear-gradient(135deg, #5c3a7d, #8f63ba 58%, #c3a4ef 145%); }
    .metric-card.out-of-order { background: linear-gradient(135deg, #3f3072, #654db5 58%, #9d89ee 145%); }

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

    .dashboard-group-card--occupied {
        background: linear-gradient(145deg, #8b0f2b 0%, #ff1f2f 52%, #ff6d7a 100%);
    }

    .dashboard-group-card--vacant {
        background: linear-gradient(145deg, #148360 0%, #2fc18d 52%, #73dbb3 100%);
    }

    .dashboard-group-card--restricted {
        background: linear-gradient(145deg, #694296 0%, #8d65ba 52%, #b08add 100%);
    }

    .dashboard-group-card--vacant .dashboard-group-item {
        border-radius: 18px;
        padding: 0.18rem 0.45rem 0.35rem;
    }

    .dashboard-group-card--vacant .dashboard-group-item + .dashboard-group-item {
        margin-top: 0.18rem;
        padding-top: 0.58rem;
        border-top: 0;
    }

    .dashboard-group-card--vacant .dashboard-group-item--vacant_ready {
        background: linear-gradient(135deg, rgba(9, 109, 76, 0.96) 0%, rgba(21, 159, 110, 0.92) 58%, rgba(77, 215, 159, 0.88) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
    }

    .dashboard-group-card--vacant .dashboard-group-item--vacant_clean {
        background: linear-gradient(135deg, rgba(30, 145, 114, 0.76) 0%, rgba(73, 191, 151, 0.72) 58%, rgba(139, 230, 196, 0.68) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.07);
    }

    .dashboard-group-card--vacant .dashboard-group-item--vacant_dirty {
        background: linear-gradient(135deg, rgba(176, 132, 38, 0.88) 0%, rgba(223, 176, 73, 0.82) 58%, rgba(245, 212, 128, 0.76) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.07);
    }

    .dashboard-group-stack {
        display: flex;
        flex-direction: column;
        height: 100%;
        gap: 0.4rem;
    }

    .dashboard-group-item {
        padding: 0;
    }

    .dashboard-group-item + .dashboard-group-item {
        padding-top: 0.5rem;
        border-top: 1px solid rgba(255,255,255,0.28);
    }

    .dashboard-group-card--occupied .dashboard-group-item {
        border-radius: 18px;
        padding: 0.18rem 0.45rem 0.35rem;
    }

    .dashboard-group-card--occupied .dashboard-group-item + .dashboard-group-item {
        margin-top: 0.18rem;
        padding-top: 0.58rem;
        border-top: 0;
    }

    .dashboard-group-card--occupied .dashboard-group-item--occupied {
        background: linear-gradient(135deg, rgba(101, 4, 24, 0.98) 0%, rgba(175, 11, 41, 0.94) 58%, rgba(235, 53, 80, 0.90) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
    }

    .dashboard-group-card--occupied .dashboard-group-item--owner_unit {
        background: linear-gradient(135deg, rgba(134, 17, 40, 0.86) 0%, rgba(208, 48, 72, 0.82) 58%, rgba(247, 110, 132, 0.78) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.07);
    }

    .dashboard-group-card--occupied .dashboard-group-item--complimentary {
        background: linear-gradient(135deg, rgba(175, 53, 79, 0.74) 0%, rgba(233, 101, 124, 0.70) 58%, rgba(255, 159, 176, 0.66) 100%);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
    }

    .dashboard-group-item .metric-label {
        margin-bottom: 0;
        padding: 0.32rem 0.62rem;
        font-size: 0.62rem;
        letter-spacing: 0.1em;
    }

    .dashboard-group-item .metric-icon {
        width: 20px;
        height: 20px;
        font-size: 0.72rem;
        border-radius: 6px;
    }

    .dashboard-group-stats {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.65rem;
        margin-top: 0.42rem;
    }

    .dashboard-group-item .metric-value {
        margin-top: 0;
        font-size: 1.9rem;
        line-height: 0.95;
    }

    .dashboard-group-item .metric-percent {
        margin-top: 0;
        flex-shrink: 0;
        font-size: 0.7rem;
        line-height: 1;
        padding-bottom: 0.16rem;
    }

    .dashboard-group-item .metric-progress {
        margin-top: 0.45rem;
        height: 7px;
    }

    .dashboard-shell {
        border: 1px solid rgba(199,165,106,.58);
        border-radius: 28px;
        background: linear-gradient(180deg, rgba(255,252,246,.98), rgba(255,255,255,.96));
        box-shadow: 0 24px 60px rgba(125,96,42,.10), inset 0 1px 0 rgba(255,255,255,.7);
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .dashboard-shell-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(199,165,106,.22);
        background: linear-gradient(180deg, rgba(232,215,174,.24), rgba(255,251,244,.72));
    }

    .dashboard-shell-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #284670;
    }

    .dashboard-shell-subtitle {
        margin: 0.3rem 0 0;
        color: #6b7b90;
        font-size: 0.92rem;
    }

    .dashboard-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.5rem 0.8rem;
        background: rgba(199,165,106,.09);
        color: #8f6a2d;
        font-weight: 700;
        font-size: 0.82rem;
        border: 1px dashed rgba(199,165,106,.45);
    }

    .breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(16,35,59,.06);
        background: rgba(255,255,255,.72);
    }

    .breakdown-item:nth-child(odd) {
        background: rgba(16,35,59,.03);
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
        background: rgba(16,35,59,.08);
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
        background: rgba(23,55,97,.08);
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
        border: 0;
        background: transparent;
        cursor: pointer;
        transition: color 0.18s ease, transform 0.18s ease;
    }

    .breakdown-more:hover {
        color: #173761;
        transform: translateY(-1px);
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
        .dashboard-hero h1 { font-size: 2rem; }
        .metric-value { font-size: 2.7rem; }
        .dashboard-group-item .metric-value { font-size: 1.75rem; }
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

    $metricGroups = [
        [
            'card_class' => 'dashboard-group-card--occupied',
            'keys' => ['occupied', 'owner_unit', 'complimentary'],
        ],
        [
            'card_class' => 'dashboard-group-card--vacant',
            'keys' => ['vacant_ready', 'vacant_clean', 'vacant_dirty'],
        ],
        [
            'card_class' => 'dashboard-group-card--restricted',
            'keys' => ['renovated', 'out_of_order'],
        ],
    ];

    $groupedMetricKeys = collect($metricGroups)
        ->flatMap(fn ($group) => $group['keys'])
        ->values()
        ->all();

    $dashboardGroups = collect($metricGroups)
        ->map(function ($group) use ($metrics) {
            $metricOrder = array_flip($group['keys']);

            $items = collect($metrics)
                ->filter(fn ($metric) => in_array($metric['key'], $group['keys'], true))
                ->sortBy(fn ($metric) => $metricOrder[$metric['key']] ?? 99)
                ->values();

            return [
                'card_class' => $group['card_class'],
                'items' => $items,
            ];
        })
        ->filter(fn ($group) => $group['items']->isNotEmpty())
        ->values();

    $regularMetrics = collect($metrics)
        ->reject(fn ($metric) => in_array($metric['key'], $groupedMetricKeys, true))
        ->values();
@endphp

<div class="container-fluid dashboard-page">
    <section class="dashboard-hero">
        <h1>DASHBOARD</h1>
    </section>

    <section class="row">
        <div class="col-xl col-md-6 mb-4">
            <div class="metric-card dashboard-summary-card">
                <div class="dashboard-summary-stack">
                    <div class="dashboard-summary-block">
                        <span class="dashboard-summary-label">Total Active Rooms</span>
                        <span class="dashboard-summary-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
                    </div>
                    <div class="dashboard-summary-block">
                        <span class="dashboard-summary-label">Operational Base</span>
                        <span class="dashboard-summary-value">{{ number_format($operationalBase, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @foreach($dashboardGroups as $group)
        <div class="col-xl col-md-6 mb-4">
            <div class="metric-card {{ $group['card_class'] }}">
                <div class="dashboard-group-stack">
                    @foreach($group['items'] as $metric)
                    <article class="dashboard-group-item dashboard-group-item--{{ $metric['key'] }}">
                        <span class="metric-label">
                            <span class="metric-icon">{!! $metricIcons[$metric['key']] ?? '&#9632;' !!}</span>
                            <span>{{ $metric['label'] }}</span>
                        </span>
                        <div class="dashboard-group-stats">
                            <div class="metric-value">{{ number_format($metric['count'], 0, ',', '.') }}</div>
                            <div class="metric-percent">{{ number_format($metric['percentage'], 2) }}%</div>
                        </div>
                        <div class="metric-progress">
                            <div class="metric-progress-bar" style="width: {{ min($metric['percentage'], 100) }}%;"></div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        @foreach($regularMetrics as $metric)
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
                <p class="dashboard-shell-subtitle">Grouped by room condition and stay-length rules for easier monitoring.</p>
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
                        <div class="breakdown-codes" data-breakdown-codes>
                            @foreach(array_slice($item['rooms'], 0, 8) as $roomCode)
                            <span class="breakdown-code">{{ $roomCode }}</span>
                            @endforeach
                            @foreach(array_slice($item['rooms'], 8) as $roomCode)
                            <span class="breakdown-code breakdown-code-extra" hidden>{{ $roomCode }}</span>
                            @endforeach
                            @if(count($item['rooms']) > 8)
                            <button type="button" class="breakdown-more" data-breakdown-toggle data-more-count="{{ count($item['rooms']) - 8 }}">+{{ count($item['rooms']) - 8 }} more</button>
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
<script>
document.querySelectorAll('[data-breakdown-toggle]').forEach(function(button){
    button.addEventListener('click', function(){
        const codesWrap = button.closest('[data-breakdown-codes]');
        if(!codesWrap){return;}
        const hiddenCodes = codesWrap.querySelectorAll('.breakdown-code-extra');
        const moreCount = button.getAttribute('data-more-count') || '0';
        const isExpanded = button.getAttribute('data-expanded') === '1';

        hiddenCodes.forEach(function(code){
            code.hidden = isExpanded;
        });

        button.setAttribute('data-expanded', isExpanded ? '0' : '1');
        button.textContent = isExpanded ? '+' + moreCount + ' more' : 'Show less';
    });
});
</script>
@endsection



















