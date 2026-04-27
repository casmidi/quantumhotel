@extends('layouts.app')

@php
    $reportTitles = [
        'summary' => 'Executive Booking Summary',
        'detail' => 'Booking Detail Ledger',
        'availability' => 'Room Availability Chart',
        'reservation' => 'Reservation Calendar Board',
        'calendar' => 'Monthly Booking Calendar',
    ];
    $reportSubtitles = [
        'summary' => 'Consolidated reservation performance and room allocation by booking number.',
        'detail' => 'Detailed reservation ledger with guest, room, contact, remark, and rate information.',
        'availability' => 'Premium room timeline with daily availability, occupancy count, and stay bars.',
        'reservation' => 'Operational reservation board grouped by room and date for front office monitoring.',
        'calendar' => 'Monthly booking calendar with daily reservation and room activity.',
    ];
    $currentReportTitle = $reportTitles[$viewMode] ?? 'Booking Report';
    $currentReportSubtitle = $reportSubtitles[$viewMode] ?? 'Calendar booking and booking details.';
@endphp

@section('title', $currentReportTitle)

@section('topbar_brand')
    <div class="booking-report-topbar">
        <h1>{{ $currentReportTitle }}</h1>
        <p>{{ $currentReportSubtitle }} Period {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}.</p>
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
            transition: opacity 0.18s ease, filter 0.18s ease;
        }

        .booking-report-page.is-loading .booking-report-shell {
            opacity: 0.58;
            filter: saturate(0.82);
            pointer-events: none;
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
            grid-column: 1 / -1;
            justify-content: flex-end;
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
            grid-column: 1 / -1;
        }

        .booking-report-mode {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            align-items: stretch;
            gap: 0.5rem;
            min-height: 54px;
            padding: 0.55rem;
            border: 1px solid rgba(128, 79, 87, 0.18);
            border-radius: 8px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(250, 247, 245, 0.96));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85), 0 12px 28px rgba(77, 48, 54, 0.06);
        }

        .booking-report-mode-option {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.6rem;
            min-width: 0;
            min-height: 44px;
            margin: 0;
            border-radius: 6px;
            border: 1px solid transparent;
            color: #895d64;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.055em;
            padding: 0.45rem 0.72rem;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease, color 0.16s ease;
        }

        .booking-report-mode-option:hover {
            background: rgba(128, 79, 87, 0.06);
            border-color: rgba(128, 79, 87, 0.16);
            color: #6e4048;
        }

        .booking-report-mode-option i {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(128, 79, 87, 0.08);
            color: #8f5c64;
            font-size: 0.82rem;
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
            background: linear-gradient(135deg, #7f4d56, #9b6870);
            border-color: rgba(114, 68, 76, 0.62);
            color: #fff;
            box-shadow: 0 12px 24px rgba(91, 50, 58, 0.22);
        }

        .booking-report-mode-option.is-active i {
            background: rgba(255, 255, 255, 0.2);
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

        .availability-chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 0.8rem;
            color: var(--package-muted);
            font-size: 0.78rem;
            font-weight: 850;
        }

        .availability-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .availability-legend-swatch {
            width: 24px;
            height: 10px;
            border-radius: 3px;
            background: #2ab7d6;
        }

        .availability-legend-swatch.today {
            background: #fff3a2;
            border: 1px solid #d7c75f;
        }

        .availability-legend-swatch.available {
            background: #f5fafc;
            border: 1px solid rgba(18, 62, 86, 0.12);
        }

        .availability-chart-wrap {
            max-height: 72vh;
            overflow: auto;
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            background: var(--package-shell-bg);
            scrollbar-gutter: stable both-edges;
        }

        .availability-board {
            --room-column: 220px;
            --day-column: 78px;
            min-width: calc(var(--room-column) + (var(--day-count) * var(--day-column)));
        }

        .availability-chart-header,
        .availability-class-row,
        .availability-room-track {
            display: grid;
            grid-template-columns: var(--room-column) minmax(0, 1fr);
        }

        .availability-chart-header {
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .availability-left-head,
        .availability-room-label,
        .availability-class-label {
            position: sticky;
            left: 0;
            z-index: 8;
            border-right: 1px solid rgba(18, 62, 86, 0.12);
        }

        .availability-left-head {
            display: grid;
            align-content: center;
            gap: 0.2rem;
            min-height: 82px;
            padding: 0.7rem 0.85rem;
            background: var(--package-header-bg);
            border-bottom: 1px solid var(--package-shell-border);
        }

        .availability-left-head span,
        .availability-class-label span {
            color: var(--package-label);
            font-size: 0.72rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .availability-left-head strong,
        .availability-class-label strong {
            color: var(--package-title);
            font-size: 0.9rem;
            font-weight: 900;
        }

        .availability-day-strip,
        .availability-room-days {
            display: grid;
            grid-template-columns: repeat(var(--day-count), var(--day-column));
        }

        .availability-day {
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 0.12rem;
            min-height: 82px;
            padding: 0.35rem;
            background: var(--package-table-head-bg);
            border-right: 1px solid rgba(18, 62, 86, 0.12);
            border-bottom: 1px solid var(--package-shell-border);
            text-align: center;
        }

        .availability-day.is-today {
            background: #fff7ad;
        }

        .availability-day-badge {
            min-width: 28px;
            border-radius: 5px;
            background: #dce5ea;
            color: #6b7f8d;
            font-size: 0.68rem;
            font-weight: 900;
            line-height: 1.25;
            padding: 0.1rem 0.35rem;
        }

        .availability-day strong {
            color: var(--package-title);
            font-size: 0.74rem;
            line-height: 1.15;
        }

        .availability-day small,
        .availability-day em {
            color: var(--package-muted);
            font-size: 0.68rem;
            font-style: normal;
            font-weight: 850;
            line-height: 1.1;
        }

        .availability-class-label,
        .availability-class-fill {
            min-height: 36px;
            background: var(--package-heading-bg);
            border-bottom: 1px solid var(--package-shell-border);
        }

        .availability-class-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.8rem;
        }

        .availability-room-label {
            display: grid;
            align-content: center;
            gap: 0.15rem;
            min-height: var(--row-height);
            padding: 0.45rem 0.75rem;
            background: var(--package-shell-bg);
            border-bottom: 1px solid rgba(18, 62, 86, 0.1);
        }

        .availability-room-label strong {
            color: var(--package-title);
            font-size: 0.86rem;
            font-weight: 900;
        }

        .availability-room-label small {
            min-width: 0;
            overflow: hidden;
            color: var(--package-muted);
            font-size: 0.72rem;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .availability-room-days {
            position: relative;
            grid-template-rows: repeat(var(--lane-count), 26px);
            min-height: var(--row-height);
            background:
                repeating-linear-gradient(
                    to right,
                    #fff 0,
                    #fff calc(var(--day-column) - 1px),
                    rgba(18, 62, 86, 0.08) calc(var(--day-column) - 1px),
                    rgba(18, 62, 86, 0.08) var(--day-column)
                );
            border-bottom: 1px solid rgba(18, 62, 86, 0.1);
        }

        .availability-tape {
            z-index: 3;
            align-self: center;
            min-width: 0;
            height: 21px;
            margin: 2px 5px;
            padding: 0 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 900;
            line-height: 1;
            clip-path: polygon(8px 0, 100% 0, calc(100% - 8px) 100%, 0 100%);
            box-shadow: 0 8px 14px rgba(16, 35, 59, 0.12);
        }

        .availability-tape span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .availability-tape.tape-0 {
            background: #2ab7d6;
        }

        .availability-tape.tape-1 {
            background: #30a66a;
        }

        .availability-tape.tape-2 {
            background: #6f7f8c;
        }

        .availability-tape.tape-3 {
            background: #d95764;
        }

        .availability-tape.tape-4 {
            background: #347fbd;
        }

        .availability-tape.tape-5 {
            background: #b9892e;
        }

        .reservation-calendar-wrap {
            max-height: 72vh;
            overflow: auto;
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            background: #f7f8f9;
            scrollbar-gutter: stable both-edges;
        }

        .reservation-calendar-board {
            --room-column: 184px;
            --day-column: 112px;
            min-width: calc(var(--room-column) + (var(--day-count) * var(--day-column)));
        }

        .reservation-calendar-header,
        .reservation-calendar-row {
            display: grid;
            grid-template-columns: var(--room-column) minmax(0, 1fr);
        }

        .reservation-calendar-header {
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .reservation-calendar-corner,
        .reservation-calendar-room {
            position: sticky;
            left: 0;
            z-index: 12;
            border-right: 1px solid #d9dee3;
        }

        .reservation-calendar-corner {
            display: flex;
            align-items: center;
            min-height: 74px;
            padding: 0.8rem;
            background: #fff;
            color: #8a6168;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 1px solid #d9dee3;
        }

        .reservation-calendar-days,
        .reservation-calendar-cells {
            display: grid;
            grid-template-columns: repeat(var(--day-count), var(--day-column));
        }

        .reservation-calendar-day {
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 0.22rem;
            min-height: 74px;
            padding: 0.45rem;
            background: #fff;
            color: #8a6168;
            border-right: 1px solid #d9dee3;
            border-bottom: 1px solid #d9dee3;
            text-align: center;
        }

        .reservation-calendar-day.is-today {
            background: #fff7d7;
        }

        .reservation-calendar-day strong {
            font-size: 0.82rem;
            font-weight: 900;
            line-height: 1.1;
        }

        .reservation-calendar-day span {
            font-size: 0.74rem;
            font-weight: 850;
            line-height: 1.1;
        }

        .reservation-calendar-room {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            align-items: center;
            gap: 0.55rem;
            min-height: var(--reservation-row-height);
            padding: 0.55rem 0.75rem;
            background: #fff;
            color: #8a6168;
            border-bottom: 1px solid #d9dee3;
        }

        .reservation-calendar-room-number {
            color: #9a7178;
            font-size: 1.05rem;
            font-weight: 500;
            text-align: right;
        }

        .reservation-calendar-room strong {
            display: block;
            color: #6e5359;
            font-size: 0.82rem;
            font-weight: 900;
            line-height: 1.15;
        }

        .reservation-calendar-room small {
            display: block;
            min-width: 0;
            overflow: hidden;
            color: #8c7b80;
            font-size: 0.72rem;
            font-weight: 850;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .reservation-calendar-cells {
            position: relative;
            grid-template-rows: repeat(var(--lane-count), 34px);
            min-height: var(--reservation-row-height);
            background:
                repeating-linear-gradient(
                    to right,
                    #fff 0,
                    #fff calc(var(--day-column) - 1px),
                    #d9dee3 calc(var(--day-column) - 1px),
                    #d9dee3 var(--day-column)
                );
            border-bottom: 1px solid #d9dee3;
        }

        .reservation-calendar-booking {
            position: relative;
            z-index: 4;
            align-self: center;
            height: 30px;
            margin: 2px 3px;
            padding: 0.22rem 0.48rem;
            display: grid;
            align-content: center;
            overflow: hidden;
            border-radius: 4px;
            color: #fff;
            font-size: 0.66rem;
            font-weight: 900;
            line-height: 1.12;
            box-shadow: 0 8px 16px rgba(16, 35, 59, 0.14);
            text-transform: uppercase;
        }

        .reservation-calendar-booking::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 9px solid #ffb04a;
            border-left: 9px solid transparent;
        }

        .reservation-calendar-booking span,
        .reservation-calendar-booking small {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .reservation-calendar-booking small {
            opacity: 0.86;
            font-size: 0.6rem;
        }

        .reservation-calendar-booking.tape-0 {
            background: #2f8d98;
        }

        .reservation-calendar-booking.tape-1 {
            background: #2f708b;
        }

        .reservation-calendar-booking.tape-2 {
            background: #7c776d;
        }

        .reservation-calendar-booking.tape-3 {
            background: #9b8461;
        }

        .reservation-calendar-booking.tape-4 {
            background: #466b8c;
        }

        .reservation-calendar-booking.tape-5 {
            background: #5f6973;
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

            .availability-board {
                --room-column: 170px;
                --day-column: 68px;
            }

            .availability-day strong {
                font-size: 0.68rem;
            }

            .reservation-calendar-board {
                --room-column: 168px;
                --day-column: 96px;
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
                        <label for="month">Reporting Month</label>
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
                        <label>Report Format</label>
                        <div class="booking-report-mode" role="radiogroup" aria-label="Booking report view">
                            <label class="booking-report-mode-option {{ $viewMode === 'summary' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="summary" @checked($viewMode === 'summary')>
                                <i class="fas fa-layer-group"></i>
                                <span>Executive Summary</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'detail' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="detail" @checked($viewMode === 'detail')>
                                <i class="fas fa-list"></i>
                                <span>Booking Detail Ledger</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'availability' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="availability" @checked($viewMode === 'availability')>
                                <i class="fas fa-th-large"></i>
                                <span>Room Availability Chart</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'reservation' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="reservation" @checked($viewMode === 'reservation')>
                                <i class="fas fa-calendar-week"></i>
                                <span>Reservation Calendar Board</span>
                            </label>
                            <label class="booking-report-mode-option {{ $viewMode === 'calendar' ? 'is-active' : '' }}">
                                <input type="radio" name="view" value="calendar" @checked($viewMode === 'calendar')>
                                <i class="fas fa-calendar-alt"></i>
                                <span>Monthly Booking Calendar</span>
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
                    <div class="booking-report-stat"><span>Report Rows</span><strong>{{ number_format($summary['guests']) }}</strong></div>
                    <div class="booking-report-stat"><span>Total Rate</span><strong>{{ number_format($summary['rate_total'], 0, ',', '.') }}</strong></div>
                </div>

                <div class="booking-report-view">
                    @if ($viewMode === 'summary')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Executive Booking Summary</h2>
                            <p class="booking-report-subtitle">Consolidated reservation performance and room allocation by booking number.</p>
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
                    @elseif ($viewMode === 'detail')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Booking Detail Ledger</h2>
                            <p class="booking-report-subtitle">Detailed reservation ledger with guest, room, contact, remark, and rate information.</p>
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
                    @elseif ($viewMode === 'calendar')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Monthly Booking Calendar</h2>
                            <p class="booking-report-subtitle">{{ $calendar['month_label'] }} booking calendar with daily reservation and room activity.</p>
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
                    @elseif ($viewMode === 'availability')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Room Availability Chart</h2>
                            <p class="booking-report-subtitle">{{ $availabilityChart['month_label'] }} room timeline with daily availability, occupancy count, and stay bars.</p>
                        </div>
                        <div class="booking-report-body">
                            @if ($availabilityChart['total_rooms'] > 0)
                                <div class="availability-chart-legend" aria-label="Room availability legend">
                                    <span class="availability-legend-item"><span class="availability-legend-swatch"></span> Booking</span>
                                    <span class="availability-legend-item"><span class="availability-legend-swatch today"></span> Today</span>
                                    <span class="availability-legend-item"><span class="availability-legend-swatch available"></span> Available Cell</span>
                                </div>
                                <div class="availability-chart-wrap">
                                    <div class="availability-board" style="--day-count: {{ $availabilityChart['day_count'] }};">
                                        <div class="availability-chart-header">
                                            <div class="availability-left-head">
                                                <span>All Room Types</span>
                                                <strong>{{ number_format($availabilityChart['total_rooms']) }} rooms</strong>
                                            </div>
                                            <div class="availability-day-strip">
                                                @foreach ($availabilityChart['days'] as $day)
                                                    <div class="availability-day {{ $day['is_today'] ? 'is-today' : '' }}">
                                                        <span class="availability-day-badge">{{ $day['booked_count'] }}</span>
                                                        <strong>{{ $day['weekday'] }} {{ $day['day'] }}</strong>
                                                        <small>{{ $day['availability_percent'] }}% available</small>
                                                        <em>{{ $day['available_count'] }} open</em>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        @foreach ($availabilityChart['groups'] as $group)
                                            <div class="availability-class-row">
                                                <div class="availability-class-label">
                                                    <span>{{ $group['class'] }}</span>
                                                    <strong>{{ $group['room_count'] }}</strong>
                                                </div>
                                                <div class="availability-class-fill"></div>
                                            </div>

                                            @foreach ($group['rooms'] as $room)
                                                <div class="availability-room-track" style="--lane-count: {{ $room['lane_count'] }}; --row-height: {{ $room['row_height'] }}px;">
                                                    <div class="availability-room-label">
                                                        <strong>{{ $room['code'] }}</strong>
                                                        <small>{{ $room['class'] }}{{ $room['rate'] > 0 ? ' - ' . number_format($room['rate'], 0, ',', '.') : '' }}</small>
                                                    </div>
                                                    <div class="availability-room-days">
                                                        @foreach ($room['tapes'] as $tape)
                                                            <div class="availability-tape {{ $tape['class'] }}"
                                                                style="grid-column: {{ $tape['start_column'] }} / span {{ $tape['span'] }}; grid-row: {{ $tape['lane'] }};"
                                                                title="{{ $tape['guest'] }} | {{ $tape['date_range'] }} | {{ $tape['res_no'] }}">
                                                                <span>{{ $tape['label'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">No room data available for this chart.</div>
                            @endif
                        </div>
                    </section>
                    @elseif ($viewMode === 'reservation')
                    <section class="booking-report-card">
                        <div class="booking-report-section-header">
                            <h2 class="booking-report-title">Reservation Calendar Board</h2>
                            <p class="booking-report-subtitle">{{ $availabilityChart['month_label'] }} operational reservation board grouped by room and date.</p>
                        </div>
                        <div class="booking-report-body">
                            @if ($availabilityChart['total_rooms'] > 0)
                                <div class="reservation-calendar-wrap">
                                    <div class="reservation-calendar-board" style="--day-count: {{ $availabilityChart['day_count'] }};">
                                        <div class="reservation-calendar-header">
                                            <div class="reservation-calendar-corner">Room / Date</div>
                                            <div class="reservation-calendar-days">
                                                @foreach ($availabilityChart['days'] as $day)
                                                    <div class="reservation-calendar-day {{ $day['is_today'] ? 'is-today' : '' }}">
                                                        <strong>{{ $day['weekday'] }}</strong>
                                                        <span>{{ $day['day'] }} {{ $day['month'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        @php($reservationRoomNumber = 1)
                                        @foreach ($availabilityChart['groups'] as $group)
                                            @foreach ($group['rooms'] as $room)
                                                <div class="reservation-calendar-row" style="--lane-count: {{ $room['lane_count'] }}; --reservation-row-height: {{ 54 + (($room['lane_count'] - 1) * 34) }}px;">
                                                    <div class="reservation-calendar-room">
                                                        <span class="reservation-calendar-room-number">{{ str_pad((string) $reservationRoomNumber, 2, '0', STR_PAD_LEFT) }}</span>
                                                        <span>
                                                            <strong>{{ $room['code'] }}</strong>
                                                            <small>{{ $room['class'] }}</small>
                                                        </span>
                                                    </div>
                                                    <div class="reservation-calendar-cells">
                                                        @foreach ($room['tapes'] as $tape)
                                                            <div class="reservation-calendar-booking {{ $tape['class'] }}"
                                                                style="grid-column: {{ $tape['start_column'] }} / span {{ $tape['span'] }}; grid-row: {{ $tape['lane'] }};"
                                                                title="{{ $tape['guest'] }} | {{ $tape['date_range'] }} | {{ $tape['res_no'] }}">
                                                                <span>{{ $tape['label'] }}</span>
                                                                <small>{{ $room['class'] }}</small>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @php($reservationRoomNumber++)
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">No room data available for this calendar.</div>
                            @endif
                        </div>
                    </section>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var page = document.querySelector('.booking-report-page');
            var activeRequest = null;

            if (!page || page.dataset.partialRefreshReady === '1') {
                return;
            }

            page.dataset.partialRefreshReady = '1';

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function currentForm() {
                return page.querySelector('.booking-report-filter');
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

                nativeInput = page.querySelector('[data-date-native-for="' + displayInput.id + '"]');
                iso = displayToIsoDate(displayInput.value);

                if (nativeInput && iso) {
                    nativeInput.value = iso;
                }
            }

            function syncAllDateInputs() {
                page.querySelectorAll('.booking-report-date-display').forEach(function (input) {
                    syncNativeDateInput(input);
                });
            }

            function syncDateRangeFromMonth() {
                var form = currentForm();
                var monthInput = form ? form.querySelector('#month') : null;
                var fromInput = form ? form.querySelector('#date_from') : null;
                var toInput = form ? form.querySelector('#date_to') : null;
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
            }

            function buildReportUrl(form) {
                var url = new URL(form.getAttribute('action') || window.location.pathname, window.location.origin);
                var formData = new FormData(form);

                url.search = '';
                formData.forEach(function (value, key) {
                    if (value !== null && String(value).trim() !== '') {
                        url.searchParams.set(key, value);
                    }
                });

                return url;
            }

            function setLoading(isLoading) {
                page.classList.toggle('is-loading', isLoading);
                page.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            }

            function replaceIfPresent(selector, nextDocument, nextSelector) {
                var currentElement = document.querySelector(selector);
                var nextElement = nextDocument.querySelector(nextSelector || selector);

                if (currentElement && nextElement) {
                    currentElement.innerHTML = nextElement.innerHTML;
                }
            }

            function submitReportForm(options) {
                var form = currentForm();

                if (!form) {
                    return;
                }

                syncAllDateInputs();
                loadReport(buildReportUrl(form), options || { push: true });
            }

            function loadReport(url, options) {
                var currentBody = page.querySelector('.booking-report-body');

                if (!currentBody || !window.fetch || !window.DOMParser) {
                    window.location.href = url.toString();
                    return;
                }

                if (activeRequest) {
                    activeRequest.abort();
                }

                activeRequest = new AbortController();
                var request = activeRequest;
                setLoading(true);

                fetch(url.toString(), {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'text/html'
                    },
                    signal: request.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Report request failed.');
                        }

                        return response.text();
                    })
                    .then(function (html) {
                        var nextDocument = new DOMParser().parseFromString(html, 'text/html');
                        var nextBody = nextDocument.querySelector('.booking-report-body');
                        var nextTitle = nextDocument.querySelector('title');
                        var nextPath = url.pathname + url.search;

                        if (!nextBody) {
                            window.location.href = url.toString();
                            return;
                        }

                        currentBody.innerHTML = nextBody.innerHTML;
                        replaceIfPresent('.navbar-topbar-brand-slot', nextDocument);
                        replaceIfPresent('.content-shell > h3', nextDocument);

                        if (nextTitle) {
                            document.title = nextTitle.textContent;
                        }

                        if ((options || {}).replace) {
                            window.history.replaceState({ bookingReportUrl: nextPath }, '', nextPath);
                        } else {
                            window.history.pushState({ bookingReportUrl: nextPath }, '', nextPath);
                        }
                    })
                    .catch(function (error) {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        window.location.href = url.toString();
                    })
                    .finally(function () {
                        if (activeRequest === request) {
                            activeRequest = null;
                            setLoading(false);
                        }
                    });
            }

            page.addEventListener('submit', function (event) {
                if (!event.target.matches('.booking-report-filter')) {
                    return;
                }

                event.preventDefault();
                submitReportForm({ push: true });
            });

            page.addEventListener('change', function (event) {
                var target = event.target;
                var displayInput;

                if (target.matches('input[name="view"]')) {
                    submitReportForm({ push: true });
                    return;
                }

                if (target.matches('#month')) {
                    syncDateRangeFromMonth();
                    submitReportForm({ push: true });
                    return;
                }

                if (target.matches('.booking-report-date-native')) {
                    displayInput = page.querySelector('#' + (target.dataset.dateNativeFor || ''));

                    if (displayInput) {
                        displayInput.value = isoToDisplayDate(target.value);
                    }
                }
            });

            page.addEventListener('blur', function (event) {
                if (event.target.matches('.booking-report-date-display')) {
                    syncNativeDateInput(event.target);
                }
            }, true);

            page.addEventListener('click', function (event) {
                var button = event.target.closest('.booking-report-date-picker');
                var displayInput;
                var nativeInput;

                if (!button || !page.contains(button)) {
                    return;
                }

                displayInput = page.querySelector('#' + (button.dataset.datePickerFor || ''));
                nativeInput = page.querySelector('[data-date-native-for="' + (button.dataset.datePickerFor || '') + '"]');

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

            window.addEventListener('popstate', function () {
                loadReport(new URL(window.location.href), { replace: true });
            });

            window.history.replaceState({ bookingReportUrl: window.location.pathname + window.location.search }, '', window.location.href);
        });
    </script>
@endsection
