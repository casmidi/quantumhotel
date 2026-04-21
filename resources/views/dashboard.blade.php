@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $metricIcons = [
            'occupied' => '&#128719;',
            'complimentary' => '&#127873;',
            'owner_unit' => '&#128081;',
            'vacant_ready' => '&#9989;',
            'vacant_clean' => '&#10004;',
            'vacant_dirty' => '&#129532;',
            'renovated' => '&#128736;',
            'out_of_order' => '&#9888;',
        ];

        $metricGroups = [
            [
                'title' => 'Occupied Cluster',
                'card_class' => 'dashboard-group-card--occupied',
                'keys' => ['occupied', 'complimentary', 'owner_unit'],
            ],
            [
                'title' => 'Vacant Cluster',
                'card_class' => 'dashboard-group-card--vacant',
                'keys' => ['vacant_ready', 'vacant_clean', 'vacant_dirty'],
            ],
            [
                'title' => 'Restricted Cluster',
                'card_class' => 'dashboard-group-card--restricted',
                'keys' => ['renovated', 'out_of_order'],
            ],
        ];

        $metricsByKey = $metrics->keyBy('key');
        $dashboardGroups = collect($metricGroups)
            ->map(function ($group) use ($metricsByKey) {
                $items = collect($group['keys'])->map(fn($key) => $metricsByKey->get($key))->filter()->values();

                return [
                    'title' => $group['title'],
                    'card_class' => $group['card_class'],
                    'items' => $items,
                ];
            })
            ->filter(fn($group) => $group['items']->isNotEmpty())
            ->values();

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
        .dashboard-hero {
            border-radius: 16px;
            padding: 0.9rem 1.4rem;
            color: #fff;
            background: linear-gradient(125deg, #182f4a 0%, #2e5c86 50%, #4b8cb5 100%);
            box-shadow: 0 16px 36px rgba(16, 35, 59, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dashboard-hero h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .dashboard-totals {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .dashboard-total {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-total-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.8);
        }

        .dashboard-total-value {
            font-size: 1.7rem;
            font-weight: 700;
        }

        .dashboard-group-card {
            border-radius: 20px;
            padding: 1.1rem;
            color: #fff;
            box-shadow: 0 14px 34px rgba(16, 35, 59, 0.12);
            height: 100%;
        }

        .dashboard-group-card--occupied {
            background: linear-gradient(135deg, #6f1f24, #b93a42 58%, #e07177 140%);
        }

        .dashboard-group-card--vacant {
            background: linear-gradient(135deg, #0f6a45, #25a776 58%, #7ce1be 145%);
        }

        .dashboard-group-card--restricted {
            background: linear-gradient(135deg, #4c3477, #7b59b2 58%, #b99ae4 145%);
        }

        .dashboard-group-title {
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            margin-bottom: 0.8rem;
            opacity: 0.9;
        }

        .dashboard-group-item {
            padding: 0.85rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16);
            margin-bottom: 0.6rem;
        }

        .dashboard-group-item:last-child {
            margin-bottom: 0;
        }

        .dashboard-group-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
        }

        .dashboard-group-name {
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .dashboard-group-count {
            font-size: 1.8rem;
            line-height: 1;
            font-weight: 700;
        }

        .dashboard-group-pct {
            margin-top: 0.2rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .status-grid-card {
            border-radius: 18px;
            border: 1px solid rgba(16, 35, 59, 0.1);
            background: #fff;
            box-shadow: 0 10px 22px rgba(16, 35, 59, 0.06);
            overflow: hidden;
            height: 100%;
        }

        .status-grid-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(16, 35, 59, 0.08);
            background: #f8fbff;
            color: #15365c;
            font-weight: 700;
        }

        .status-grid-count {
            min-width: 2.2rem;
            padding: 0.26rem 0.62rem;
            border-radius: 999px;
            border: 1px solid rgba(18, 48, 84, 0.28);
            background: #e8f1ff;
            color: #0f2c4d;
            font-size: 0.82rem;
            font-weight: 800;
            text-align: center;
            line-height: 1.1;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.7);
        }

        .status-grid-list {
            margin: 0;
            padding: 0.8rem 1rem 0.95rem;
            list-style: none;
        }

        .status-grid-list-items {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .status-grid-item.is-extra {
            display: none;
        }

        .status-grid-list-items.is-expanded .status-grid-item.is-extra {
            display: inline-flex;
        }

        .status-grid-list-items.is-collapsed li.is-extra {
            display: none;
        }

        .status-grid-list li {
            padding: 0.28rem 0.6rem;
            border-radius: 999px;
            border: 1px solid rgba(24, 65, 110, 0.18);
            background: #f5f9ff;
            color: #173761;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .status-grid-empty {
            color: #798aa3;
            font-size: 0.88rem;
            font-style: italic;
        }

        .status-grid-toggle-wrap {
            display: none;
            padding: 0;
            border: none !important;
            background: none !important;
            list-style: none;
            align-items: center;
        }

        .status-grid-toggle-wrap.is-visible {
            display: inline-flex;
        }

        .status-grid-more-btn {
            padding: 0;
            border: none;
            background: none;
            color: #1a3f6f;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: color 0.15s ease;
        }

        .status-grid-more-btn:hover {
            color: #0d2442;
        }

        @media (max-width: 767.98px) {
            .dashboard-hero h1 {
                font-size: 1.6rem;
            }

            .dashboard-group-count {
                font-size: 1.45rem;
            }
        }
    </style>

    <div class="container-fluid">
        <section class="dashboard-hero mb-3">
            <h1>Room Status Overview</h1>
            <div class="dashboard-totals">
                <div class="dashboard-total">
                    <span class="dashboard-total-label">Total Rooms</span>
                    <span class="dashboard-total-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
                </div>
                <div class="dashboard-total">
                    <span class="dashboard-total-label">Operational Base</span>
                    <span class="dashboard-total-value">{{ number_format($operationalBase, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>

        <section class="row mb-4">
            @foreach ($dashboardGroups as $group)
                <div class="col-xl-4 col-md-6 mb-3">
                    <article class="dashboard-group-card {{ $group['card_class'] }}">
                        <div class="dashboard-group-title">{{ $group['title'] }}</div>
                        @foreach ($group['items'] as $metric)
                            <div class="dashboard-group-item">
                                <div class="dashboard-group-row">
                                    <div class="dashboard-group-name">{!! $metricIcons[$metric['key']] ?? '&#9632;' !!} {{ $metric['label'] }}</div>
                                    <div class="dashboard-group-count">{{ number_format($metric['count'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="dashboard-group-pct">{{ number_format($metric['percentage'], 2) }}%</div>
                            </div>
                        @endforeach
                    </article>
                </div>
            @endforeach
        </section>

        <section class="row">
            @foreach ($statusGridOrder as $statusKey)
                @php
                    $rooms = collect($statusRoomLists[$statusKey] ?? [])
                        ->filter()
                        ->values();
                    $colClass = 'col-12';
                @endphp
                <div class="{{ $colClass }} mb-3">
                    <article class="status-grid-card">
                        <div class="status-grid-head">
                            <span>{!! $metricIcons[$statusKey] ?? '&#9632;' !!} {{ $statusLabels[$statusKey] ?? $statusKey }}</span>
                            <span class="status-grid-count">{{ $rooms->count() }}</span>
                        </div>

                        @if ($rooms->isEmpty())
                            <div class="status-grid-list">
                                <ul style="margin: 0; padding: 0; list-style: none;">
                                    <li class="status-grid-empty">No rooms</li>
                                </ul>
                            </div>
                        @else
                            <div class="status-grid-list">
                                <ul class="status-grid-list-items is-collapsed" data-room-list
                                    data-status="{{ $statusKey }}">
                                    @foreach ($rooms as $roomCode)
                                        <li class="status-grid-item">{{ $roomCode }}</li>
                                    @endforeach
                                    @if ($rooms->count() > 1)
                                        <li class="status-grid-toggle-wrap">
                                            <button type="button" class="status-grid-more-btn"
                                                data-status="{{ $statusKey }}" data-toggle="room-list"
                                                data-more-label="more +" data-less-label="less -" aria-expanded="false">
                                                <span class="btn-text">more +</span>
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </article>
                </div>
            @endforeach
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const moreButtons = document.querySelectorAll('[data-toggle="room-list"]');
            const roomLists = document.querySelectorAll('[data-room-list]');

            const updateButtonLabel = (button, expanded) => {
                const label = expanded ? button.dataset.lessLabel : button.dataset.moreLabel;
                button.querySelector('.btn-text').textContent = label;
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            };

            const DESKTOP_LIMIT = 20;
            const isMobile = () => window.innerWidth < 768;

            const syncRoomLists = () => {
                roomLists.forEach(list => {
                    const statusKey = list.getAttribute('data-status');
                    const button = document.querySelector(
                        `[data-toggle="room-list"][data-status="${statusKey}"]`);

                    if (!button) {
                        return;
                    }

                    const expanded = list.classList.contains('is-expanded');
                    const items = Array.from(list.querySelectorAll('.status-grid-item'));

                    // Reset semua dulu
                    list.classList.remove('is-collapsed', 'is-expanded');
                    items.forEach(item => item.classList.remove('is-extra'));

                    if (items.length === 0) {
                        button.closest('.status-grid-toggle-wrap').classList.remove('is-visible');
                        return;
                    }

                    let hasExtra = false;

                    if (isMobile()) {
                        // Mobile: deteksi visual — extra = meluber ke baris kedua
                        const firstTop = items[0].getBoundingClientRect().top;
                        items.forEach(item => {
                            if (item.getBoundingClientRect().top > firstTop + 2) {
                                item.classList.add('is-extra');
                                hasExtra = true;
                            }
                        });
                    } else {
                        // Desktop: batasi 20 item per baris collapsed
                        if (items.length > DESKTOP_LIMIT) {
                            items.slice(DESKTOP_LIMIT).forEach(item => {
                                item.classList.add('is-extra');
                            });
                            hasExtra = true;
                        }
                    }

                    if (!hasExtra) {
                        button.closest('.status-grid-toggle-wrap').classList.remove('is-visible');
                        updateButtonLabel(button, false);
                        return;
                    }

                    button.closest('.status-grid-toggle-wrap').classList.add('is-visible');

                    if (expanded) {
                        list.classList.remove('is-collapsed');
                        list.classList.add('is-expanded');
                    } else {
                        list.classList.add('is-collapsed');
                        list.classList.remove('is-expanded');
                    }

                    updateButtonLabel(button, expanded);
                });
            };

            moreButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const statusKey = this.getAttribute('data-status');
                    const roomList = document.querySelector(
                        `[data-room-list][data-status="${statusKey}"]`);
                    const isExpanded = roomList.classList.contains('is-expanded');

                    roomList.classList.toggle('is-expanded', !isExpanded);
                    roomList.classList.toggle('is-collapsed', isExpanded);
                    updateButtonLabel(this, !isExpanded);
                });
            });

            syncRoomLists();

            let resizeTimeout = null;
            window.addEventListener('resize', function() {
                window.clearTimeout(resizeTimeout);
                resizeTimeout = window.setTimeout(syncRoomLists, 120);
            });
        });
    </script>

@endsection
