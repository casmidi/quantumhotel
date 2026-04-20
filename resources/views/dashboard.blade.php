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
            border-radius: 24px;
            padding: 1.7rem;
            color: #fff;
            background: linear-gradient(125deg, #182f4a 0%, #2e5c86 50%, #4b8cb5 100%);
            box-shadow: 0 16px 36px rgba(16, 35, 59, 0.15);
        }

        .dashboard-hero h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .dashboard-hero p {
            margin: 0.6rem 0 0;
            color: rgba(255, 255, 255, 0.86);
            max-width: 820px;
        }

        .dashboard-totals {
            margin-top: 1.2rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .dashboard-total {
            display: inline-flex;
            flex-direction: column;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 180px;
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
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: rgba(24, 65, 110, 0.12);
            font-size: 0.78rem;
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

        .status-grid-controls {
            margin-top: 0.55rem;
        }

        .status-grid-more-btn {
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0.42rem 0.8rem;
            border: 1px dashed rgba(24, 65, 110, 0.3);
            border-radius: 8px;
            background: #f9fbff;
            color: #173761;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-grid-more-btn.is-visible {
            display: inline-flex;
        }

        .status-grid-more-btn:hover {
            background: #f0f5ff;
            border-color: rgba(24, 65, 110, 0.5);
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
        <section class="dashboard-hero mb-4">
            <h1>Room Status Overview</h1>
            <p>
                Menampilkan seluruh status kamar aktif: Occupied, Vacant Ready, Vacant Clean, Vacant Dirty, Complimentary,
                Owner Unit, Renovated, dan Out Of Order.
            </p>

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
                @endphp
                <div class="col-xl-3 col-md-6 mb-3">
                    <article class="status-grid-card">
                        <div class="status-grid-head">
                            <span>{!! $metricIcons[$statusKey] ?? '&#9632;' !!} {{ $statusLabels[$statusKey] ?? $statusKey }}</span>
                            <span class="status-grid-count">{{ $rooms->count() }}</span>
                        </div>

                        @if ($rooms->isEmpty())
                            <div class="status-grid-list">
                                <ul style="margin: 0; padding: 0; list-style: none;">
                                    <li class="status-grid-empty">Tidak ada kamar</li>
                                </ul>
                            </div>
                        @else
                            <div class="status-grid-list">
                                <ul class="status-grid-list-items is-collapsed" data-room-list
                                    data-status="{{ $statusKey }}">
                                    @foreach ($rooms as $roomCode)
                                        <li>{{ $roomCode }}</li>
                                    @endforeach
                                </ul>

                                @if ($rooms->count() > 1)
                                    <div class="status-grid-controls">
                                        <button type="button" class="status-grid-more-btn"
                                            data-status="{{ $statusKey }}" data-toggle="room-list"
                                            data-more-label="More" data-less-label="Less" aria-expanded="false">
                                            <span class="btn-text">More</span>
                                        </button>
                                    </div>
                                @endif
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

            const syncRoomLists = () => {
                roomLists.forEach(list => {
                    const statusKey = list.getAttribute('data-status');
                    const button = document.querySelector(
                        `[data-toggle="room-list"][data-status="${statusKey}"]`);

                    if (!button) {
                        return;
                    }

                    const items = Array.from(list.querySelectorAll('li'));
                    const expanded = list.classList.contains('is-expanded');

                    items.forEach(item => item.classList.remove('is-extra'));

                    if (items.length <= 1) {
                        button.classList.remove('is-visible');
                        updateButtonLabel(button, false);
                        return;
                    }

                    const firstRowTop = items[0].offsetTop;
                    let hasExtraItems = false;

                    items.forEach(item => {
                        if (item.offsetTop > firstRowTop) {
                            item.classList.add('is-extra');
                            hasExtraItems = true;
                        }
                    });

                    if (!hasExtraItems) {
                        list.classList.remove('is-collapsed', 'is-expanded');
                        button.classList.remove('is-visible');
                        updateButtonLabel(button, false);
                        return;
                    }

                    button.classList.add('is-visible');

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
