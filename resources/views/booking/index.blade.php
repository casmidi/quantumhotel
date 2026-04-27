@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="booking-topbar-brand">
        <strong>Room Booking Board</strong>
        <span>Pick an arrival date first, then compose the reservation from the live day panel.</span>
    </div>
@endsection

@section('content')

    @include('partials.crud-package-theme')

    <style>
        .booking-page {
            color: var(--package-text);
            padding-bottom: 2rem;
        }

        .booking-board {
            background: #f6f9fb;
            border: 1px solid rgba(18, 62, 86, 0.1);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(16, 35, 59, 0.1);
        }

        .booking-board-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: 1.15rem 1.25rem;
            background: linear-gradient(135deg, #0b6f7f, #123d62 64%, #25324d);
            color: #fff;
        }

        .booking-title {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 850;
            letter-spacing: 0;
        }

        .booking-subtitle {
            margin: 0.35rem 0 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.92rem;
        }

        .booking-topbar-brand {
            display: grid;
            gap: 0.12rem;
            color: var(--package-title);
            line-height: 1.15;
        }

        .booking-topbar-brand strong {
            font-size: 1.08rem;
            font-weight: 900;
        }

        .booking-topbar-brand span {
            color: var(--package-muted);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .booking-range-note {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.7rem;
            padding: 0.42rem 0.65rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 850;
        }

        .booking-month-form {
            display: flex;
            gap: 0.55rem;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .booking-month-form input,
        .booking-month-form button {
            height: 42px;
            border-radius: 8px;
            border: 0;
            font-weight: 800;
        }

        .booking-month-form input {
            padding: 0 0.75rem;
            color: #18334d;
        }

        .booking-month-form button,
        .booking-primary-button {
            background: #ff6d38;
            color: #fff;
            box-shadow: 0 12px 24px rgba(255, 109, 56, 0.28);
        }

        .booking-calendar-strip {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding: 1rem 1.25rem 1.35rem;
            background: #fff;
            border-bottom: 1px solid rgba(18, 62, 86, 0.1);
            scroll-snap-type: x proximity;
        }

        .booking-page-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            padding: 0.85rem 1rem 0;
            background: var(--package-shell-bg);
        }

        .booking-page-tab {
            min-height: 38px;
            border: 1px solid var(--package-input-border);
            border-radius: 8px;
            background: var(--package-button-secondary-bg);
            color: var(--package-button-secondary-text);
            font-weight: 900;
            padding: 0 0.9rem;
        }

        .booking-page-tab.is-active {
            background: var(--package-button-primary);
            border-color: var(--package-button-primary);
            color: #fff;
        }

        .booking-tab-panel {
            display: none;
        }

        .booking-tab-panel.is-active {
            display: block;
        }

        .booking-date-card {
            position: relative;
            flex: 0 0 138px;
            min-height: 146px;
            padding: 0.85rem;
            border: 1px solid rgba(18, 62, 86, 0.12);
            border-radius: 8px;
            background: #fff;
            color: #18334d;
            text-align: left;
            cursor: pointer;
            scroll-snap-align: start;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }

        .booking-date-card:hover,
        .booking-date-card.is-selected {
            transform: translateY(-2px);
            border-color: #ff6d38;
            box-shadow: 0 14px 26px rgba(16, 35, 59, 0.13);
        }

        .booking-date-card.is-selected {
            background: #fff6f1;
        }

        .booking-date-card.is-past {
            opacity: 0.48;
            cursor: not-allowed;
        }

        .date-weekday,
        .date-meta,
        .date-label {
            display: block;
        }

        .date-weekday {
            color: #65798b;
            font-size: 0.78rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .date-number {
            display: block;
            margin-top: 0.22rem;
            font-size: 2rem;
            line-height: 1;
            font-weight: 900;
        }

        .date-meta {
            margin-top: 0.4rem;
            font-size: 0.82rem;
            color: #65798b;
        }

        .date-label {
            margin-top: 0.65rem;
            color: #0b6f7f;
            font-size: 0.82rem;
            font-weight: 850;
        }

        .date-stay-band {
            position: absolute;
            left: -0.76rem;
            right: -0.76rem;
            bottom: 0.68rem;
            height: 12px;
            background: rgba(210, 47, 47, 0.72);
            box-shadow: 0 5px 12px rgba(210, 47, 47, 0.22);
            display: none;
        }

        .booking-date-card.stay-start .date-stay-band {
            left: 0.82rem;
            border-top-left-radius: 999px;
            border-bottom-left-radius: 999px;
        }

        .booking-date-card.stay-end .date-stay-band {
            right: 0.82rem;
            border-top-right-radius: 999px;
            border-bottom-right-radius: 999px;
        }

        .date-stay-count {
            position: absolute;
            left: 0.85rem;
            right: 0.85rem;
            bottom: 1.05rem;
            color: #8f1f1f;
            font-size: 0.72rem;
            font-weight: 900;
            line-height: 1;
            text-align: center;
            display: none;
        }

        .arrival-status {
            display: inline-flex;
            align-items: center;
            margin-left: 0.3rem;
            padding: 0.2rem 0.42rem;
            border-radius: 999px;
            background: #fff1f1;
            color: #9f2424;
            font-size: 0.74rem;
            font-weight: 850;
        }

        .booking-workspace {
            display: grid;
            grid-template-columns: minmax(310px, 0.78fr) minmax(0, 1.22fr);
            gap: 1rem;
            padding: 1rem;
        }

        .booking-panel {
            background: #fff;
            border: 1px solid rgba(18, 62, 86, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .booking-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(18, 62, 86, 0.08);
        }

        .booking-panel-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 850;
            color: #18334d;
        }

        .booking-panel-subtitle {
            margin: 0.28rem 0 0;
            color: #65798b;
            font-size: 0.86rem;
        }

        .booking-pill {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: #e8f7f9;
            color: #0b6f7f;
            font-size: 0.8rem;
            font-weight: 850;
            white-space: nowrap;
        }

        .booking-panel-body {
            padding: 1rem;
        }

        .booking-insight-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.7rem;
            margin-bottom: 1rem;
        }

        .booking-insight {
            min-height: 82px;
            padding: 0.8rem;
            border-radius: 8px;
            background: #f7fafc;
            border: 1px solid rgba(18, 62, 86, 0.08);
        }

        .booking-insight span {
            display: block;
            color: #65798b;
            font-size: 0.72rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .booking-insight strong {
            display: block;
            margin-top: 0.45rem;
            color: #18334d;
            font-size: 1.45rem;
            line-height: 1;
        }

        .booking-field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .booking-field-grid .is-wide {
            grid-column: 1 / -1;
        }

        .booking-field label {
            display: block;
            margin-bottom: 0.35rem;
            color: #31506a;
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .booking-field input,
        .booking-field select {
            width: 100%;
            min-height: 44px;
            border: 1px solid rgba(18, 62, 86, 0.14);
            border-radius: 8px;
            background: #fff;
            color: #18334d;
            font-weight: 700;
            padding: 0.55rem 0.72rem;
        }

        .booking-field input:focus,
        .booking-field select:focus {
            outline: 0;
            border-color: #0b6f7f;
            box-shadow: 0 0 0 3px rgba(11, 111, 127, 0.12);
        }

        .booking-actions {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .booking-primary-button,
        .booking-secondary-button {
            border: 0;
            border-radius: 8px;
            min-height: 44px;
            padding: 0 1rem;
            font-weight: 850;
        }

        .booking-secondary-button {
            background: #edf3f6;
            color: #18334d;
        }

        .booking-room-note {
            margin-top: 0.45rem;
            color: #65798b;
            font-size: 0.82rem;
            min-height: 1.1rem;
        }

        .booking-mode-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        .booking-mode-tab {
            min-height: 44px;
            border: 1px solid rgba(18, 62, 86, 0.14);
            border-radius: 8px;
            background: #f7fafc;
            color: #31506a;
            font-weight: 900;
        }

        .booking-mode-tab.is-active {
            border-color: #0b6f7f;
            background: #e8f7f9;
            color: #0b6f7f;
            box-shadow: 0 10px 22px rgba(11, 111, 127, 0.12);
        }

        .booking-block-fields {
            display: none;
            margin-bottom: 1rem;
            padding: 0.85rem;
            border: 1px solid rgba(11, 111, 127, 0.16);
            border-radius: 8px;
            background: #f4fbfc;
        }

        .booking-block-fields.is-active {
            display: block;
        }

        .booking-block-summary {
            margin-top: 0.7rem;
            color: #31506a;
            font-size: 0.84rem;
            font-weight: 800;
        }

        .booking-block-room-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(136px, 1fr));
            gap: 0.55rem;
            max-height: 260px;
            overflow: auto;
            margin-top: 0.75rem;
            padding: 0.65rem;
            border: 1px solid rgba(18, 62, 86, 0.12);
            border-radius: 8px;
            background: #fff;
        }

        .booking-block-room {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 0.5rem;
            align-items: center;
            min-height: 46px;
            padding: 0.55rem;
            border: 1px solid rgba(18, 62, 86, 0.12);
            border-radius: 8px;
            background: #f7fafc;
            color: #18334d;
            cursor: pointer;
        }

        .booking-block-room:has(input:checked) {
            border-color: #0b6f7f;
            background: #e8f7f9;
        }

        .booking-block-room input {
            width: 18px;
            height: 18px;
            accent-color: var(--package-input-focus);
        }

        .booking-block-room strong,
        .booking-block-room small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .booking-block-room strong {
            font-size: 0.9rem;
            font-weight: 900;
        }

        .booking-block-room small {
            margin-top: 0.1rem;
            color: #65798b;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .booking-floor-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: 0.3rem;
        }

        .booking-floor-filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 34px;
            margin: 0;
            padding: 0.35rem 0.55rem;
            border: 1px solid rgba(18, 62, 86, 0.14);
            border-radius: 8px;
            background: #fff;
            color: #31506a;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: none;
            cursor: pointer;
        }

        .booking-floor-filter label:has(input:checked) {
            border-color: #0b6f7f;
            background: #e8f7f9;
            color: #0b6f7f;
        }

        .booking-floor-filter input {
            width: 16px;
            height: 16px;
            accent-color: var(--package-input-focus);
        }

        .booking-board {
            background: var(--package-shell-bg);
            border-color: var(--package-shell-border);
            box-shadow: var(--package-shell-shadow), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .booking-board-top {
            background: var(--package-header-bg);
            color: var(--package-text);
            border-bottom: 1px solid var(--package-shell-border);
        }

        .booking-range-note,
        .booking-pill,
        .booking-code,
        .arrival-status,
        .booking-mode-tab.is-active,
        .booking-block-room:has(input:checked),
        .booking-floor-filter label:has(input:checked) {
            background: var(--package-badge-bg);
            color: var(--package-badge-text);
            border-color: var(--package-shell-border);
        }

        .booking-month-form input,
        .booking-field input,
        .booking-field select {
            background: var(--package-input-bg);
            border-color: var(--package-input-border);
            color: var(--package-text);
            box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        }

        .booking-month-form input:focus,
        .booking-field input:focus,
        .booking-field select:focus {
            border-color: var(--package-input-focus);
            box-shadow: var(--package-input-focus-shadow);
        }

        .booking-month-form button,
        .booking-primary-button {
            background: var(--package-button-primary);
            color: #fff;
            box-shadow: 0 14px 28px rgba(16, 35, 59, 0.18);
        }

        .booking-secondary-button {
            background: var(--package-button-secondary-bg);
            color: var(--package-button-secondary-text);
            border: 1px solid var(--package-input-border);
        }

        .booking-calendar-strip,
        .booking-panel,
        .booking-block-room-list {
            background: var(--package-shell-bg);
            border-color: var(--package-shell-border);
        }

        .booking-panel-header {
            background: var(--package-header-bg);
            border-bottom-color: var(--package-shell-border);
        }

        .booking-panel-title,
        .arrival-guest,
        .arrival-room,
        .date-number,
        .booking-insight strong,
        .booking-block-room strong {
            color: var(--package-title);
        }

        .booking-panel-subtitle,
        .date-weekday,
        .date-meta,
        .booking-room-note,
        .arrival-meta,
        .booking-block-room small {
            color: var(--package-muted);
        }

        .booking-field label,
        .booking-insight span {
            color: var(--package-label);
        }

        .booking-date-card,
        .booking-insight,
        .booking-block-fields,
        .booking-block-room,
        .booking-floor-filter label,
        .booking-empty-state,
        .booking-arrival-card {
            background: var(--package-heading-bg);
            border-color: var(--package-heading-border);
            color: var(--package-text);
        }

        .booking-date-card:hover,
        .booking-date-card.is-selected,
        .booking-arrival-card:hover {
            border-color: var(--package-input-focus);
            box-shadow: var(--package-input-focus-shadow), 0 12px 26px rgba(16, 35, 59, 0.08);
        }

        .booking-date-card.is-selected {
            background: var(--package-badge-bg);
        }

        .date-label,
        .arrival-rate,
        .booking-block-summary {
            color: var(--package-badge-text);
        }

        .booking-mode-tab {
            background: var(--package-button-secondary-bg);
            border-color: var(--package-input-border);
            color: var(--package-button-secondary-text);
        }

        .booking-arrival-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 0.75rem;
        }

        .booking-arrival-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.75rem;
            align-items: stretch;
            padding: 0.9rem;
            border-radius: 8px;
            border: 1px solid rgba(18, 62, 86, 0.1);
            background: #fff;
            cursor: pointer;
        }

        .booking-arrival-card:hover {
            border-color: #ff6d38;
            box-shadow: 0 10px 22px rgba(16, 35, 59, 0.1);
        }

        .arrival-guest {
            margin: 0;
            color: #18334d;
            font-size: 1rem;
            font-weight: 850;
        }

        .arrival-meta {
            margin-top: 0.28rem;
            color: #65798b;
            font-size: 0.86rem;
        }

        .arrival-room {
            text-align: left;
            color: #18334d;
            font-weight: 850;
            padding-top: 0.65rem;
            border-top: 1px solid rgba(18, 62, 86, 0.08);
        }

        .arrival-rate {
            display: block;
            margin-top: 0.25rem;
            color: #147254;
        }

        .booking-empty-state {
            padding: 2.2rem 1rem;
            text-align: center;
            color: #65798b;
            border: 1px dashed rgba(18, 62, 86, 0.18);
            border-radius: 8px;
            background: #f7fafc;
        }

        .booking-drag-shell {
            display: grid;
            grid-template-columns: minmax(280px, 0.32fr) minmax(0, 1fr);
            gap: 1rem;
            padding: 1rem;
        }

        .booking-drag-summary {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border: 1px solid var(--package-heading-border);
            border-radius: 8px;
            background: var(--package-heading-bg);
            color: var(--package-text);
            font-size: 0.86rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .booking-drag-board-wrap {
            overflow-x: scroll;
            overflow-y: auto;
            max-height: 74vh;
            border: 1px solid var(--package-shell-border);
            border-radius: 8px;
            background: var(--package-shell-bg);
            scrollbar-gutter: stable both-edges;
            scrollbar-width: auto;
        }

        .booking-drag-board-wrap::-webkit-scrollbar {
            width: 14px;
            height: 14px;
        }

        .booking-drag-board-wrap::-webkit-scrollbar-track {
            background: var(--package-heading-bg);
            border-radius: 999px;
        }

        .booking-drag-board-wrap::-webkit-scrollbar-thumb {
            background: rgba(126, 78, 86, 0.55);
            border: 3px solid var(--package-heading-bg);
            border-radius: 999px;
        }

        .booking-drag-board-wrap::-webkit-scrollbar-thumb:hover {
            background: rgba(126, 78, 86, 0.78);
        }

        .booking-drag-board {
            display: grid;
            min-width: max-content;
            user-select: none;
        }

        .booking-drag-corner,
        .booking-drag-day,
        .booking-drag-room,
        .booking-drag-cell {
            border-right: 1px solid rgba(18, 62, 86, 0.1);
            border-bottom: 1px solid rgba(18, 62, 86, 0.1);
        }

        .booking-drag-corner,
        .booking-drag-day,
        .booking-drag-room {
            position: sticky;
            z-index: 2;
            background: var(--package-header-bg);
        }

        .booking-drag-corner,
        .booking-drag-day {
            top: 0;
            min-height: 58px;
        }

        .booking-drag-corner {
            left: 0;
            z-index: 4;
            display: flex;
            align-items: center;
            padding: 0.65rem;
            color: var(--package-label);
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .booking-drag-day {
            display: grid;
            align-content: center;
            gap: 0.12rem;
            padding: 0.45rem 0.55rem;
            color: var(--package-title);
            font-weight: 900;
            text-align: center;
        }

        .booking-drag-day small {
            color: var(--package-muted);
            font-weight: 800;
        }

        .booking-drag-room {
            left: 0;
            z-index: 3;
            display: grid;
            align-content: center;
            min-height: 56px;
            padding: 0.5rem 0.65rem;
            color: var(--package-title);
            font-weight: 900;
        }

        .booking-drag-room small {
            color: var(--package-muted);
            font-weight: 800;
        }

        .booking-drag-cell {
            min-width: 92px;
            min-height: 56px;
            background: var(--package-heading-bg);
            cursor: crosshair;
        }

        .booking-drag-cell.is-booked,
        .booking-drag-cell.is-restricted {
            background: rgba(178, 34, 34, 0.1);
            color: #9d3030;
            cursor: not-allowed;
        }

        .booking-drag-cell.is-selected {
            background: rgba(11, 111, 127, 0.2);
            box-shadow: inset 0 0 0 2px var(--package-input-focus);
        }

        .booking-drag-cell.is-anchor {
            background: rgba(255, 109, 56, 0.22);
        }

        .booking-drag-cell span {
            display: block;
            padding: 0.4rem;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1.2;
            pointer-events: none;
        }

        .booking-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.32rem 0.55rem;
            border-radius: 999px;
            background: #e8f7f9;
            color: #0b6f7f;
            font-weight: 850;
        }

        .booking-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(178, 34, 34, 0.08);
            color: #aa2f2f;
            border: 1px solid rgba(178, 34, 34, 0.12);
            text-decoration: none;
        }

        .booking-delete:hover {
            background: #aa2f2f;
            color: #fff;
            text-decoration: none;
        }

        @media (max-width: 991.98px) {
            .booking-board-top,
            .booking-workspace,
            .booking-drag-shell {
                grid-template-columns: 1fr;
            }

            .booking-month-form {
                justify-content: flex-start;
            }
        }

        @media (max-width: 575.98px) {
            .booking-field-grid,
            .booking-insight-grid,
            .booking-mode-tabs {
                grid-template-columns: 1fr;
            }

            .booking-date-card {
                flex-basis: 118px;
            }
        }
    </style>

    <div class="container-fluid booking-page">
        @if (session('success'))
            <div class="alert crud-alert mb-4" id="successAlert">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert crud-error mb-4">{{ session('error') }}</div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert crud-error mb-4">{{ $errors->first() }}</div>
        @endif

        <section class="booking-board">
            <div class="booking-board-top">
                <div>
                    <span class="booking-range-note"><i class="fas fa-calendar-day"></i> Next 30 days: {{ $calendarRangeLabel }}</span>
                </div>
                <form method="GET" action="/booking" class="booking-month-form">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Guest, room, res no" aria-label="Search booking">
                    <button type="submit"><i class="fas fa-search mr-1"></i> Search</button>
                </form>
            </div>

            <div class="booking-page-tabs" role="tablist" aria-label="Booking transaction tabs">
                <button type="button" class="booking-page-tab is-active" data-booking-page-tab="form">
                    <i class="fas fa-edit mr-1"></i> Input Booking
                </button>
                <button type="button" class="booking-page-tab" data-booking-page-tab="drag">
                    <i class="fas fa-calendar-alt mr-1"></i> Drag & Drop
                </button>
            </div>

            <div class="booking-tab-panel is-active" data-booking-page-panel="form">
            <div class="booking-calendar-strip" id="bookingCalendarStrip" aria-label="Arrival date calendar">
                @foreach ($calendarDays as $day)
                    <button type="button"
                        class="booking-date-card {{ $day['date'] === $selectedDate ? 'is-selected' : '' }} {{ $day['is_past'] ? 'is-past' : '' }} {{ $day['stay_count'] > 0 ? 'has-stay' : '' }} {{ $day['stay_start'] ? 'stay-start' : '' }} {{ $day['stay_end'] ? 'stay-end' : '' }}"
                        data-date="{{ $day['date'] }}"
                        data-total="{{ $day['total'] }}"
                        data-active="{{ $day['active'] }}"
                        data-cancelled="{{ $day['cancelled'] }}"
                        data-revenue="{{ $day['revenue'] }}"
                        data-stay-count="{{ $day['stay_count'] }}"
                        {{ $day['is_past'] ? 'disabled' : '' }}>
                        <span class="date-weekday">{{ $day['weekday'] }}</span>
                        <span class="date-number">{{ $day['day'] }}</span>
                        <span class="date-meta">{{ $day['month'] }}{{ $day['is_today'] ? ' · Today' : '' }}</span>
                        <span class="date-label">{{ $day['active'] }} active arrival{{ $day['active'] === 1 ? '' : 's' }}</span>
                        <span class="date-stay-band" aria-hidden="true"></span>
                        <span class="date-stay-count">{{ $day['stay_count'] }} stay{{ $day['stay_count'] === 1 ? '' : 's' }}</span>
                    </button>
                @endforeach
            </div>

            <div class="booking-workspace">
                <section class="booking-panel">
                    <div class="booking-panel-header">
                        <div>
                            <h2 class="booking-panel-title">New Reservation</h2>
                            <p class="booking-panel-subtitle">Arrival is driven by the selected date card.</p>
                        </div>
                        <span class="booking-pill" id="selectedDateBadge">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</span>
                    </div>
                    <div class="booking-panel-body">
                        <form method="POST" action="/booking" id="formBooking">
                            @csrf
                            <input type="hidden" name="booking_mode" id="bookingMode" value="{{ old('booking_mode', 'single') }}">
                            <div class="booking-mode-tabs" role="tablist" aria-label="Booking mode">
                                <button type="button" class="booking-mode-tab is-active" data-booking-mode-tab="single">
                                    <i class="fas fa-bed mr-1"></i> Single Booking
                                </button>
                                <button type="button" class="booking-mode-tab" data-booking-mode-tab="blocking">
                                    <i class="fas fa-layer-group mr-1"></i> Blocking Booking
                                </button>
                            </div>
                            <div class="booking-block-fields" id="blockingFields">
                                <div class="booking-field-grid">
                                    <div class="booking-field">
                                        <label for="BlockRoomQty">Rooms to Block</label>
                                        <input type="number" name="BlockRoomQty" id="BlockRoomQty" value="{{ old('BlockRoomQty', 50) }}" min="1" max="100">
                                    </div>
                                    <div class="booking-field">
                                        <label for="BlockClassPreview">Room Class Filter</label>
                                        <select id="BlockClassPreview">
                                            <option value="">All classes</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class['kode'] }}" {{ old('Kelas') === $class['kode'] ? 'selected' : '' }}>
                                                    {{ $class['kode'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="booking-field is-wide">
                                        <label>Floor Filter</label>
                                        <div class="booking-floor-filter" id="blockFloorFilter"></div>
                                    </div>
                                    <div class="booking-field is-wide">
                                        <div class="booking-block-summary" id="blockingSummary">
                                            Check the available rooms to include in this blocking booking.
                                        </div>
                                        <div class="booking-block-room-list" id="blockRoomList"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-field-grid">
                                <div class="booking-field">
                                    <label for="ResNo">Reservation No</label>
                                    <input type="text" name="ResNo" id="ResNo" value="{{ old('ResNo', $nextResNo) }}" required>
                                </div>
                                <div class="booking-field">
                                    <label for="Tipe">Booking Type</label>
                                    <select name="Tipe" id="Tipe">
                                        @foreach ($typeOptions as $option)
                                            <option value="{{ $option }}" {{ old('Tipe', 'Personal') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="booking-field">
                                    <label for="TglIn">Arrival</label>
                                    <input type="date" name="TglIn" id="TglIn" value="{{ $defaultCheckIn }}" data-date-native required>
                                </div>
                                <div class="booking-field">
                                    <label for="TglOut">Departure</label>
                                    <input type="date" name="TglOut" id="TglOut" value="{{ $defaultCheckOut }}" data-date-native required>
                                </div>
                                <div class="booking-field">
                                    <label for="JamIn">Arrival Time</label>
                                    <input type="time" name="JamIn" id="JamIn" value="{{ old('JamIn', '14:00') }}">
                                </div>
                                <div class="booking-field">
                                    <label for="JamOut">Departure Time</label>
                                    <input type="time" name="JamOut" id="JamOut" value="{{ old('JamOut', '13:00') }}">
                                </div>
                                <div class="booking-field is-wide">
                                    <label for="Kode">Room</label>
                                    <select name="Kode" id="Kode" required>
                                        <option value="">Select room</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room['kode'] }}" data-kelas="{{ $room['kelas'] }}"
                                                data-rate="{{ $room['rate'] }}" data-fasilitas="{{ $room['fasilitas'] }}"
                                                {{ old('Kode') === $room['kode'] ? 'selected' : '' }}>
                                                {{ $room['kode'] }} - {{ $room['kelas'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="booking-room-note" id="roomFacility"></div>
                                </div>
                                <div class="booking-field">
                                    <label for="Kelas">Class</label>
                                    <input type="text" name="Kelas" id="Kelas" value="{{ old('Kelas') }}" list="bookingClassOptions">
                                    <datalist id="bookingClassOptions">
                                        @foreach ($classes as $class)
                                            <option value="{{ $class['kode'] }}">{{ $class['nama'] }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="booking-field">
                                    <label for="Person">Pax</label>
                                    <input type="number" name="Person" id="Person" value="{{ old('Person', 1) }}" min="0">
                                </div>
                                <div class="booking-field is-wide">
                                    <label for="OriginalGuest">Guest Name</label>
                                    <input type="text" name="OriginalGuest" id="OriginalGuest" value="{{ old('OriginalGuest') }}" required>
                                </div>
                                <div class="booking-field">
                                    <label for="BookingGuest">Booked By</label>
                                    <input type="text" name="BookingGuest" id="BookingGuest" value="{{ old('BookingGuest') }}">
                                </div>
                                <div class="booking-field">
                                    <label for="Telphone">Phone</label>
                                    <input type="text" name="Telphone" id="Telphone" value="{{ old('Telphone') }}">
                                </div>
                                <div class="booking-field">
                                    <label for="KTP">ID Number</label>
                                    <input type="text" name="KTP" id="KTP" value="{{ old('KTP') }}">
                                </div>
                                <div class="booking-field">
                                    <label for="Usaha">Company / Source</label>
                                    <input type="text" name="Usaha" id="Usaha" value="{{ old('Usaha') }}">
                                </div>
                                <div class="booking-field is-wide">
                                    <label for="Alamat">Address</label>
                                    <input type="text" name="Alamat" id="Alamat" value="{{ old('Alamat') }}">
                                </div>
                                <div class="booking-field">
                                    <label for="Payment">Payment</label>
                                    <select name="Payment" id="Payment">
                                        @foreach ($paymentOptions as $option)
                                            <option value="{{ $option }}" {{ old('Payment', 'PA') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="booking-field">
                                    <label for="Rate">Rate</label>
                                    <input type="text" name="Rate" id="Rate" value="{{ old('Rate') }}" inputmode="numeric">
                                </div>
                                <div class="booking-field">
                                    <label for="Deposit">Deposit</label>
                                    <input type="text" name="Deposit" id="Deposit" value="{{ old('Deposit') }}" inputmode="numeric">
                                </div>
                                <div class="booking-field">
                                    <label for="AcceptBy">Accepted By</label>
                                    <input type="text" name="AcceptBy" id="AcceptBy" value="{{ old('AcceptBy', session('user')) }}">
                                </div>
                                <div class="booking-field is-wide">
                                    <label for="Remark">Stay Note</label>
                                    <input type="text" name="Remark" id="Remark" value="{{ old('Remark') }}">
                                </div>
                            </div>

                            <div class="booking-actions">
                                <button class="booking-primary-button" id="saveButton"><i class="fas fa-check mr-1"></i> Save Booking</button>
                                <button type="button" class="booking-secondary-button" onclick="resetBookingForm()" id="resetButton">Reset</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="booking-panel">
                    <div class="booking-panel-header">
                        <div>
                            <h2 class="booking-panel-title">Booked Rooms</h2>
                            <p class="booking-panel-subtitle" id="selectedDateSubtitle">Rooms booked on {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</p>
                        </div>
                        <span class="booking-pill">{{ $calendarRangeLabel }}</span>
                    </div>
                    <div class="booking-panel-body">
                        <div class="booking-insight-grid">
                            <div class="booking-insight">
                                <span>Booked Rooms</span>
                                <strong id="insightArrivals">0</strong>
                            </div>
                            <div class="booking-insight">
                                <span>Active</span>
                                <strong id="insightActive">0</strong>
                            </div>
                            <div class="booking-insight">
                                <span>Revenue</span>
                                <strong id="insightRevenue">0</strong>
                            </div>
                        </div>

                        <div class="booking-arrival-list" id="arrivalList">
                            @forelse ($bookingRows as $booking)
                                @php
                                    $checkIn = $booking->TglIn ? \Carbon\Carbon::parse($booking->TglIn)->format('Y-m-d') : '';
                                    $checkOut = $booking->TglOut ? \Carbon\Carbon::parse($booking->TglOut)->format('Y-m-d') : '';
                                    $displayIn = $booking->TglIn ? \Carbon\Carbon::parse($booking->TglIn)->format('d M Y') : '-';
                                    $displayOut = $booking->TglOut ? \Carbon\Carbon::parse($booking->TglOut)->format('d M Y') : '-';
                                    $jamIn = $booking->JamIn ? \Carbon\Carbon::parse($booking->JamIn)->format('H:i') : '14:00';
                                    $jamOut = $booking->JamOut ? \Carbon\Carbon::parse($booking->JamOut)->format('H:i') : '13:00';
                                    $status = trim((string) $booking->Status);
                                @endphp
                                <article class="booking-arrival-card" data-booking-card data-date="{{ $checkIn }}"
                                    data-start="{{ $checkIn }}" data-end="{{ $checkOut }}"
                                    data-resno="{{ $booking->ResNo }}" data-resno2="{{ $booking->Resno2 }}"
                                    data-status="{{ $status }}"
                                    data-tipe="{{ $booking->Tipe }}" data-tglin="{{ $checkIn }}"
                                    data-tglout="{{ $checkOut }}" data-jamin="{{ $jamIn }}"
                                    data-jamout="{{ $jamOut }}" data-kode="{{ $booking->Kode }}"
                                    data-kelas="{{ $booking->Kelas }}" data-guest="{{ $booking->OriginalGuest }}"
                                    data-bookingguest="{{ $booking->BookingGuest }}" data-ktp="{{ $booking->KTP }}"
                                    data-alamat="{{ $booking->Alamat }}" data-telphone="{{ $booking->Telphone }}"
                                    data-person="{{ (int) ($booking->Person ?? 0) }}" data-usaha="{{ $booking->Usaha }}"
                                    data-payment="{{ $booking->Payment }}" data-rate="{{ $booking->Rate }}"
                                    data-deposit="{{ $booking->Deposit }}" data-acceptby="{{ $booking->AcceptBy }}"
                                    data-remark="{{ $booking->Remark }}">
                                    <div>
                                        <p class="arrival-guest">{{ $booking->OriginalGuest ?: 'Unnamed Guest' }}</p>
                                        <div class="arrival-meta">
                                            <span class="booking-code">{{ $booking->ResNo }}</span>
                                            <span>{{ $displayIn }} - {{ $displayOut }}</span>
                                            <span>{{ $status }}</span>
                                            <span class="arrival-status" data-stay-status>Stay-over</span>
                                        </div>
                                    </div>
                                    <div class="arrival-room">
                                        {{ $booking->Kode }}
                                        <small class="d-block text-muted">{{ $booking->Kelas }}</small>
                                        <span class="arrival-rate">{{ number_format((float) ($booking->Rate ?? 0), 0, ',', '.') }}</span>
                                        <a href="/booking/{{ $booking->Resno2 }}/delete" class="booking-delete mt-2"
                                            title="Cancel Booking" aria-label="Cancel Booking"
                                            data-confirm-delete="Cancel this booking?">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    </div>
                                </article>
                            @empty
                            @endforelse
                        </div>

                        <div class="booking-empty-state" id="emptyDateState">
                            No booked rooms on this date yet. Select the date and create the first booking.
                        </div>
                    </div>
                </section>
            </div>
            </div>

            <div class="booking-tab-panel" data-booking-page-panel="drag">
                <div class="booking-drag-shell">
                    <section class="booking-panel">
                        <div class="booking-panel-header">
                            <div>
                                <h2 class="booking-panel-title">Drag & Drop Booking</h2>
                                <p class="booking-panel-subtitle">Drag across room/date cells to build a blocking booking.</p>
                            </div>
                        </div>
                        <div class="booking-panel-body">
                            <form method="POST" action="/booking" id="dragBookingForm">
                                @csrf
                                <input type="hidden" name="booking_mode" value="blocking">
                                <input type="hidden" name="ResNo" value="">
                                <input type="hidden" name="TglIn" id="DragTglIn" value="{{ $defaultCheckIn }}">
                                <input type="hidden" name="TglOut" id="DragTglOut" value="{{ $defaultCheckOut }}">
                                <input type="hidden" name="JamIn" value="14:00">
                                <input type="hidden" name="JamOut" value="13:00">
                                <input type="hidden" name="BlockRoomQty" id="DragBlockRoomQty" value="0">
                                <input type="hidden" name="Kelas" id="DragKelas" value="">
                                <input type="hidden" name="Tipe" value="Group">
                                <div id="dragBlockRooms"></div>

                                <div class="booking-drag-summary" id="dragSelectionSummary">
                                    Drag on the grid to select rooms and stay dates.
                                </div>

                                <div class="booking-field-grid">
                                    <div class="booking-field">
                                        <label for="DragStartDate">Board Start</label>
                                        <input type="date" id="DragStartDate" value="{{ $selectedDate }}" data-date-native>
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragDays">Days Shown</label>
                                        <input type="number" id="DragDays" value="14" min="7" max="31">
                                    </div>
                                    <div class="booking-field is-wide">
                                        <label for="DragClassFilter">Room Class</label>
                                        <select id="DragClassFilter">
                                            <option value="">All classes</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class['kode'] }}">{{ $class['kode'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragTglInDisplay">Arrival / TglIn</label>
                                        <input type="text" id="DragTglInDisplay" value="{{ \Carbon\Carbon::parse($defaultCheckIn)->format('d-m-Y') }}" readonly>
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragTglOutDisplay">Departure / TglOut</label>
                                        <input type="text" id="DragTglOutDisplay" value="{{ \Carbon\Carbon::parse($defaultCheckOut)->format('d-m-Y') }}" readonly>
                                    </div>
                                    <div class="booking-field is-wide">
                                        <label for="DragOriginalGuest">Guest Name</label>
                                        <input type="text" name="OriginalGuest" id="DragOriginalGuest" required>
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragBookingGuest">Booked By</label>
                                        <input type="text" name="BookingGuest" id="DragBookingGuest">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragTelphone">Phone</label>
                                        <input type="text" name="Telphone" id="DragTelphone">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragPerson">Pax</label>
                                        <input type="number" name="Person" id="DragPerson" value="1" min="0">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragPayment">Payment</label>
                                        <select name="Payment" id="DragPayment">
                                            @foreach ($paymentOptions as $option)
                                                <option value="{{ $option }}" {{ $option === 'PA' ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragUsaha">Company / Source</label>
                                        <input type="text" name="Usaha" id="DragUsaha">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragRate">Rate</label>
                                        <input type="text" name="Rate" id="DragRate" inputmode="numeric">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragDeposit">Deposit</label>
                                        <input type="text" name="Deposit" id="DragDeposit" inputmode="numeric">
                                    </div>
                                    <div class="booking-field">
                                        <label for="DragAcceptBy">Accepted By</label>
                                        <input type="text" name="AcceptBy" id="DragAcceptBy" value="{{ session('user') }}">
                                    </div>
                                    <div class="booking-field is-wide">
                                        <label for="DragRemark">Stay Note</label>
                                        <input type="text" name="Remark" id="DragRemark" value="DRAG DROP BOOKING">
                                    </div>
                                </div>

                                <div class="booking-actions">
                                    <button class="booking-primary-button" id="dragSaveButton" disabled>
                                        <i class="fas fa-layer-group mr-1"></i> Save Drag Booking
                                    </button>
                                    <button type="button" class="booking-secondary-button" id="dragClearButton">Clear Selection</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <section class="booking-panel">
                        <div class="booking-panel-header">
                            <div>
                                <h2 class="booking-panel-title">Room Calendar</h2>
                                <p class="booking-panel-subtitle">Booked or restricted cells are locked.</p>
                            </div>
                            <span class="booking-pill" id="dragDateRangeBadge">-</span>
                        </div>
                        <div class="booking-panel-body">
                            <div class="booking-drag-board-wrap">
                                <div class="booking-drag-board" id="dragBookingBoard"></div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>

    <script>
        const formBooking = document.getElementById('formBooking');
        const saveButton = document.getElementById('saveButton');
        const resetButton = document.getElementById('resetButton');
        const bookingModeField = document.getElementById('bookingMode');
        const bookingModeTabs = Array.from(document.querySelectorAll('[data-booking-mode-tab]'));
        const blockingFields = document.getElementById('blockingFields');
        const blockRoomQtyField = document.getElementById('BlockRoomQty');
        const blockClassPreviewField = document.getElementById('BlockClassPreview');
        const blockRoomList = document.getElementById('blockRoomList');
        const blockFloorFilter = document.getElementById('blockFloorFilter');
        const resNoField = document.getElementById('ResNo');
        const tipeField = document.getElementById('Tipe');
        const tglInField = document.getElementById('TglIn');
        const tglOutField = document.getElementById('TglOut');
        const jamInField = document.getElementById('JamIn');
        const jamOutField = document.getElementById('JamOut');
        const roomField = document.getElementById('Kode');
        const classField = document.getElementById('Kelas');
        const facilityNote = document.getElementById('roomFacility');
        const guestField = document.getElementById('OriginalGuest');
        const bookingGuestField = document.getElementById('BookingGuest');
        const ktpField = document.getElementById('KTP');
        const addressField = document.getElementById('Alamat');
        const phoneField = document.getElementById('Telphone');
        const personField = document.getElementById('Person');
        const companyField = document.getElementById('Usaha');
        const paymentField = document.getElementById('Payment');
        const rateField = document.getElementById('Rate');
        const depositField = document.getElementById('Deposit');
        const acceptByField = document.getElementById('AcceptBy');
        const remarkField = document.getElementById('Remark');
        const selectedDateBadge = document.getElementById('selectedDateBadge');
        const selectedDateSubtitle = document.getElementById('selectedDateSubtitle');
        const insightArrivals = document.getElementById('insightArrivals');
        const insightActive = document.getElementById('insightActive');
        const insightRevenue = document.getElementById('insightRevenue');
        const emptyDateState = document.getElementById('emptyDateState');
        const defaultAction = '/booking';
        const defaultResNo = @json($nextResNo);
        const oldBookingMode = @json(old('booking_mode', 'single'));
        const allRooms = @json($rooms);
        const data2Occupancies = @json($data2Occupancies);
        const bookingOccupancies = @json($bookingOccupancies);
        const restrictedRoomCodes = new Set(@json($restrictedRoomCodes).map((code) => String(code).toUpperCase()));
        const oldBlockRooms = @json(old('BlockRooms', []));
        const oldBlockFloors = @json(old('BlockFloors', []));
        let manualBlockSelection = new Set(Array.isArray(oldBlockRooms) ? oldBlockRooms.map((code) => String(code).toUpperCase()) : []);
        let selectedBlockFloors = new Set(Array.isArray(oldBlockFloors) ? oldBlockFloors.map((floor) => String(floor)) : []);
        let selectedDate = @json($selectedDate);
        const bookingPageTabs = Array.from(document.querySelectorAll('[data-booking-page-tab]'));
        const bookingPagePanels = Array.from(document.querySelectorAll('[data-booking-page-panel]'));
        const dragBookingForm = document.getElementById('dragBookingForm');
        const dragBookingBoard = document.getElementById('dragBookingBoard');
        const dragStartDateField = document.getElementById('DragStartDate');
        const dragDaysField = document.getElementById('DragDays');
        const dragClassFilter = document.getElementById('DragClassFilter');
        const dragTglInField = document.getElementById('DragTglIn');
        const dragTglOutField = document.getElementById('DragTglOut');
        const dragTglInDisplay = document.getElementById('DragTglInDisplay');
        const dragTglOutDisplay = document.getElementById('DragTglOutDisplay');
        const dragBlockRoomQtyField = document.getElementById('DragBlockRoomQty');
        const dragBlockRooms = document.getElementById('dragBlockRooms');
        const dragSelectionSummary = document.getElementById('dragSelectionSummary');
        const dragDateRangeBadge = document.getElementById('dragDateRangeBadge');
        const dragSaveButton = document.getElementById('dragSaveButton');
        const dragClearButton = document.getElementById('dragClearButton');
        const dragGuestField = document.getElementById('DragOriginalGuest');
        const dragRateField = document.getElementById('DragRate');
        const dragDepositField = document.getElementById('DragDeposit');
        const dragCompanyField = document.getElementById('DragUsaha');
        const dragBookingGuestField = document.getElementById('DragBookingGuest');
        const dragClassField = document.getElementById('DragKelas');
        let dragSelection = null;
        let dragAnchor = null;
        let dragGridRooms = [];
        let dragGridDates = [];
        let isDraggingGrid = false;

        const flowFields = [
            resNoField, tipeField, tglOutField, jamInField, jamOutField, roomField, classField,
            personField, guestField, bookingGuestField, phoneField, ktpField, addressField, companyField,
            paymentField, rateField, depositField, acceptByField, remarkField
        ];

        function normalizeNumber(value) {
            const raw = (value || '').toString().trim();

            if (raw.includes('.')) {
                const parts = raw.split('.');
                if (/^0+$/.test(parts[1] || '')) {
                    return parts[0].replace(/[^\d]/g, '');
                }
            }

            return raw.replace(/[^\d]/g, '');
        }

        function formatRibuan(value) {
            const normalized = normalizeNumber(value);
            return normalized ? normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        }

        function formatDisplayDate(value) {
            const date = new Date(value + 'T00:00:00');
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function formatNumericDisplayDate(value) {
            const date = new Date(value + 'T00:00:00');
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '-');
        }

        function formatCurrency(value) {
            return normalizeNumber(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.') || '0';
        }

        function addOneDay(value) {
            const date = new Date(value + 'T00:00:00');
            date.setDate(date.getDate() + 1);
            return date.toISOString().slice(0, 10);
        }

        function addDays(value, days) {
            const date = new Date(value + 'T00:00:00');
            date.setDate(date.getDate() + days);
            return date.toISOString().slice(0, 10);
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        function getRoomFloor(roomCode) {
            const code = String(roomCode || '').replace(/\D/g, '');

            if (code.length >= 4) {
                return String(parseInt(code.slice(0, 2), 10) || code.slice(0, 2));
            }

            if (code.length === 3) {
                return code.slice(0, 1);
            }

            return '';
        }

        function maxDateValue(values) {
            const dates = values.filter(Boolean).sort();
            return dates.length ? dates[dates.length - 1] : '';
        }

        function renderFloorFilter() {
            const floors = Array.from(new Set(allRooms.map((room) => getRoomFloor(room.kode)).filter(Boolean)))
                .sort((a, b) => Number(a) - Number(b));

            blockFloorFilter.innerHTML = '';

            floors.forEach((floor) => {
                const label = document.createElement('label');
                label.innerHTML =
                    '<input type="checkbox" name="BlockFloors[]" value="' + escapeHtml(floor) + '">' +
                    '<span>Floor ' + escapeHtml(floor) + '</span>';

                const checkbox = label.querySelector('input');
                checkbox.checked = selectedBlockFloors.has(floor);
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedBlockFloors.add(floor);
                    } else {
                        selectedBlockFloors.delete(floor);
                    }

                    renderBlockRooms();
                });

                blockFloorFilter.appendChild(label);
            });
        }

        function setBookingMode(mode) {
            const isBlocking = mode === 'blocking';
            bookingModeField.value = isBlocking ? 'blocking' : 'single';
            bookingModeTabs.forEach((tab) => {
                tab.classList.toggle('is-active', tab.dataset.bookingModeTab === bookingModeField.value);
            });

            blockingFields.classList.toggle('is-active', isBlocking);
            roomField.required = !isBlocking;
            roomField.closest('.booking-field').style.display = isBlocking ? 'none' : '';
            classField.closest('.booking-field').style.display = isBlocking ? 'none' : '';
            blockRoomQtyField.required = isBlocking;
            saveButton.innerHTML = isBlocking
                ? '<i class="fas fa-layer-group mr-1"></i> Save Blocking'
                : '<i class="fas fa-check mr-1"></i> Save Booking';

            if (isBlocking) {
                resNoField.readOnly = false;
                blockClassPreviewField.value = classField.value || blockClassPreviewField.value;
                roomField.value = '';
                facilityNote.textContent = '';
                renderBlockRooms();
            }
        }

        function rangesOverlap(startA, endA, startB, endB) {
            return startA < endB && endA > startB;
        }

        function getBookedRoomCodesForRange() {
            const checkIn = tglInField.value;
            const checkOut = tglOutField.value;
            const booked = new Set();

            if (!checkIn || !checkOut) {
                return booked;
            }

            bookingOccupancies.forEach((row) => {
                const code = String(row.kode || '').toUpperCase();
                const starts = row.tgl_in || '';
                const ends = row.tgl_out || starts;

                if (code && starts && ends && rangesOverlap(starts, ends, checkIn, checkOut)) {
                    booked.add(code);
                }
            });

            data2Occupancies.forEach((row) => {
                const code = String(row.kode || '').toUpperCase();
                const starts = row.tgl_in || '';
                const ends = maxDateValue([row.tgl_out || '', row.tgl_keluar || '']);

                if (code && starts && starts < checkOut && (!ends || ends > checkIn)) {
                    booked.add(code);
                }
            });

            restrictedRoomCodes.forEach((code) => booked.add(code));

            return booked;
        }

        function getAvailableBlockRooms() {
            const booked = getBookedRoomCodesForRange();
            const classText = (blockClassPreviewField.value || '').trim().toUpperCase();

            return allRooms.filter((room) => {
                const code = String(room.kode || '').toUpperCase();
                const roomClass = String(room.kelas || '').toUpperCase();
                const floor = getRoomFloor(code);
                const matchesFloor = selectedBlockFloors.size === 0 || selectedBlockFloors.has(floor);
                return code && !booked.has(code) && (!classText || roomClass === classText) && matchesFloor;
            });
        }

        function setBookingPageTab(tabName) {
            bookingPageTabs.forEach((tab) => {
                tab.classList.toggle('is-active', tab.dataset.bookingPageTab === tabName);
            });

            bookingPagePanels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.bookingPagePanel === tabName);
            });

            if (tabName === 'drag') {
                renderDragBoard();
            }
        }

        function dragDates() {
            const start = dragStartDateField.value || selectedDate;
            const days = Math.max(7, Math.min(parseInt(dragDaysField.value, 10) || 14, 31));
            return Array.from({ length: days }, (_, index) => addDays(start, index));
        }

        function dragRooms() {
            const classText = (dragClassFilter.value || '').trim().toUpperCase();

            return allRooms
                .filter((room) => {
                    const code = String(room.kode || '').toUpperCase();
                    const roomClass = String(room.kelas || '').toUpperCase();
                    return code && (!classText || roomClass === classText);
                })
                .slice(0, 160);
        }

        function dragCellState(roomCode, dateValue) {
            const code = String(roomCode || '').toUpperCase();

            if (restrictedRoomCodes.has(code)) {
                return { locked: true, type: 'restricted', label: 'Restricted' };
            }

            const booking = bookingOccupancies.find((row) => {
                const starts = row.tgl_in || '';
                const ends = row.tgl_out || starts;
                return String(row.kode || '').toUpperCase() === code && starts <= dateValue && ends >= dateValue;
            });

            if (booking) {
                return {
                    locked: true,
                    type: 'booked',
                    label: booking.guest || booking.res_no || 'Booked',
                };
            }

            const data2 = data2Occupancies.find((row) => {
                const starts = row.tgl_in || '';
                const ends = maxDateValue([row.tgl_out || '', row.tgl_keluar || '']);
                return String(row.kode || '').toUpperCase() === code && starts && starts <= dateValue && (!ends || ends >= dateValue);
            });

            if (data2) {
                return { locked: true, type: 'booked', label: 'Occupied' };
            }

            return { locked: false, type: 'available', label: '' };
        }

        function clearDragSelection() {
            dragSelection = null;
            dragAnchor = null;
            dragBlockRooms.innerHTML = '';
            dragBlockRoomQtyField.value = '0';
            dragTglInDisplay.value = dragTglInField.value ? formatNumericDisplayDate(dragTglInField.value) : '';
            dragTglOutDisplay.value = dragTglOutField.value ? formatNumericDisplayDate(dragTglOutField.value) : '';
            dragSaveButton.disabled = true;
            dragSelectionSummary.textContent = 'Drag on the grid to select rooms and stay dates.';
            dragBookingBoard.querySelectorAll('.booking-drag-cell').forEach((cell) => {
                cell.classList.remove('is-selected', 'is-anchor');
            });
        }

        function setDragSelection(anchor, target) {
            const rowStart = Math.min(anchor.row, target.row);
            const rowEnd = Math.max(anchor.row, target.row);
            const dateStart = Math.min(anchor.date, target.date);
            const dateEnd = Math.max(anchor.date, target.date);
            const selectedDates = dragGridDates.slice(dateStart, dateEnd + 1);
            const selectedRooms = dragGridRooms
                .slice(rowStart, rowEnd + 1)
                .filter((room) => selectedDates.every((dateValue) => !dragCellState(room.kode, dateValue).locked));

            dragBookingBoard.querySelectorAll('.booking-drag-cell').forEach((cell) => {
                const row = parseInt(cell.dataset.row, 10);
                const date = parseInt(cell.dataset.date, 10);
                const room = dragGridRooms[row];
                const isInBox = row >= rowStart && row <= rowEnd && date >= dateStart && date <= dateEnd;
                const isSelectedRoom = room && selectedRooms.some((selectedRoom) => selectedRoom.kode === room.kode);

                cell.classList.toggle('is-anchor', row === anchor.row && date === anchor.date);
                cell.classList.toggle('is-selected', isInBox && isSelectedRoom && !cell.classList.contains('is-booked') && !cell.classList.contains('is-restricted'));
            });

            dragBlockRooms.innerHTML = '';
            selectedRooms.forEach((room) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'BlockRooms[]';
                input.value = String(room.kode || '').toUpperCase();
                dragBlockRooms.appendChild(input);
            });

            if (selectedRooms.length < 1 || selectedDates.length < 1) {
                dragSelection = null;
                dragBlockRoomQtyField.value = '0';
                dragSaveButton.disabled = true;
                dragSelectionSummary.textContent = 'No available room is fully open in the selected range.';
                return;
            }

            const startDate = selectedDates[0];
            const endDate = selectedDates[selectedDates.length - 1];
            const classes = Array.from(new Set(selectedRooms.map((room) => String(room.kelas || '').trim()).filter(Boolean)));
            dragSelection = { rooms: selectedRooms, dates: selectedDates };
            dragTglInField.value = startDate;
            dragTglOutField.value = selectedDates.length === 1 ? addOneDay(startDate) : endDate;
            dragTglInDisplay.value = formatNumericDisplayDate(dragTglInField.value);
            dragTglOutDisplay.value = formatNumericDisplayDate(dragTglOutField.value);
            dragBlockRoomQtyField.value = selectedRooms.length;
            dragClassField.value = classes.length === 1 ? classes[0] : '';
            dragSaveButton.disabled = false;
            dragSelectionSummary.textContent = selectedRooms.length + ' room' + (selectedRooms.length === 1 ? '' : 's') +
                ' selected for ' + formatDisplayDate(startDate) + ' - ' + formatDisplayDate(endDate) + '.';
        }

        function renderDragBoard() {
            if (!dragBookingBoard || !dragStartDateField) {
                return;
            }

            dragGridDates = dragDates();
            dragGridRooms = dragRooms();
            dragBookingBoard.innerHTML = '';
            dragBookingBoard.style.gridTemplateColumns = '150px repeat(' + dragGridDates.length + ', 92px)';
            dragDateRangeBadge.textContent = formatDisplayDate(dragGridDates[0]) + ' - ' + formatDisplayDate(dragGridDates[dragGridDates.length - 1]);

            const corner = document.createElement('div');
            corner.className = 'booking-drag-corner';
            corner.textContent = 'Room / Date';
            dragBookingBoard.appendChild(corner);

            dragGridDates.forEach((dateValue) => {
                const date = new Date(dateValue + 'T00:00:00');
                const day = document.createElement('div');
                day.className = 'booking-drag-day';
                day.innerHTML = '<span>' + date.toLocaleDateString('en-GB', { weekday: 'short' }) + '</span><small>' +
                    date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) + '</small>';
                dragBookingBoard.appendChild(day);
            });

            dragGridRooms.forEach((room, rowIndex) => {
                const roomLabel = document.createElement('div');
                roomLabel.className = 'booking-drag-room';
                roomLabel.innerHTML = '<span>' + escapeHtml(room.kode || '-') + '</span><small>' + escapeHtml(room.kelas || '-') + '</small>';
                dragBookingBoard.appendChild(roomLabel);

                dragGridDates.forEach((dateValue, dateIndex) => {
                    const state = dragCellState(room.kode, dateValue);
                    const cell = document.createElement('div');
                    cell.className = 'booking-drag-cell' + (state.locked ? ' is-' + state.type : '');
                    cell.dataset.row = rowIndex;
                    cell.dataset.date = dateIndex;
                    cell.innerHTML = state.label ? '<span>' + escapeHtml(state.label) + '</span>' : '';

                    if (!state.locked) {
                        cell.addEventListener('pointerdown', function(event) {
                            event.preventDefault();
                            isDraggingGrid = true;
                            dragAnchor = { row: rowIndex, date: dateIndex };
                            setDragSelection(dragAnchor, dragAnchor);
                        });

                        cell.addEventListener('pointerenter', function() {
                            if (isDraggingGrid && dragAnchor) {
                                setDragSelection(dragAnchor, { row: rowIndex, date: dateIndex });
                            }
                        });
                    }

                    dragBookingBoard.appendChild(cell);
                });
            });

            clearDragSelection();
        }

        function syncBlockSelectionToTarget(availableRooms) {
            const availableCodes = new Set(availableRooms.map((room) => String(room.kode || '').toUpperCase()));
            manualBlockSelection = new Set(Array.from(manualBlockSelection).filter((code) => availableCodes.has(code)));

            const targetQty = Math.min(parseInt(blockRoomQtyField.value, 10) || 0, 100, availableRooms.length);
            for (const room of availableRooms) {
                if (manualBlockSelection.size >= targetQty) {
                    break;
                }

                manualBlockSelection.add(String(room.kode || '').toUpperCase());
            }
        }

        function renderBlockRooms() {
            const availableRooms = getAvailableBlockRooms();
            syncBlockSelectionToTarget(availableRooms);
            blockRoomList.innerHTML = '';

            availableRooms.forEach((room) => {
                const code = String(room.kode || '').toUpperCase();
                const label = document.createElement('label');
                label.className = 'booking-block-room';
                label.innerHTML =
                    '<input type="checkbox" name="BlockRooms[]" value="' + escapeHtml(code) + '">' +
                    '<span><strong>' + escapeHtml(code) + '</strong><small>' + escapeHtml(room.kelas || '-') + '</small></span>';

                const checkbox = label.querySelector('input');
                checkbox.checked = manualBlockSelection.has(code);
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        manualBlockSelection.add(code);
                    } else {
                        manualBlockSelection.delete(code);
                    }

                    blockRoomQtyField.value = manualBlockSelection.size || 1;
                    updateBlockingSummary(availableRooms);
                });

                blockRoomList.appendChild(label);
            });

            updateBlockingSummary(availableRooms);
        }

        function updateBlockingSummary(availableRooms = getAvailableBlockRooms()) {
            const selectedCount = manualBlockSelection.size;
            const classText = (blockClassPreviewField.value || '').trim();
            const floors = Array.from(selectedBlockFloors).sort((a, b) => Number(a) - Number(b)).join(', ');
            const dateText = tglInField.value && tglOutField.value
                ? formatDisplayDate(tglInField.value) + ' - ' + formatDisplayDate(tglOutField.value)
                : 'selected dates';
            document.getElementById('blockingSummary').textContent = selectedCount + ' selected from ' + availableRooms.length + ' available room(s) for ' + dateText + (classText ? ' in ' + classText + ' class' : ' across all classes') + (floors ? ', floor ' + floors + '.' : '.');
        }

        function applySelectedRoom() {
            const option = roomField.options[roomField.selectedIndex];

            if (!option || !option.value) {
                facilityNote.textContent = '';
                return;
            }

            classField.value = option.dataset.kelas || classField.value;
            rateField.value = formatRibuan(option.dataset.rate || rateField.value);
            facilityNote.textContent = option.dataset.fasilitas ? 'Facility: ' + option.dataset.fasilitas : '';
        }

        function setSelectedDate(dateValue, card) {
            selectedDate = dateValue;
            tglInField.value = dateValue;

            if (!tglOutField.value || tglOutField.value <= dateValue) {
                tglOutField.value = addOneDay(dateValue);
            }

            document.querySelectorAll('.booking-date-card').forEach((item) => {
                item.classList.toggle('is-selected', item.dataset.date === dateValue);
            });

            selectedDateBadge.textContent = formatDisplayDate(dateValue);
            selectedDateSubtitle.textContent = 'Rooms booked on ' + formatDisplayDate(dateValue);
            if (dragStartDateField && dragStartDateField.value !== dateValue) {
                dragStartDateField.value = dateValue;
                renderDragBoard();
            }
            updateDateActivity(card || document.querySelector('.booking-date-card[data-date="' + dateValue + '"]'));
            renderBlockRooms();
        }

        function updateDateActivity(card) {
            const cards = Array.from(document.querySelectorAll('[data-booking-card]'));
            let visibleCount = 0;
            let revenue = 0;

            cards.forEach((item) => {
                const starts = item.dataset.start || item.dataset.date;
                const ends = item.dataset.end || item.dataset.date;
                const isVisible = starts <= selectedDate && ends >= selectedDate;
                const status = item.querySelector('[data-stay-status]');
                item.style.display = isVisible ? '' : 'none';
                if (isVisible) {
                    visibleCount += 1;
                    revenue += parseInt(normalizeNumber(item.dataset.rate), 10) || 0;

                    if (status) {
                        status.textContent = starts === selectedDate ? 'Arrival' : 'Stay-over';
                    }
                }
            });

            insightArrivals.textContent = visibleCount;
            insightActive.textContent = card ? (card.dataset.stayCount || visibleCount) : visibleCount;
            insightRevenue.textContent = formatCurrency(String(card ? card.dataset.revenue : revenue));
            emptyDateState.style.display = visibleCount > 0 ? 'none' : '';
        }

        function fillFormFromCard(card) {
            setBookingMode('single');
            formBooking.action = '/booking/' + card.dataset.resno2 + '/update';
            resNoField.value = card.dataset.resno || '';
            resNoField.readOnly = true;
            tipeField.value = card.dataset.tipe || 'Personal';
            tglInField.value = card.dataset.tglin || selectedDate;
            tglOutField.value = card.dataset.tglout || addOneDay(selectedDate);
            jamInField.value = card.dataset.jamin || '14:00';
            jamOutField.value = card.dataset.jamout || '13:00';
            roomField.value = card.dataset.kode || '';
            classField.value = card.dataset.kelas || '';
            guestField.value = card.dataset.guest || '';
            bookingGuestField.value = card.dataset.bookingguest || '';
            ktpField.value = card.dataset.ktp || '';
            addressField.value = card.dataset.alamat || '';
            phoneField.value = card.dataset.telphone || '';
            personField.value = card.dataset.person || '0';
            companyField.value = card.dataset.usaha || '';
            paymentField.value = card.dataset.payment || 'PA';
            rateField.value = formatRibuan(card.dataset.rate || '');
            depositField.value = formatRibuan(card.dataset.deposit || '');
            acceptByField.value = card.dataset.acceptby || '';
            remarkField.value = card.dataset.remark || '';
            saveButton.innerHTML = '<i class="fas fa-check mr-1"></i> Update Booking';
            resetButton.textContent = 'Cancel Edit';
            applySelectedRoom();
            guestField.focus();
        }

        function resetBookingForm() {
            const currentMode = bookingModeField.value;
            formBooking.reset();
            formBooking.action = defaultAction;
            resNoField.readOnly = false;
            resNoField.value = defaultResNo;
            tglInField.value = selectedDate;
            tglOutField.value = addOneDay(selectedDate);
            resetButton.textContent = 'Reset';
            applySelectedRoom();
            setBookingMode(currentMode);
            guestField.focus();
        }

        document.querySelectorAll('.booking-date-card').forEach((card) => {
            card.addEventListener('click', function() {
                if (this.disabled) {
                    return;
                }

                setSelectedDate(this.dataset.date, this);
                resetBookingForm();
            });
        });

        document.querySelectorAll('[data-booking-card]').forEach((card) => {
            card.addEventListener('click', function(event) {
                if (event.target.closest('a')) {
                    return;
                }

                setSelectedDate(this.dataset.date);
                fillFormFromCard(this);
            });
        });

        [rateField, depositField].forEach((field) => {
            field.addEventListener('input', function() {
                this.value = formatRibuan(this.value);
            });
        });

        roomField.addEventListener('change', applySelectedRoom);

        bookingModeTabs.forEach((tab) => {
            tab.addEventListener('click', function() {
                setBookingMode(this.dataset.bookingModeTab);
            });
        });

        bookingPageTabs.forEach((tab) => {
            tab.addEventListener('click', function() {
                setBookingPageTab(this.dataset.bookingPageTab);
            });
        });

        document.addEventListener('pointerup', function() {
            isDraggingGrid = false;
        });

        [dragStartDateField, dragDaysField, dragClassFilter].forEach((field) => {
            field?.addEventListener('change', renderDragBoard);
            field?.addEventListener('input', renderDragBoard);
        });

        dragClearButton?.addEventListener('click', clearDragSelection);

        [dragRateField, dragDepositField].forEach((field) => {
            field?.addEventListener('input', function() {
                this.value = formatRibuan(this.value);
            });
        });

        blockRoomQtyField.addEventListener('input', function() {
            manualBlockSelection = new Set();
            renderBlockRooms();
        });

        [blockClassPreviewField, tglInField, tglOutField].forEach((field) => {
            field.addEventListener('input', renderBlockRooms);
            field.addEventListener('change', renderBlockRooms);
        });

        blockClassPreviewField.addEventListener('change', function() {
            classField.value = this.value;
        });

        flowFields.forEach((field, index) => {
            field.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (field === roomField) {
                    applySelectedRoom();
                }

                if (index < flowFields.length - 1) {
                    flowFields[index + 1].focus();
                    if (typeof flowFields[index + 1].select === 'function') {
                        flowFields[index + 1].select();
                    }
                    return;
                }

                formBooking.requestSubmit();
            });
        });

        formBooking.addEventListener('submit', function(event) {
            if (bookingModeField.value === 'blocking') {
                classField.value = blockClassPreviewField.value.trim();
                roomField.required = false;
                if (manualBlockSelection.size < 1) {
                    event.preventDefault();
                    alert('Select at least one available room for blocking booking.');
                    return;
                }
            }

            rateField.value = normalizeNumber(rateField.value);
            depositField.value = normalizeNumber(depositField.value);
            guestField.value = guestField.value.trim().toUpperCase();
            bookingGuestField.value = bookingGuestField.value.trim().toUpperCase();
            companyField.value = companyField.value.trim().toUpperCase();
        });

        dragBookingForm?.addEventListener('submit', function(event) {
            if (!dragSelection || dragSelection.rooms.length < 1) {
                event.preventDefault();
                alert('Drag on the calendar to select at least one available room.');
                return;
            }

            if (!dragGuestField.value.trim()) {
                event.preventDefault();
                dragGuestField.focus();
                return;
            }

            dragRateField.value = normalizeNumber(dragRateField.value);
            dragDepositField.value = normalizeNumber(dragDepositField.value);
            dragGuestField.value = dragGuestField.value.trim().toUpperCase();
            dragBookingGuestField.value = dragBookingGuestField.value.trim().toUpperCase();
            dragCompanyField.value = dragCompanyField.value.trim().toUpperCase();
        });

        document.querySelectorAll('[data-confirm-delete]').forEach((link) => {
            link.addEventListener('click', function(event) {
                if (!confirm(this.dataset.confirmDelete)) {
                    event.preventDefault();
                }
            });
        });

        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-8px)';
                setTimeout(() => successAlert.remove(), 300);
            }, 3000);
        }

        renderFloorFilter();
        setBookingMode(oldBookingMode === 'blocking' ? 'blocking' : 'single');
        applySelectedRoom();
        setSelectedDate(selectedDate);
        document.querySelector('.booking-date-card.is-selected')?.scrollIntoView({ inline: 'center', block: 'nearest' });
    </script>

@endsection
