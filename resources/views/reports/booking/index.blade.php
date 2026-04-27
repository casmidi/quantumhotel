@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="booking-report-topbar">
        <h1>Booking Report</h1>
        <p>Calendar booking and booking details from {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}.</p>
    </div>
@endsection

@section('content')
    @include('partials.crud-package-theme')

    <style>
        .booking-report-page {
            color: var(--package-text);
            padding-bottom: 2rem;
        }

        .booking-report-shell {
            background: var(--package-shell-bg);
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            box-shadow: var(--package-shell-shadow);
            overflow: hidden;
        }

        .booking-report-topbar {
            min-width: 0;
            padding: 0.1rem 0;
        }

        .booking-report-topbar h1 {
            margin: 0;
            color: var(--package-title);
            font-size: 1.15rem;
            font-weight: 900;
            line-height: 1.15;
        }

        .booking-report-topbar p {
            margin: 0.15rem 0 0;
            color: var(--package-muted);
            font-size: 0.78rem;
            line-height: 1.2;
        }

        .booking-report-section-header {
            background: var(--package-header-bg);
            border-bottom: 1px solid var(--package-shell-border);
            padding: 1rem 1.2rem;
        }

        .booking-report-title {
            margin: 0;
            color: var(--package-title);
            font-size: 1.25rem;
            font-weight: 900;
        }

        .booking-report-subtitle {
            margin: 0.3rem 0 0;
            color: var(--package-muted);
            font-size: 0.9rem;
        }

        .booking-report-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .booking-report-filter {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.75rem;
            align-items: end;
        }

        .booking-report-field label {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--package-label);
            font-size: 0.74rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .booking-report-field > input {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--package-input-border);
            border-radius: 8px;
            background: var(--package-input-bg);
            color: var(--package-text);
            font-weight: 700;
            padding: 0.5rem 0.7rem;
        }

        .booking-report-date-control {
            position: relative;
        }

        .booking-report-date-display {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--package-input-border);
            border-radius: 8px;
            background: var(--package-input-bg);
            color: var(--package-text);
            font-weight: 700;
            padding: 0.5rem 2.55rem 0.5rem 0.7rem;
        }

        .booking-report-date-picker {
            position: absolute;
            top: 50%;
            right: 0.35rem;
            width: 32px;
            height: 32px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--package-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .booking-report-date-picker:hover,
        .booking-report-date-picker:focus {
            background: var(--package-button-secondary-bg);
            color: var(--package-title);
            outline: 0;
        }

        .booking-report-date-native {
            position: absolute;
            right: 0.35rem;
            bottom: 0;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .booking-report-field > input:focus,
        .booking-report-date-display:focus {
            outline: 0;
            border-color: var(--package-input-focus);
            box-shadow: var(--package-input-focus-shadow);
        }

        .booking-report-actions {
            display: flex;
            gap: 0.55rem;
            flex-wrap: wrap;
        }

        .booking-report-btn {
            min-height: 42px;
            border: 0;
            border-radius: 8px;
            padding: 0 0.95rem;
            font-weight: 900;
        }

        .booking-report-btn.primary {
            background: var(--package-button-primary);
            color: #fff;
        }

        .booking-report-btn.secondary {
            background: var(--package-button-secondary-bg);
            color: var(--package-button-secondary-text);
            border: 1px solid var(--package-input-border);
        }

        .booking-report-view-field {
            grid-column: span 2;
        }

        .booking-report-mode {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
            min-height: 42px;
            padding: 0.25rem;
            border: 1px solid var(--package-input-border);
            border-radius: 8px;
            background: var(--package-input-bg);
        }

        .booking-report-mode-option {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-width: max-content;
            min-height: 32px;
            margin: 0;
            border-radius: 6px;
            color: var(--package-muted);
            font-size: 0.82rem;
            font-weight: 900;
            padding: 0 0.8rem;
            cursor: pointer;
        }

        .booking-report-mode-option input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .booking-report-mode-option span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .booking-report-mode-option.is-active {
            background: var(--package-button-primary);
            color: #fff;
        }

        .booking-report-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .booking-report-stat,
        .booking-calendar-day {
            background: var(--package-heading-bg);
            border: 1px solid var(--package-heading-border);
            border-radius: 8px;
            padding: 0.8rem;
        }

        .booking-report-stat span {
            display: block;
            color: var(--package-muted);
            font-size: 0.72rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .booking-report-stat strong {
            display: block;
            margin-top: 0.4rem;
            color: var(--package-title);
            font-size: 1.35rem;
            line-height: 1;
        }

        .booking-report-view {
            margin-top: 1rem;
        }

        .booking-report-card {
            background: var(--package-shell-bg);
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .booking-report-table-wrap {
            overflow-x: auto;
        }

        .booking-report-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            min-width: 760px;
        }

        .booking-report-table th {
            background: var(--package-table-head-bg);
            color: var(--package-title);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.7rem;
            border-bottom: 1px solid var(--package-shell-border);
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }

        .booking-report-table td {
            color: var(--package-text);
            padding: 0.65rem 0.7rem;
            border-bottom: 1px solid var(--package-shell-border);
            text-align: center;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .booking-report-table .text-right {
            text-align: right;
        }

        .booking-report-table .text-left {
            text-align: left;
        }

        .booking-report-room-list {
            min-width: 220px;
            line-height: 1.45;
        }

        .booking-calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(96px, 1fr));
            gap: 0.55rem;
        }

        .booking-calendar-day {
            min-height: 132px;
        }

        .booking-calendar-day.has-booking {
            background: rgba(196, 47, 58, 0.14);
            border-color: rgba(196, 47, 58, 0.36);
        }

        .booking-calendar-date {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            color: var(--package-title);
            font-weight: 900;
        }

        .booking-calendar-count {
            margin-top: 0.45rem;
            color: #a92835;
            font-size: 0.8rem;
            font-weight: 900;
        }

        .booking-calendar-detail {
            margin: 0.45rem 0 0;
            padding-left: 1rem;
            color: var(--package-text);
            font-size: 0.78rem;
        }

        .booking-calendar-detail li + li {
            margin-top: 0.35rem;
        }

        .booking-calendar-group-count {
            color: #a92835;
            font-weight: 900;
            white-space: nowrap;
        }

        @media (max-width: 1199.98px) {
            .booking-report-filter,
            .booking-report-summary {
                grid-template-columns: 1fr 1fr;
            }

            .booking-report-view-field {
                grid-column: span 2;
            }
        }

        @media (max-width: 767.98px) {
            .booking-report-filter,
            .booking-report-summary,
            .booking-calendar {
                grid-template-columns: 1fr;
            }

            .booking-report-view-field {
                grid-column: span 1;
            }
        }
    </style>

    <div class="container-fluid booking-report-page">
        <section class="booking-report-shell">
            <div class="booking-report-body">
                <form method="GET" action="/booking-report" class="booking-report-filter">
                    <div class="booking-report-field">
                        <label for="date_from">From</label>
                        <div class="booking-report-date-control">
                            <input type="text" id="date_from" name="date_from" class="booking-report-date-display" value="{{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" maxlength="10">
                            <button type="button" class="booking-report-date-picker" data-date-picker-for="date_from" aria-label="Pick from date">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <input type="date" class="booking-report-date-native package-date-native" data-date-native data-date-native-for="date_from" value="{{ $dateFrom }}" tabindex="-1" aria-hidden="true">
                        </div>
                    </div>
                    <div class="booking-report-field">
                        <label for="date_to">To</label>
                        <div class="booking-report-date-control">
                            <input type="text" id="date_to" name="date_to" class="booking-report-date-display" value="{{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" maxlength="10">
                            <button type="button" class="booking-report-date-picker" data-date-picker-for="date_to" aria-label="Pick to date">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <input type="date" class="booking-report-date-native package-date-native" data-date-native data-date-native-for="date_to" value="{{ $dateTo }}" tabindex="-1" aria-hidden="true">
                        </div>
                    </div>
                    <div class="booking-report-field">
                        <label for="month">Calendar Month</label>
                        <input type="month" id="month" name="month" value="{{ $month }}">
                    </div>
                    <div class="booking-report-field">
                        <label for="res_no">Reservation No</label>
                        <input type="text" id="res_no" name="res_no" value="{{ $resNo }}" placeholder="Optional">
                    </div>
                    <div class="booking-report-field">
                        <label for="search">Guest / Room</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Optional">
                    </div>
                    <div class="booking-report-field booking-report-view-field">
                        <label>View</label>
                        <div class="booking-report-mode" role="radiogroup" aria-label="Booking report view">
                            <label class="booking-report-mode-option {{ $viewMode === 'summary' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="summary" onchange="this.form.submit()" @checked($viewMode === 'summary')>
                                <i class="fas fa-layer-group"></i>
                                <span>Summary</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'detail' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="detail" onchange="this.form.submit()" @checked($viewMode === 'detail')>
                                <i class="fas fa-list"></i>
                                <span>Breakdown / Details</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'calendar' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="calendar" onchange="this.form.submit()" @checked($viewMode === 'calendar')>
                                <i class="fas fa-calendar-alt"></i>
                                <span>Calendar Booking</span>
                            </label>
                        </div>
                    </div>
                    <div class="booking-report-actions">
                        <button class="booking-report-btn primary" type="submit"><i class="fas fa-search mr-1"></i> Show</button>
                        <a class="booking-report-btn secondary d-inline-flex align-items-center" href="{{ $printUrl }}" target="_blank"><i class="fas fa-print mr-1"></i> Print</a>
                    </div>
                </form>

                <div class="booking-report-summary">
                    <div class="booking-report-stat"><span>Reservations</span><strong>{{ number_format($summary['reservations']) }}</strong></div>
                    <div class="booking-report-stat"><span>Rooms</span><strong>{{ number_format($summary['rooms']) }}</strong></div>
                    <div class="booking-report-stat"><span>Rows</span><strong>{{ number_format($summary['guests']) }}</strong></div>
                    <div class="booking-report-stat"><span>Total Rate</span><strong>{{ number_format($summary['rate_total'], 0, ',', '.') }}</strong></div>
                </div>

                <div class="booking-report-view">
                    @if ($viewMode === 'summary')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Summary Booking</h2>
                            <p class="booking-report-subtitle">One reservation number represents all reserved rooms in that booking.</p>
                        </div>
                        <div class="booking-report-table-wrap">
                            <table class="booking-report-table">
                                <thead>
                                    <tr>
                                        <th>ResNo</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Guest</th>
                                        <th>Rooms</th>
                                        <th>Room List</th>
                                        <th>Class</th>
                                        <th>Remark</th>
                                        <th class="text-right">Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($summaryRows as $row)
                                        <tr>
                                            <td>{{ $row->ResNo }}</td>
                                            <td>{{ $row->TglInDisplay }}</td>
                                            <td>{{ $row->TglKeluarDisplay }}</td>
                                            <td>{{ $row->Guest }}</td>
                                            <td>{{ $row->RoomCount }} {{ $row->RoomCount === 1 ? 'room' : 'rooms' }}</td>
                                            <td class="text-left booking-report-room-list">{{ $row->RoomList }}</td>
                                            <td>{{ $row->ClassList }}</td>
                                            <td>{{ $row->Remark }}</td>
                                            <td class="text-right">{{ number_format($row->Rate, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No booking data in this range.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                    @elseif ($viewMode === 'detail')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Breakdown / Details</h2>
                            <p class="booking-report-subtitle">{{ $profile['address'] ?? '' }} | {{ $profile['phone'] ?? '' }}</p>
                        </div>
                        <div class="booking-report-table-wrap">
                            <table class="booking-report-table">
                                <thead>
                                    <tr>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Room</th>
                                        <th>Class</th>
                                        <th>Guest</th>
                                        <th>Phone</th>
                                        <th>Remark</th>
                                        <th class="text-right">Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td>{{ $row->TglInDisplay }}</td>
                                            <td>{{ $row->TglKeluarDisplay }}</td>
                                            <td>{{ $row->Kode }}</td>
                                            <td>{{ $row->Kelas }}</td>
                                            <td>{{ $row->Guest }}</td>
                                            <td>{{ $row->Telphone }}</td>
                                            <td>{{ $row->Remark }}</td>
                                            <td class="text-right">{{ number_format($row->Rate, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No booking data in this range.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                    @else
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Calendar {{ $calendar['month_label'] }}</h2>
                            <p class="booking-report-subtitle">Highlighted dates have bookings. Details show guest names and reserved rooms.</p>
                        </div>
                        <div class="booking-report-body">
                            <div class="booking-calendar">
                                @foreach ($calendar['days'] as $day)
                                    <div class="booking-calendar-day {{ $day['has_booking'] ? 'has-booking' : '' }}">
                                        <div class="booking-calendar-date">
                                            <span>{{ $day['day'] }}</span>
                                            <span>{{ $day['weekday'] }}</span>
                                        </div>
                                        @if ($day['has_booking'])
                                            <div class="booking-calendar-count">{{ $day['room_count'] }} rooms / {{ $day['booking_count'] }} bookings</div>
                                            <ul class="booking-calendar-detail">
                                                @foreach ($day['groups'] as $group)
                                                    @php($groupRoomCount = count($group['rooms']))
                                                    <li>
                                                        <strong>{{ $group['guest'] ?: $group['res_no'] }}</strong>
                                                        <span class="booking-calendar-group-count">- {{ $groupRoomCount }} {{ $groupRoomCount === 1 ? 'room' : 'rooms' }}</span><br>
                                                        {{ implode(', ', $group['rooms']) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('.booking-report-filter');
            var monthInput = document.getElementById('month');
            var fromInput = document.getElementById('date_from');
            var toInput = document.getElementById('date_to');

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function isoToDisplayDate(value) {
                var match = String(value || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);

                if (!match) {
                    return '';
                }

                return match[3] + '-' + match[2] + '-' + match[1];
            }

            function displayToIsoDate(value) {
                var match = String(value || '').trim().match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
                var day;
                var month;
                var year;
                var date;

                if (!match) {
                    return '';
                }

                day = parseInt(match[1], 10);
                month = parseInt(match[2], 10);
                year = parseInt(match[3], 10);
                date = new Date(year, month - 1, day);

                if (
                    date.getFullYear() !== year ||
                    date.getMonth() !== month - 1 ||
                    date.getDate() !== day
                ) {
                    return '';
                }

                return year + '-' + pad(month) + '-' + pad(day);
            }

            function formatDisplayDate(date) {
                return pad(date.getDate()) + '-' + pad(date.getMonth() + 1) + '-' + date.getFullYear();
            }

            function syncNativeDateInput(displayInput) {
                var nativeInput;
                var iso;

                if (!displayInput) {
                    return;
                }

                nativeInput = document.querySelector('[data-date-native-for="' + displayInput.id + '"]');
                iso = displayToIsoDate(displayInput.value);

                if (nativeInput && iso) {
                    nativeInput.value = iso;
                }
            }

            function syncDateRangeFromMonth(shouldSubmit) {
                var parts;
                var year;
                var month;
                var start;
                var end;

                if (!monthInput || !monthInput.value || !fromInput || !toInput) {
                    return;
                }

                parts = monthInput.value.split('-');
                year = parseInt(parts[0], 10);
                month = parseInt(parts[1], 10);

                if (!year || !month) {
                    return;
                }

                start = new Date(year, month - 1, 1);
                end = new Date(year, month, 0);
                fromInput.value = formatDisplayDate(start);
                toInput.value = formatDisplayDate(end);
                syncNativeDateInput(fromInput);
                syncNativeDateInput(toInput);

                if (shouldSubmit && form) {
                    form.submit();
                }
            }

            document.querySelectorAll('.booking-report-date-display').forEach(function (input) {
                input.addEventListener('blur', function () {
                    syncNativeDateInput(input);
                });
            });

            document.querySelectorAll('.booking-report-date-native').forEach(function (nativeInput) {
                nativeInput.addEventListener('change', function () {
                    var displayInput = document.getElementById(nativeInput.dataset.dateNativeFor || '');

                    if (displayInput) {
                        displayInput.value = isoToDisplayDate(nativeInput.value);
                    }
                });
            });

            document.querySelectorAll('.booking-report-date-picker').forEach(function (button) {
                button.addEventListener('click', function () {
                    var displayInput = document.getElementById(button.dataset.datePickerFor || '');
                    var nativeInput = document.querySelector('[data-date-native-for="' + (button.dataset.datePickerFor || '') + '"]');

                    syncNativeDateInput(displayInput);

                    if (!nativeInput) {
                        return;
                    }

                    if (typeof nativeInput.showPicker === 'function') {
                        nativeInput.showPicker();
                        return;
                    }

                    nativeInput.focus();
                    nativeInput.click();
                });
            });

            if (monthInput) {
                monthInput.addEventListener('change', function () {
                    syncDateRangeFromMonth(true);
                });
            }
        });
    </script>
@endsection
