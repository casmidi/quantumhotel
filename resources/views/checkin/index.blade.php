@extends('layouts.app')

@section('title', '')

@section('content')

@include('partials.crud-package-theme')

<style>
    .checkin-page {
        padding: 0 0 2rem;
        color: #10233b;
    }

    .checkin-shell + .checkin-shell {
        margin-top: 1.5rem;
    }

    .checkin-header-side {
        display: flex;
        align-items: stretch;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex: 1 1 420px;
    }

    .checkin-room-note small,
    .checkin-package-note small {
        display: block;
        margin-bottom: 0.18rem;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #8f6a2d;
    }

    .checkin-room-note strong,
    .checkin-package-note strong {
        display: block;
        font-size: 1rem;
        color: #173761;
        line-height: 1.1;
    }

    .checkin-room-note,
    .checkin-package-note {
        min-width: 220px;
        padding: 0.82rem 0.95rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(199, 165, 106, 0.2);
    }

    .registration-details-grid {
        display: grid;
        grid-template-columns: 1.05fr 1.15fr 1fr 0.82fr 1.05fr 1.2fr;
        gap: 0.95rem;
    }

    .checkin-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        min-width: 0;
    }

    .registration-number-item .package-input {
        background: rgba(16, 35, 59, 0.05);
    }

    .checkin-info-label {
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #5f6f84;
    }

    .checkin-info-input {
        font-weight: 700;
        color: #173761;
    }

    .checkin-section {
        margin-bottom: 1.5rem;
        padding: 1.35rem;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .checkin-section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: -1.35rem -1.35rem 1rem;
        padding: 0.9rem 1.1rem;
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
        border-bottom: 1px solid rgba(137, 167, 214, 0.25);
    }

    .checkin-section-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-size: 1.18rem;
        font-weight: 800;
        color: #172b4d;
    }

    .checkin-section-header i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        background: rgba(107, 120, 240, 0.12);
        color: #6b78f0;
        font-size: 1rem;
    }

    .checkin-section-body {
        display: block;
        width: 100%;
    }

    .payment-checkbox-field {
        justify-content: flex-end;
    }

    .payment-checkbox-control {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: calc(3rem + 2px);
        color: #173761;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .payment-checkbox-control input {
        width: 1rem;
        height: 1rem;
    }

    .guest-info-grid {
        display: grid;
        gap: 1rem;
    }

    .guest-info-row {
        display: grid;
        gap: 1rem;
    }

    .guest-info-row-two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .guest-info-row-five {
        grid-template-columns: 1.1fr 1fr 1fr 1.05fr 1fr;
    }

    .guest-info-row-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .address-info-grid {
        display: grid;
        gap: 1rem;
    }

    .address-info-row {
        display: grid;
        gap: 1rem;
    }

    .address-info-row-five {
        grid-template-columns: 0.95fr 1fr 0.95fr 1fr 0.9fr;
    }

    .room-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .room-info-column {
        display: grid;
        gap: 1rem;
    }

    .address-section .checkin-section-header i {
        background: rgba(107, 120, 240, 0.12);
        color: #6b78f0;
    }

    .guest-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .guest-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .address-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .address-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .room-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .room-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .detail-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .detail-info-column {
        display: grid;
        gap: 1rem;
    }

    .detail-info-row-two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .detail-info-field {
        display: flex;
        flex-direction: column;
        gap: 0.42rem;
        min-width: 0;
    }

    .detail-info-field label {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #243b63;
    }

    .room-allocation-note {
        font-size: 0.84rem;
        color: #6f7f96;
        font-weight: 600;
    }

    .room-allocation-wrap {
        border: 1px solid #e3eaf6;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .room-allocation-table {
        width: 100%;
        border-collapse: collapse;
    }

    .room-allocation-table thead th {
        padding: 0.85rem 0.95rem;
        font-size: 0.83rem;
        font-weight: 800;
        color: #4c5d78;
        text-align: left;
        background: #f8fbff;
        border-bottom: 1px solid #e3eaf6;
    }

    .room-allocation-table tbody td {
        padding: 0.7rem 0.95rem;
        border-bottom: 1px solid #edf2f9;
        vertical-align: middle;
    }

    .room-allocation-table tbody tr:last-child td {
        border-bottom: none;
    }

    .room-allocation-table .package-input,
    .room-allocation-table .package-select {
        height: calc(2.55rem + 2px);
        border-radius: 10px;
        border-color: #e2e8f0;
        background: #fff;
    }

    .room-display-text {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-height: calc(2.55rem + 2px);
        color: #223a5e;
        font-weight: 700;
    }

    .room-main-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.16rem 0.45rem;
        border-radius: 999px;
        background: rgba(86, 111, 255, 0.1);
        color: #4f63dd;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .room-muted-display {
        color: #7f8ea5;
        font-weight: 600;
    }

    .room-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border: 1px solid rgba(220, 38, 38, 0.14);
        border-radius: 999px;
        background: rgba(239, 68, 68, 0.06);
        color: #ef4444;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .room-action-btn:hover {
        background: #ef4444;
        color: #fff;
    }

    .room-summary {
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
        padding: 0.85rem 1rem;
        border-top: 1px solid #e3eaf6;
        background: #fafcff;
        font-size: 0.85rem;
        color: #4c5d78;
        font-weight: 700;
    }

    .guest-info-field .package-input,
    .guest-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .address-info-field .package-input,
    .address-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .room-info-field .package-input,
    .room-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .detail-info-field .package-input,
    .detail-info-field .package-select {
        height: calc(3rem + 2px);
        border-radius: 14px;
        border-color: #e2e8f0;
        background: #fff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.92);
    }

    .guest-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .address-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .room-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .detail-info-field .package-input::placeholder {
        color: #9ca7b8;
        font-weight: 600;
    }

    .guest-info-field .package-input:focus,
    .guest-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .address-info-field .package-input:focus,
    .address-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .room-info-field .package-input:focus,
    .room-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .detail-info-field .package-input:focus,
    .detail-info-field .package-select:focus {
        border-color: #7c89f8;
        box-shadow: 0 0 0 0.2rem rgba(124, 137, 248, 0.08);
    }

    .guest-contact-input {
        position: relative;
    }

    .guest-contact-input .package-input {
        padding-left: 2.8rem;
    }

    .guest-contact-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #7b8798;
        font-size: 1.15rem;
        pointer-events: none;
    }

    .checkin-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.05rem 1.2rem;
    }

    .checkin-column {
        display: grid;
        gap: 0.75rem;
    }

    .checkin-row {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.7rem;
    }

    .checkin-row-label {
        font-size: 0.85rem;
        font-weight: 900;
        color: #233f6b;
        letter-spacing: 0.01em;
    }

    .checkin-row .package-input,
    .checkin-row .package-select,
    .checkin-row textarea.package-input {
        height: calc(2.55rem + 2px);
        font-size: 0.96rem;
        font-weight: 700;
        min-width: 0;
    }

    .checkin-row textarea.package-input {
        min-height: 88px;
        resize: vertical;
        padding-top: 0.7rem;
    }

    .checkin-row-note {
        min-width: 74px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #8f6a2d;
        text-align: left;
    }

    .checkin-row-note.is-stack {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        color: #173761;
    }

    .checkin-row-note.is-stack input {
        width: 1rem;
        height: 1rem;
    }

    .checkin-row-note.is-empty {
        min-width: 0;
    }

    .checkin-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }

    .checkin-actions-main,
    .checkin-actions-side {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .checkin-directory-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .checkin-directory-head > div {
        min-width: 0;
    }

    .checkin-search-form {
        display: flex;
        align-items: flex-end;
        gap: 0.8rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .checkin-search-group {
        flex: 1 1 320px;
    }

    .checkin-search-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .checkin-table-meta {
        font-size: 0.82rem;
        color: #6b7b90;
        line-height: 1.55;
    }

    .checkin-table thead th {
        background: linear-gradient(180deg, rgba(240, 246, 255, 0.98), rgba(225, 235, 248, 0.95));
        border-bottom: 2px solid rgba(30, 75, 128, 0.2);
        color: #10233b;
        font-size: 0.77rem;
        font-weight: 900;
        letter-spacing: 0.11em;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.9);
    }

    .checkin-table .package-code {
        min-width: 132px;
    }

    .checkin-table .room-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 0.36rem 0.72rem;
        border-radius: 999px;
        background: rgba(199, 165, 106, 0.13);
        color: #8f6a2d;
        font-weight: 800;
    }

    .checkin-table .guest-block strong {
        display: block;
        color: #10233b;
    }

    .checkin-table .guest-block span {
        display: block;
        margin-top: 0.2rem;
        color: #6b7b90;
        font-size: 0.8rem;
    }

    .checkin-table .nominal-cell {
        font-weight: 800;
        color: #173761;
    }

    .checkin-table tbody tr.is-active {
        background: rgba(30, 75, 128, 0.08) !important;
        box-shadow: inset 4px 0 0 #1e4b80;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .status-stay {
        background: rgba(30, 75, 128, 0.12);
        color: #173761;
    }

    .status-checkout {
        background: rgba(33, 150, 83, 0.12);
        color: #1c6b40;
    }

    .package-table .checkin-delete-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(178, 34, 34, 0.08);
        color: #aa2f2f;
        border: 1px solid rgba(178, 34, 34, 0.12);
        text-decoration: none;
    }

    .package-table .checkin-delete-link:hover {
        background: #aa2f2f;
        color: #fff;
        text-decoration: none;
    }

    @media (max-width: 1199.98px) {
        .checkin-top-info,
        .checkin-form-grid,
        .guest-info-row-five,
        .guest-info-row-three,
        .address-info-row-five,
        .room-info-grid,
        .detail-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .room-allocation-table {
            min-width: 820px;
        }
    }

    @media (max-width: 767.98px) {
        .checkin-page {
            padding-bottom: 1rem;
        }

        .checkin-header-side,
        .checkin-search-form,
        .checkin-search-actions,
        .checkin-actions,
        .checkin-actions-main,
        .checkin-actions-side {
            align-items: stretch;
            flex-direction: column;
            width: 100%;
        }

        .checkin-header-side {
            justify-content: stretch;
        }

        .checkin-room-note,
        .checkin-package-note,
        .checkin-search-group,
        .checkin-search-actions .btn,
        .checkin-actions .btn {
            width: 100%;
        }

        .registration-details-grid,
        .checkin-form-grid,
        .guest-info-row-two,
        .guest-info-row-three,
        .guest-info-row-five,
        .address-info-row-five,
        .room-info-grid,
        .detail-info-grid {
            grid-template-columns: 1fr;
        }

        .detail-info-row-two {
            grid-template-columns: 1fr;
        }

        .room-allocation-wrap {
            overflow-x: auto;
        }

        .checkin-section {
            padding: 1rem;
            border-radius: 16px;
        }

        .checkin-section-header {
            margin: -1rem -1rem 0.85rem;
            padding: 0.8rem 0.95rem;
        }

        .checkin-row {
            grid-template-columns: 1fr;
            gap: 0.4rem;
            padding: 0.2rem 0;
        }

        .checkin-row-note.is-empty {
            display: none;
        }

        .checkin-row .package-input,
        .checkin-row .package-select,
        .checkin-row textarea.package-input,
        .package-date-picker {
            height: calc(2.75rem + 2px);
        }
    }

    @media (max-width: 420px) {
        .checkin-section {
            padding: 0.85rem;
        }

        .checkin-section-header h3 {
            font-size: 1.02rem;
        }

        .checkin-section-header {
            margin: -0.85rem -0.85rem 0.8rem;
            padding: 0.72rem 0.85rem;
        }

        .guest-info-field label,
        .checkin-row-label {
            font-size: 0.82rem;
        }
    }
</style>

<div class="container-fluid checkin-page">
    @if (session('success'))
        <div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert package-error mb-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert package-error mb-4">{{ $errors->first() }}</div>
    @endif

    <section class="package-shell checkin-shell">
        <div class="package-shell-header">
            <div class="package-shell-heading-block">
                <h1 class="package-shell-title">Check In</h1>
                <p class="package-shell-subtitle">Enter active guest details and choose a room with a cleaner form.</p>
            </div>
            <div class="checkin-header-side">
                <div class="checkin-room-note" id="roomHelper">
                    <small>Room Status</small>
                    <strong>Select an available room</strong>
                </div>
                <div class="checkin-package-note" id="packageHelper">
                    <small>Package Note</small>
                    <strong>Nominal will be filled automatically when a package is found</strong>
                </div>
            </div>
        </div>
        <div class="package-shell-body">
            <form method="POST" action="/checkin" id="checkinForm" autocomplete="off">
                @csrf
                <input type="hidden" id="CurrentDetailKey" value="">

                <div class="checkin-section registration-details-section">
                    <div class="checkin-section-header">
                        <h3><i class="fas fa-id-card"></i> Registration Details</h3>
                    </div>
                    <div class="registration-details-grid">
                        <div class="checkin-info-item registration-number-item">
                            <span class="checkin-info-label">Reg. Number</span>
                            <input type="text" name="GeneratedRegNo" id="GeneratedRegNo"
                                class="form-control package-input checkin-info-input"
                                value="{{ old('GeneratedRegNo', $nextRegNo) }}" data-flow data-vb="xRegNo" readonly>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Type of Check In</span>
                            <select name="TypeOfCheckIn" id="TypeOfCheckIn" class="form-control package-select"
                                data-flow data-vb="xTipe" required>
                                @foreach ($typeOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ old('TypeOfCheckIn', $defaultTypeOfCheckIn) === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Check In <span class="text-danger">*</span></span>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="CheckInDate" id="CheckInDate" value="{{ $checkInIso }}">
                                <input type="text" id="CheckInDateDisplay" class="form-control package-input"
                                    value="{{ \Carbon\Carbon::parse($checkInIso)->format('d-m-Y') }}"
                                    placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglIn" required>
                                <button type="button" class="package-date-picker" data-date-button
                                    aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native
                                    value="{{ $checkInIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Time <span class="text-danger">*</span></span>
                            <input type="text" name="CheckInTime" id="CheckInTime" class="form-control package-input"
                                value="{{ old('CheckInTime', now()->format('H:i:s')) }}" placeholder="HH:mm:ss"
                                inputmode="numeric" maxlength="8" pattern="^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$"
                                data-flow data-vb="xJamIn" required>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Reservation Number</span>
                            <select name="ReservationNumber" id="ReservationNumber" class="form-control package-select"
                                data-flow data-vb="xResNo">
                                <option value="">Select reservation number</option>
                                @foreach ($reservationNumberOptions as $option)
                                    <option value="{{ $option['resno'] }}"
                                        data-address="{{ $option['address'] }}"
                                        data-phone="{{ $option['phone'] }}"
                                        data-remarks="{{ $option['remarks'] }}"
                                        data-original-guest="{{ $option['original_guest'] }}"
                                        data-room-code="{{ $option['room_code'] }}"
                                        data-room-class="{{ $option['room_class'] }}"
                                        data-accept-by="{{ $option['accept_by'] }}"
                                        data-status="{{ $option['status'] }}"
                                        data-check-in-date="{{ $option['check_in_date'] }}"
                                        data-nationality="{{ $option['nationality'] }}"
                                        {{ old('ReservationNumber') === $option['resno'] ? 'selected' : '' }}>
                                        {{ $option['resno'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="checkin-info-item">
                            <span class="checkin-info-label">Estimation Checkout <span class="text-danger">*</span></span>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="EstimationOut" id="EstimationOut" value="{{ $checkOutIso }}">
                                <input type="text" id="EstimationOutDisplay" class="form-control package-input"
                                    value="{{ \Carbon\Carbon::parse($checkOutIso)->format('d-m-Y') }}"
                                    placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglKeluar" required>
                                <button type="button" class="package-date-picker" data-date-button
                                    aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native
                                    value="{{ $checkOutIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkin-section guest-info-section">
                    <div class="checkin-section-header">
                        <h3><i class="fas fa-user-circle"></i> Guest Information</h3>
                    </div>
                    <div class="guest-info-grid">
                        <div class="guest-info-row guest-info-row-two">
                            <div class="guest-info-field">
                                <label for="GuestName">Guest Name<span class="text-danger">*</span></label>
                                <input type="text" name="GuestName" id="GuestName" class="form-control package-input"
                                    value="{{ old('GuestName') }}" placeholder="Budi Santoso" data-flow data-vb="xGuest" required>
                            </div>
                            <div class="guest-info-field">
                                <label for="GuestName2">Name II</label>
                                <input type="text" name="GuestName2" id="GuestName2" class="form-control package-input"
                                    value="{{ old('GuestName2') }}" placeholder="Enter name II (optional)" data-flow data-vb="xGuest2">
                            </div>
                        </div>

                        <div class="guest-info-row guest-info-row-five">
                            <div class="guest-info-field">
                                <label for="PlaceOfBirth">Place of Birth</label>
                                <input type="text" name="PlaceOfBirth" id="PlaceOfBirth"
                                    class="form-control package-input" value="{{ old('PlaceOfBirth') }}"
                                    data-vb="xPlaceBirth"
                                    placeholder="Jakarta" data-flow>
                            </div>
                            <div class="guest-info-field">
                                <label for="BirthDateDisplay">Date of Birth</label>
                                <div class="package-date-group" data-date-field>
                                    <input type="hidden" name="BirthDate" id="BirthDate" value="{{ $birthIso }}">
                                    <input type="text" id="BirthDateDisplay" class="form-control package-input"
                                        value="{{ $birthIso ? \Carbon\Carbon::parse($birthIso)->format('d-m-Y') : '' }}"
                                        placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xTglLahir">
                                    <button type="button" class="package-date-picker" data-date-button
                                        aria-label="Open system date picker"><i
                                            class="fa-regular fa-calendar"></i></button>
                                    <input type="date" class="package-date-native" data-date-native
                                        value="{{ $birthIso }}" tabindex="-1" aria-hidden="true">
                                </div>
                            </div>
                            <div class="guest-info-field">
                                <label for="TypeOfId">Type of ID</label>
                                <select name="TypeOfId" id="TypeOfId" class="form-control package-select" data-flow data-vb="xTypeId">
                                    @foreach ($idTypeOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('TypeOfId', $defaultIdType) === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="guest-info-field">
                                <label for="IdNumber">ID Number</label>
                                <input type="text" name="IdNumber" id="IdNumber" class="form-control package-input"
                                    value="{{ old('IdNumber') }}" placeholder="3171234567890001" data-flow data-vb="xKTP">
                            </div>
                            <div class="guest-info-field">
                                <label for="ExpiredDateDisplay">Expired Date</label>
                                <div class="package-date-group" data-date-field>
                                    <input type="hidden" name="ExpiredDate" id="ExpiredDate" value="{{ $expiredIso }}">
                                    <input type="text" id="ExpiredDateDisplay" class="form-control package-input"
                                        value="{{ $expiredIso ? \Carbon\Carbon::parse($expiredIso)->format('d-m-Y') : '' }}"
                                        placeholder="dd-MM-yyyy" inputmode="numeric" data-flow data-vb="xExpired">
                                    <button type="button" class="package-date-picker" data-date-button
                                        aria-label="Open system date picker"><i
                                            class="fa-regular fa-calendar"></i></button>
                                    <input type="date" class="package-date-native" data-date-native
                                        value="{{ $expiredIso }}" tabindex="-1" aria-hidden="true">
                                </div>
                            </div>
                        </div>

                        <div class="guest-info-row guest-info-row-two">
                            <div class="guest-info-field">
                                <label for="Phone">Phone</label>
                                <div class="guest-contact-input">
                                    <span class="guest-contact-icon"><i class="fa fa-phone"></i></span>
                                    <input type="text" name="Phone" id="Phone" class="form-control package-input"
                                        value="{{ old('Phone') }}" placeholder="0812-3456-7890" data-flow data-vb="xPhone">
                                </div>
                            </div>
                            <div class="guest-info-field">
                                <label for="Email">Email</label>
                                <div class="guest-contact-input">
                                    <span class="guest-contact-icon"><i class="fa fa-envelope-o"></i></span>
                                    <input type="email" name="Email" id="Email" class="form-control package-input"
                                        value="{{ old('Email') }}" placeholder="budi.santoso@email.com" data-flow data-vb="xEmail">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="checkin-section address-section">
                    <div class="checkin-section-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
                    </div>
                    <div class="address-info-grid">
                        <div class="address-info-row">
                            <div class="address-info-field">
                                <label for="Address">Address</label>
                                <input type="text" name="Address" id="Address" class="form-control package-input"
                                    value="{{ old('Address') }}"
                                    placeholder="Jl. Sudirman No. 123, Karet Semanggi, Setiabudi, Jakarta Selatan"
                                    data-flow data-vb="xAlamat">
                            </div>
                        </div>
                        <div class="address-info-row address-info-row-five">
                            <div class="address-info-field">
                                <label for="Kelurahan">Kelurahan</label>
                                <input type="text" name="Kelurahan" id="Kelurahan" class="form-control package-input"
                                    value="{{ old('Kelurahan') }}" placeholder="Karet" data-flow data-vb="xKelurahan">
                            </div>
                            <div class="address-info-field">
                                <label for="Kecamatan">Kecamatan</label>
                                <input type="text" name="Kecamatan" id="Kecamatan" class="form-control package-input"
                                    value="{{ old('Kecamatan') }}" placeholder="Setiabudi" data-flow data-vb="xKecamatan">
                            </div>
                            <div class="address-info-field">
                                <label for="KabCity">City / Kab</label>
                                <input type="text" name="KabCity" id="KabCity" class="form-control package-input"
                                    value="{{ old('KabCity') }}" placeholder="Jakarta Selatan" data-flow data-vb="xKota">
                            </div>
                            <div class="address-info-field">
                                <label for="ProvinceCountry">Province / Country</label>
                                <select name="ProvinceCountry" id="ProvinceCountry" class="form-control package-select"
                                    data-flow data-vb="xPropinsi">
                                    <option value="">Select province / country</option>
                                    @foreach ($provinceOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('ProvinceCountry') === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="address-info-field">
                                <label for="Nationality">Nationality<span class="text-danger">*</span></label>
                                <select name="Nationality" id="Nationality" class="form-control package-select"
                                    data-flow data-vb="xKodeNegara">
                                    @foreach ($nationalityOptions as $option)
                                        <option value="{{ $option }}"
                                            {{ old('Nationality', 'INA') === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROOMS SECTION -->
                <div class="checkin-section">
                    <div class="checkin-section-header">
                        <h3><i class="fas fa-bed"></i> Rooms (3)</h3>
                        <span class="room-allocation-note">All rooms will be billed to this registration (master
                            folio).</span>
                        <button type="button" class="btn package-btn-add" id="addRoomRowButton"
                            style="margin-left: auto;"><i class="fa-solid fa-plus mr-1"></i>Add Room</button>
                    </div>
                    <div class="room-allocation-wrap">
                        <table class="room-allocation-table">
                            <thead>
                                <tr>
                                    <th width="16%">Room Number</th>
                                    <th width="24%">Guest Name</th>
                                    <th width="9%">Pax</th>
                                    <th width="14%">Package Code</th>
                                    <th width="29%">Nominal</th>
                                    <th width="8%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="hidden" name="DetailKeyList[]" id="PrimaryDetailKey"
                                            value="{{ $firstDetailKey }}">
                                        <select name="RoomCodeList[]" id="RoomCode" class="form-control package-select"
                                            data-flow data-vb="xKode" required>
                                            <option value="">Select room</option>
                                            @foreach ($rooms as $room)
                                                <option value="{{ $room['kode'] }}"
                                                    {{ $firstRoomCode === $room['kode'] ? 'selected' : '' }}>
                                                    {{ $room['kode'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="room-display-text">
                                            <span id="PrimaryRoomGuestName">{{ old('GuestName') ?: '-' }}</span>
                                            <span class="room-main-badge">Main Guest</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" id="PrimaryRoomPaxDisplay"
                                            class="form-control package-input text-right" value="{{ old('NumberOfPerson', 2) }}"
                                            min="1" max="20">
                                    </td>
                                    <td>
                                        <select name="PackageCodeList[]" id="PackageCode"
                                            class="form-control package-select" data-flow data-vb="xPackage">
                                            <option value="">Select package</option>
                                            @foreach ($packages as $package)
                                                <option value="{{ $package['kode'] }}"
                                                    {{ $firstPackageCode === $package['kode'] ? 'selected' : '' }}>
                                                    {{ $package['kode'] }} - {{ $package['nama'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" id="NominalDisplayVisible"
                                            class="form-control package-input text-right room-muted-display"
                                            data-vb="xNominal"
                                            value="{{ $firstNominal ? number_format((float) preg_replace('/[^\d]/', '', (string) $firstNominal), 0, ',', '.') : '' }}"
                                            readonly>
                                        <input type="hidden" id="NominalDisplay" name="NominalList[]"
                                            value="{{ $firstNominal ? preg_replace('/[^\d]/', '', (string) $firstNominal) : '' }}">
                                        <input type="hidden" name="BreakfastList[]" id="Breakfast"
                                            value="{{ $firstBreakfast }}" data-vb="xBF">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="room-action-btn" id="clearPrimaryRoomButton"
                                            title="Clear room"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="additionalRoomBody"></tbody>
                        </table>
                        <div class="room-summary">
                            <span>Total Rooms: <strong id="RoomSummaryCount">1</strong></span>
                            <span>Total Pax: <strong id="RoomSummaryPax">2</strong></span>
                        </div>
                    </div>
                </div>

    <!-- ADDITIONAL DETAILS SECTION -->
    <div class="checkin-section">
        <div class="checkin-section-header">
            <h3><i class="fas fa-clipboard-list"></i> Additional Details</h3>
        </div>
        <div class="detail-info-grid">
            <div class="detail-info-column">
                <div class="detail-info-field">
                    <label for="GroupPosition">Group Position</label>
                    <input type="text" name="GroupPosition" id="GroupPosition" class="form-control package-input"
                        value="{{ old('GroupPosition') }}" data-flow data-vb="xPosisi">
                </div>
                <div class="detail-info-row-two">
                    <div class="detail-info-field">
                        <label for="Religion">Religion</label>
                        <select name="Religion" id="Religion" class="form-control package-select" data-flow data-vb="xAgama">
                            <option value="">Select</option>
                            @foreach ($religionOptions as $option)
                                <option value="{{ $option }}" {{ old('Religion') === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="detail-info-field">
                        <label for="NumberOfPerson">Number of Person</label>
                        <input type="number" name="NumberOfPerson" id="NumberOfPerson"
                            class="form-control package-input text-right" value="{{ old('NumberOfPerson', 2) }}"
                            data-vb="xPerson"
                            min="1" max="20" data-flow required>
                    </div>
                </div>
            </div>

            <div class="detail-info-column">
                <div class="detail-info-field">
                    <label for="Company">Company</label>
                    <select name="Company" id="Company" class="form-control package-select" data-flow data-vb="xUsaha">
                        <option value="">Select company</option>
                        @foreach ($companyOptions as $option)
                            <option value="{{ $option }}" {{ old('Company') === $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="detail-info-field">
                    <label for="Segment">Segment</label>
                    <select name="Segment" id="Segment" class="form-control package-select" data-flow data-vb="xSegment">
                        <option value="">Select</option>
                        @foreach ($segmentOptions as $option)
                            <option value="{{ $option }}"
                                {{ old('Segment', 'TRAVEL') === $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="detail-info-field">
                    <label for="Remarks">Remarks</label>
                    <input type="text" name="Remarks" id="Remarks" class="form-control package-input"
                        value="{{ old('Remarks') }}" data-flow data-vb="xRemark">
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT INFORMATION SECTION -->
    <div class="checkin-section payment-info-section">
        <div class="checkin-section-header">
            <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
        </div>
        <div class="checkin-section-body payment-info-body">
            <div class="detail-info-grid payment-info-grid">
                <div class="detail-info-column">
                    <div class="detail-info-field">
                        <label for="PaymentMethod">Payment Method</label>
                        <select name="PaymentMethod" id="PaymentMethod" class="form-control package-select" data-flow
                            data-vb="xPayment" required>
                            @foreach ($paymentOptions as $option)
                                <option value="{{ $option }}"
                                    {{ old('PaymentMethod', 'OTA') === $option ? 'selected' : '' }}>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="detail-info-row-two">
                        <div class="detail-info-field">
                            <label for="CreditCardNumber">Credit Card #</label>
                            <input type="text" name="CreditCardNumber" id="CreditCardNumber"
                                class="form-control package-input" value="{{ old('CreditCardNumber') }}" data-flow data-vb="xCreditCard">
                        </div>
                        <div class="detail-info-field payment-checkbox-field">
                            <label for="CheckDeposit">Deposit</label>
                            <label class="payment-checkbox-control" for="CheckDeposit"><input type="checkbox"
                                    name="CheckDeposit" id="CheckDeposit" value="1" data-vb="xPeriksa"
                                    {{ old('CheckDeposit') ? 'checked' : '' }}>
                                Check Deposit</label>
                        </div>
                    </div>
                </div>

                <div class="detail-info-column">
                    <div class="detail-info-field">
                        <label for="Member">Member 1</label>
                        <input type="text" name="Member" id="Member" class="form-control package-input"
                            value="{{ old('Member') }}" data-flow data-vb="xMember">
                    </div>
                    <div class="detail-info-field">
                        <label for="Sales">Sales</label>
                        <select name="Sales" id="Sales" class="form-control package-select" data-flow data-vb="xSales">
                            <option value="">Select sales</option>
                            @foreach ($salesOptions as $option)
                                <option value="{{ $option }}" {{ old('Sales') === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="checkin-actions">
        <div class="checkin-actions-main">
            <button type="submit" class="btn package-btn-primary" id="saveButton">Save Check In</button>
            <button type="button" class="btn package-btn-secondary" id="newEntryButton">New
                Entry</button>
            <button type="button" class="btn package-btn-secondary" id="focusSearchButton">Search
                Data</button>
        </div>
    </div>
    </form>
    </div>
    </section>

    <section class="package-shell checkin-shell" id="checkinDirectoryShell">
        <div class="package-shell-body">
            <div class="checkin-directory-head">
                <div>
                    <h3 class="package-grid-title mb-1">Check In Directory</h3>
                    <p class="package-grid-note mb-0">Klik salah satu baris untuk memuat data ke form di atas.</p>
                </div>
                <div class="checkin-table-meta">
                    Active: <strong>{{ number_format($summary['active'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
                    Rooms Ready: <strong>{{ number_format($summary['rooms_ready'], 0, ',', '.') }}</strong>
                    &nbsp;|&nbsp;
                    Active Packages: <strong>{{ number_format($summary['packages'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <form method="GET" action="/checkin" class="checkin-search-form" id="checkinSearchForm">
                <div class="checkin-search-group">
                    <label class="package-label" for="searchKeyword">Search Keyword</label>
                    <input type="text" name="search" id="searchKeyword" class="form-control package-input"
                        value="{{ $search }}" placeholder="Search reg number, room, guest, or package">
                </div>
                <div class="checkin-search-actions">
                    <button type="submit" class="btn package-btn-primary"><i
                            class="fa-solid fa-magnifying-glass mr-2"></i>Search</button>
                    <a href="/checkin" class="btn package-btn-secondary">Clear</a>
                </div>
            </form>
            <div class="package-table-wrap mt-4">
                <table class="table package-table checkin-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Reg Number</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Check In</th>
                            <th>Est. Out</th>
                            <th>Package</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkins as $record)
                            <tr class="checkin-record-row" data-record="{{ e($record->record_json) }}"
                                data-detail-key="{{ $record->RegNo2 }}">
                                <td>{{ $record->id ?? '-' }}</td>
                                <td>
                                    @if ($record->guest_status === 'CHECKOUT')
                                        <span class="status-badge status-checkout">CHECKOUT</span>
                                    @else
                                        <span class="status-badge status-stay">STAY</span>
                                    @endif
                                </td>
                                <td><span class="package-code">{{ $record->RegNo }}</span></td>
                                <td><span class="room-pill">{{ $record->Kode }}</span></td>
                                <td>
                                    <div class="guest-block">
                                        <strong>{{ $record->Guest }}</strong>
                                        <span>{{ $record->Tipe ?: 'CHECK IN' }}</span>
                                    </div>
                                </td>
                                <td>{{ $record->check_in_date }}</td>
                                <td>{{ $record->check_out_date }}</td>
                                <td>{{ $record->Package ?: '-' }}</td>
                                <td class="text-right nominal-cell">Rp {{ $record->nominal_display }}</td>
                                <td class="text-center">
                                    <a href="/checkin/{{ urlencode($record->RegNo2) }}/delete"
                                        class="checkin-delete-link"
                                        onclick="event.stopPropagation(); return confirm('Hapus data check in ini?');"
                                        title="Delete record"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="package-empty">Belum ada data check in aktif untuk
                                    ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


        </div>
    </section>

    <datalist id="packageCodeOptions">
        @foreach ($packages as $package)
            <option value="{{ $package['kode'] }}" data-name="{{ $package['nama'] }}"
                data-nominal="{{ $package['nominal'] }}" data-expired="{{ $package['expired'] }}">
                {{ $package['kode'] }} - {{ $package['nama'] }}</option>
        @endforeach
    </datalist>

    <datalist id="roomCodeOptions">
        @foreach ($rooms as $room)
            <option value="{{ $room['kode'] }}" data-kelas="{{ $room['kelas'] }}"
                data-status="{{ $room['status'] }}" data-status2="{{ $room['status2'] }}"
                data-available="{{ $room['available'] ? '1' : '0' }}">
                {{ $room['kode'] }} - {{ $room['kelas'] }}
            </option>
        @endforeach
    </datalist>
    </div>

    <template id="additionalRoomRowTemplate">
        <tr class="additional-room-row" data-room-row>
            <td>
                <input type="hidden" name="DetailKeyList[]" class="detail-key-input" value="">
                <select name="RoomCodeList[]" class="form-control package-select room-code-input">
                    <option value="">Select room</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room['kode'] }}">{{ $room['kode'] }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control package-input room-guest-display-input"
                    placeholder="Guest name">
            </td>
            <td>
                <input type="number" class="form-control package-input text-right room-pax-display-input"
                    min="1" max="20" value="1">
            </td>
            <td>
                <select name="PackageCodeList[]" class="form-control package-select package-code-input">
                    <option value="">Select package</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package['kode'] }}">{{ $package['kode'] }} - {{ $package['nama'] }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control package-input text-right nominal-display-input room-muted-display"
                    value="" readonly>
                <input type="hidden" name="NominalList[]" class="nominal-input" value="">
                <input type="hidden" name="BreakfastList[]" class="breakfast-input" value="0">
            </td>
            <td class="text-center"><button type="button" class="room-action-btn room-row-remove"
                    title="Remove room"><i class="fa-solid fa-trash"></i></button></td>
        </tr>
    </template>

    <script>
        function normalizeNumber(value) {
            return (value || '').toString().replace(/[^\d]/g, '');
        }

        function formatRibuan(value) {
            const normalized = normalizeNumber(value);
            return normalized ? normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        }

        function formatDisplayDate(value) {
            if (!value) {
                return '';
            }
            const normalized = value.toString().trim().replace(/\//g, '-');
            const parts = normalized.split('-');
            if (parts.length === 3 && parts[0].length === 4) {
                return [parts[2], parts[1], parts[0]].join('-');
            }
            if (parts.length === 3 && parts[2].length === 4) {
                return [parts[0].padStart(2, '0'), parts[1].padStart(2, '0'), parts[2]].join('-');
            }
            return value;
        }

        function normalizeDisplayDate(value) {
            const normalized = (value || '').toString().trim().replace(/\//g, '-');
            if (!normalized) {
                return '';
            }
            const parts = normalized.split('-');
            if (parts.length !== 3) {
                return '';
            }
            const day = parts[0].padStart(2, '0');
            const month = parts[1].padStart(2, '0');
            const year = parts[2];
            if (year.length !== 4) {
                return '';
            }
            const iso = year + '-' + month + '-' + day;
            const testDate = new Date(iso + 'T00:00:00');
            if (Number.isNaN(testDate.getTime())) {
                return '';
            }
            return testDate.getFullYear().toString() === year && (testDate.getMonth() + 1).toString().padStart(2, '0') ===
                month && testDate.getDate().toString().padStart(2, '0') === day ? iso : '';
        }

        function showCrudAlert(message) {
            if (typeof window.showCrudNotice === 'function') {
                window.showCrudNotice(message, 'Check In');
                return;
            }
            window.alert(message);
        }

        function findOptionByValue(listId, value) {
            const normalized = (value || '').toString().trim().toUpperCase();
            return Array.from(document.querySelectorAll('#' + listId + ' option')).find(option => option.value.trim()
                .toUpperCase() === normalized) || null;
        }

        const form = document.getElementById('checkinForm');
        const generatedRegNoField = document.getElementById('GeneratedRegNo');
        const currentDetailKeyField = document.getElementById('CurrentDetailKey');
        const primaryDetailKeyField = document.getElementById('PrimaryDetailKey');
        const saveButton = document.getElementById('saveButton');
        const newEntryButton = document.getElementById('newEntryButton');
        const focusSearchButton = document.getElementById('focusSearchButton');
        const addRoomRowButton = document.getElementById('addRoomRowButton');
        const additionalRoomBody = document.getElementById('additionalRoomBody');
        const additionalRoomRowTemplate = document.getElementById('additionalRoomRowTemplate');
        const searchKeyword = document.getElementById('searchKeyword');
        const roomCodeField = document.getElementById('RoomCode');
        const packageCodeField = document.getElementById('PackageCode');
        const nominalField = document.getElementById('NominalDisplay');
        const nominalVisibleField = document.getElementById('NominalDisplayVisible');
        const breakfastField = document.getElementById('Breakfast');
        const guestNameField = document.getElementById('GuestName');
        const numberOfPersonField = document.getElementById('NumberOfPerson');
        const reservationNumberField = document.getElementById('ReservationNumber');
        const primaryRoomGuestName = document.getElementById('PrimaryRoomGuestName');
        const primaryRoomPaxDisplay = document.getElementById('PrimaryRoomPaxDisplay');
        const clearPrimaryRoomButton = document.getElementById('clearPrimaryRoomButton');
        const roomSummaryCount = document.getElementById('RoomSummaryCount');
        const roomSummaryPax = document.getElementById('RoomSummaryPax');
        const roomHelper = document.getElementById('roomHelper');
        const packageHelper = document.getElementById('packageHelper');
        const defaultRegNo = @json(old('GeneratedRegNo', $nextRegNo));
        const defaultTypeOfCheckIn = @json($defaultTypeOfCheckIn);
        const defaultIdType = @json($defaultIdType);
        const defaultCheckIn = @json($checkInIso);
        const defaultCheckOut = @json($checkOutIso);
        const defaultCheckInTime = @json(old('CheckInTime', now()->format('H:i:s')));
        const oldAdditionalRoomRows = @json($oldAdditionalRoomRows);
        const initialHasOld = @json((bool) (old('GuestName') || old('RoomCodeList.0')));
        const vbFields = {};
        Array.from(document.querySelectorAll('[data-vb]')).forEach(function(field) {
            const vbName = field.dataset.vb;
            if (!vbName) {
                return;
            }

            vbFields[vbName] = field;
            if (typeof window[vbName] === 'undefined') {
                window[vbName] = field;
            }
        });
        window.vbFields = vbFields;
        const xRegNo = vbFields.xRegNo || generatedRegNoField;
        const xTipe = vbFields.xTipe || document.getElementById('TypeOfCheckIn');
        const xTglIn = vbFields.xTglIn || document.getElementById('CheckInDateDisplay');
        const xJamIn = vbFields.xJamIn || document.getElementById('CheckInTime');
        const xResNo = vbFields.xResNo || reservationNumberField;
        const xTglKeluar = vbFields.xTglKeluar || document.getElementById('EstimationOutDisplay');
        const xGuest = vbFields.xGuest || guestNameField;
        const xGuest2 = vbFields.xGuest2 || document.getElementById('GuestName2');
        const xPlaceBirth = vbFields.xPlaceBirth || document.getElementById('PlaceOfBirth');
        const xTglLahir = vbFields.xTglLahir || document.getElementById('BirthDateDisplay');
        const xTypeId = vbFields.xTypeId || document.getElementById('TypeOfId');
        const xKTP = vbFields.xKTP || document.getElementById('IdNumber');
        const xExpired = vbFields.xExpired || document.getElementById('ExpiredDateDisplay');
        const xPhone = vbFields.xPhone || document.getElementById('Phone');
        const xEmail = vbFields.xEmail || document.getElementById('Email');
        const xAlamat = vbFields.xAlamat || document.getElementById('Address');
        const xKelurahan = vbFields.xKelurahan || document.getElementById('Kelurahan');
        const xKecamatan = vbFields.xKecamatan || document.getElementById('Kecamatan');
        const xKota = vbFields.xKota || document.getElementById('KabCity');
        const xPropinsi = vbFields.xPropinsi || document.getElementById('ProvinceCountry');
        const xKodeNegara = vbFields.xKodeNegara || document.getElementById('Nationality');
        const xKode = vbFields.xKode || roomCodeField;
        const xPackage = vbFields.xPackage || packageCodeField;
        const xNominal = vbFields.xNominal || nominalVisibleField;
        const xBF = vbFields.xBF || breakfastField;
        const xPosisi = vbFields.xPosisi || document.getElementById('GroupPosition');
        const xAgama = vbFields.xAgama || document.getElementById('Religion');
        const xPerson = vbFields.xPerson || numberOfPersonField;
        const xUsaha = vbFields.xUsaha || document.getElementById('Company');
        const xSegment = vbFields.xSegment || document.getElementById('Segment');
        const xRemark = vbFields.xRemark || document.getElementById('Remarks');
        const xPayment = vbFields.xPayment || document.getElementById('PaymentMethod');
        const xCreditCard = vbFields.xCreditCard || document.getElementById('CreditCardNumber');
        const xPeriksa = vbFields.xPeriksa || document.getElementById('CheckDeposit');
        const xMember = vbFields.xMember || document.getElementById('Member');
        const xSales = vbFields.xSales || document.getElementById('Sales');

        function normalizeTimeDisplay(value) {
            const raw = (value || '').toString().trim();
            if (!raw) {
                return '';
            }

            const directMatch = raw.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
            if (directMatch) {
                const hours = parseInt(directMatch[1], 10);
                const minutes = parseInt(directMatch[2], 10);
                const seconds = parseInt(directMatch[3] || '0', 10);

                if (hours <= 23 && minutes <= 59 && seconds <= 59) {
                    return [hours, minutes, seconds].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            const digits = raw.replace(/[^\d]/g, '');
            if (digits.length === 6) {
                const hours = parseInt(digits.slice(0, 2), 10);
                const minutes = parseInt(digits.slice(2, 4), 10);
                const seconds = parseInt(digits.slice(4, 6), 10);

                if (hours <= 23 && minutes <= 59 && seconds <= 59) {
                    return [hours, minutes, seconds].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            if (digits.length === 4) {
                const hours = parseInt(digits.slice(0, 2), 10);
                const minutes = parseInt(digits.slice(2, 4), 10);

                if (hours <= 23 && minutes <= 59) {
                    return [hours, minutes, '00'].map(part => String(part).padStart(2, '0')).join(':');
                }
            }

            return raw;
        }

        function ensureSelectValueExists(field, label) {
            if (!field) {
                return true;
            }

            const currentValue = (field.value || '').toString().trim().toUpperCase();
            const hasOption = Array.from(field.options || []).some(function(option) {
                return (option.value || '').toString().trim().toUpperCase() === currentValue;
            });

            if (!hasOption) {
                showCrudAlert(label + ' must be selected from the available list.');
                field.focus();
                return false;
            }

            return true;
        }

        function updatePrimaryGuestDisplay() {
            primaryRoomGuestName.textContent = (xGuest?.value || '').trim() || '-';
        }

        function applyReservationNumberSelection() {
            if (!xResNo) {
                return;
            }

            const option = xResNo.options[xResNo.selectedIndex];
            if (!option || !option.value) {
                return;
            }

            if (xAlamat) {
                xAlamat.value = option.dataset.address || '';
            }

            if (xKodeNegara) {
                xKodeNegara.value = option.dataset.nationality || 'INA';
            }

            if (xPhone) {
                xPhone.value = option.dataset.phone || '';
            }

            if (xRemark) {
                xRemark.value = option.dataset.remarks || '';
            }
        }

        function updateRoomSummary() {
            const primaryRooms = roomCodeField.value.trim() ? 1 : 0;
            const extraRows = Array.from(additionalRoomBody.querySelectorAll('[data-room-row]'));
            const extraRooms = extraRows.filter(row => {
                const input = row.querySelector('.room-code-input');
                return input && input.value.trim() !== '';
            }).length;
            const primaryPax = parseInt(primaryRoomPaxDisplay.value || '0', 10) || 0;
            const extraPax = extraRows.reduce((sum, row) => {
                const input = row.querySelector('.room-pax-display-input');
                return sum + (parseInt(input?.value || '0', 10) || 0);
            }, 0);

            roomSummaryCount.textContent = String(primaryRooms + extraRooms);
            roomSummaryPax.textContent = String(primaryPax + extraPax);
        }

        function bindDateGroup(group) {
            const hidden = group.querySelector('input[type="hidden"]');
            const display = group.querySelector('input[type="text"]');
            const native = group.querySelector('[data-date-native]');
            const button = group.querySelector('[data-date-button]');
            if (!hidden || !display || !native) {
                return;
            }
            display.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    hidden.value = '';
                    native.value = '';
                    return;
                }
                const iso = normalizeDisplayDate(this.value);
                if (!iso) {
                    showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.');
                    this.focus();
                    return;
                }
                hidden.value = iso;
                native.value = iso;
                this.value = formatDisplayDate(iso);
            });
            native.addEventListener('change', function() {
                hidden.value = this.value || '';
                display.value = formatDisplayDate(this.value);
            });
            if (button) {
                button.addEventListener('click', function() {
                    if (typeof native.showPicker === 'function') {
                        native.showPicker();
                    } else {
                        native.focus();
                        native.click();
                    }
                });
            }
            display.value = formatDisplayDate(hidden.value);
            native.value = hidden.value;
        }

        function updateRoomHelper() {
            const option = findOptionByValue('roomCodeOptions', roomCodeField.value);
            if (!option) {
                roomHelper.querySelector('strong').textContent = 'Select an available room';
                updateRoomSummary();
                return;
            }
            const kelas = option.dataset.kelas || '-';
            const status = option.dataset.status || '-';
            const available = option.dataset.available === '1';
            roomHelper.querySelector('strong').textContent = roomCodeField.value.toUpperCase() + ' | ' + kelas + ' | ' +
                status + (available ? '' : ' | tidak tersedia');
            updateRoomSummary();
        }

        function updatePackageHelper() {
            const option = findOptionByValue('packageCodeOptions', packageCodeField.value);
            if (!option) {
                packageHelper.querySelector('strong').textContent = 'Nominal will be filled automatically when a package is found';
                nominalField.value = '';
                nominalVisibleField.value = '';
                return;
            }
            const nominal = Number(option.dataset.nominal || 0);
            const expired = option.dataset.expired || '-';
            nominalField.value = normalizeNumber(String(nominal));
            nominalVisibleField.value = formatRibuan(String(nominal));
            packageHelper.querySelector('strong').textContent = (option.dataset.name || 'Package') + ' | Exp ' + expired +
                ' | Rp ' + formatRibuan(String(nominal));
        }

        function renumberAdditionalRows() {
            updateRoomSummary();
        }

        function bindAdditionalRow(row) {
            const roomInput = row.querySelector('.room-code-input');
            const packageInput = row.querySelector('.package-code-input');
            const nominalInput = row.querySelector('.nominal-input');
            const nominalDisplayInput = row.querySelector('.nominal-display-input');
            const breakfastInput = row.querySelector('.breakfast-input');
            const paxInput = row.querySelector('.room-pax-display-input');
            const removeButton = row.querySelector('.room-row-remove');
            if (packageInput) {
                packageInput.addEventListener('change', function() {
                    const option = findOptionByValue('packageCodeOptions', this.value);
                    nominalInput.value = option ? normalizeNumber(String(option.dataset.nominal || '0')) : '';
                    nominalDisplayInput.value = option ? formatRibuan(String(option.dataset.nominal || '0')) : '';
                });
            }
            if (roomInput) {
                roomInput.addEventListener('change', function() {
                    updateRoomSummary();
                });
            }
            if (paxInput) {
                paxInput.addEventListener('input', function() {
                    this.value = this.value ? String(Math.max(parseInt(this.value, 10) || 1, 1)) : '1';
                    updateRoomSummary();
                });
            }
            if (breakfastInput) {
                breakfastInput.value = breakfastInput.value || '0';
            }
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    row.remove();
                    renumberAdditionalRows();
                });
            }
        }

        function addAdditionalRoomRow(detail = {}) {
            const fragment = additionalRoomRowTemplate.content.cloneNode(true);
            const row = fragment.querySelector('[data-room-row]');
            row.querySelector('.detail-key-input').value = detail.detailKey || '';
            row.querySelector('.room-code-input').value = detail.roomCode || '';
            row.querySelector('.room-guest-display-input').value = detail.guestName || '';
            row.querySelector('.room-pax-display-input').value = detail.pax || detail.breakfast || 1;
            row.querySelector('.package-code-input').value = detail.packageCode || '';
            row.querySelector('.nominal-input').value = detail.nominal ? normalizeNumber(String(detail.nominal)) : '';
            row.querySelector('.nominal-display-input').value = detail.nominal ? formatRibuan(String(detail.nominal)) : '';
            row.querySelector('.breakfast-input').value = detail.breakfast ?? 0;
            bindAdditionalRow(row);
            additionalRoomBody.appendChild(fragment);
            renumberAdditionalRows();
        }

        function resetAdditionalRoomRows() {
            additionalRoomBody.innerHTML = '';
        }

        function setFormModeCreate(regNo = defaultRegNo) {
            form.action = '/checkin';
            currentDetailKeyField.value = '';
            primaryDetailKeyField.value = '';
            xRegNo.value = regNo || defaultRegNo;
            saveButton.textContent = 'Save Check In';
            Array.from(document.querySelectorAll('.checkin-record-row')).forEach(row => row.classList.remove('is-active'));
        }

        function applyRecord(record) {
            setFormModeCreate(record.RegNo || defaultRegNo);
            currentDetailKeyField.value = record.DetailKey || '';
            primaryDetailKeyField.value = record.DetailKey || '';
            form.action = '/checkin/' + encodeURIComponent(record.DetailKey) + '/update';
            saveButton.textContent = 'Update Check In';
            const mappings = ['ReservationNumber', 'GuestName', 'GuestName2', 'Address', 'Kelurahan', 'Kecamatan',
                'KabCity', 'ProvinceCountry', 'TypeOfId', 'IdNumber', 'GroupPosition', 'TypeOfCheckIn', 'PlaceOfBirth',
                'Religion', 'Nationality', 'NumberOfPerson', 'PaymentMethod', 'Company', 'CreditCardNumber', 'Segment',
                'Phone', 'Email', 'Remarks', 'Member', 'Sales'
            ];
            mappings.forEach(function(id) {
                const field = document.getElementById(id);
                if (field) {
                    field.value = record[id] ?? '';
                }
            });
            xPeriksa.checked = String(record.CheckDeposit || '0') === '1';
            document.getElementById('CheckInDate').value = record.CheckInDate || '';
            xTglIn.value = formatDisplayDate(record.CheckInDate || '');
            xTglIn.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.CheckInDate || '';
            document.getElementById('BirthDate').value = record.BirthDate || '';
            xTglLahir.value = formatDisplayDate(record.BirthDate || '');
            xTglLahir.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.BirthDate || '';
            document.getElementById('ExpiredDate').value = record.ExpiredDate || '';
            xExpired.value = formatDisplayDate(record.ExpiredDate || '');
            xExpired.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.ExpiredDate || '';
            document.getElementById('EstimationOut').value = record.EstimationOut || '';
            xTglKeluar.value = formatDisplayDate(record.EstimationOut || '');
            xTglKeluar.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = record.EstimationOut || '';
            xJamIn.value = normalizeTimeDisplay(record.CheckInTime || '');
            xKode.value = record.RoomCode || '';
            xPackage.value = record.PackageCode || '';
            nominalField.value = normalizeNumber(String(record.Nominal || ''));
            xNominal.value = record.Nominal ? formatRibuan(String(record.Nominal)) : '';
            xBF.value = record.Breakfast || 0;
            primaryRoomPaxDisplay.value = record.NumberOfPerson || record.Breakfast || 1;
            updatePrimaryGuestDisplay();
            resetAdditionalRoomRows();
            updateRoomHelper();
            updatePackageHelper();
        }

        function resetForm() {
            const preservedRegNo = (xRegNo.value || defaultRegNo).trim().toUpperCase();
            form.reset();
            setFormModeCreate(preservedRegNo);
            resetAdditionalRoomRows();
            document.getElementById('CheckInDate').value = defaultCheckIn;
            xTglIn.value = formatDisplayDate(defaultCheckIn);
            xTglIn.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = defaultCheckIn;
            document.getElementById('EstimationOut').value = defaultCheckOut;
            xTglKeluar.value = formatDisplayDate(defaultCheckOut);
            xTglKeluar.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = defaultCheckOut;
            document.getElementById('BirthDate').value = '';
            xTglLahir.value = '';
            xTglLahir.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = '';
            document.getElementById('ExpiredDate').value = '';
            xExpired.value = '';
            xExpired.closest('[data-date-field]').querySelector('[data-date-native]')
                .value = '';
            roomHelper.querySelector('strong').textContent = 'Select an available room';
            packageHelper.querySelector('strong').textContent = 'Nominal will be filled automatically when a package is found';
            nominalField.value = '';
            xNominal.value = '';
            xPackage.value = '';
            xKode.value = '';
            xBF.value = '2';
            xJamIn.value = defaultCheckInTime;
            xTipe.value = defaultTypeOfCheckIn;
            xTypeId.value = defaultIdType;
            xPayment.value = 'OTA';
            xSegment.value = 'TRAVEL';
            xKodeNegara.value = 'INA';
            xPerson.value = '2';
            primaryRoomPaxDisplay.value = '2';
            updatePrimaryGuestDisplay();
            updateRoomSummary();
            xGuest.focus();
        }

        Array.from(document.querySelectorAll('[data-date-field]')).forEach(bindDateGroup);
        xGuest.addEventListener('input', updatePrimaryGuestDisplay);
        if (xResNo) {
            xResNo.addEventListener('change', applyReservationNumberSelection);
            xResNo.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyReservationNumberSelection();
                }
            });
        }
        xJamIn.addEventListener('blur', function() {
            this.value = normalizeTimeDisplay(this.value);
        });
        xJamIn.value = normalizeTimeDisplay(xJamIn.value);
        if (xTypeId) {
            xTypeId.value = xTypeId.value || defaultIdType;
        }
        numberOfPersonField.addEventListener('input', function() {
            primaryRoomPaxDisplay.value = this.value || '1';
            updateRoomSummary();
        });
        primaryRoomPaxDisplay.addEventListener('input', function() {
            this.value = this.value ? String(Math.max(parseInt(this.value, 10) || 1, 1)) : '1';
            numberOfPersonField.value = this.value;
            updateRoomSummary();
        });
        roomCodeField.addEventListener('change', function() {
            updateRoomHelper();
        });
        packageCodeField.addEventListener('change', updatePackageHelper);
        addRoomRowButton.addEventListener('click', function() {
            addAdditionalRoomRow();
        });
        clearPrimaryRoomButton.addEventListener('click', function() {
            primaryDetailKeyField.value = '';
            roomCodeField.value = '';
            packageCodeField.value = '';
            nominalField.value = '';
            nominalVisibleField.value = '';
            breakfastField.value = '0';
            updateRoomHelper();
            updatePackageHelper();
        });
        newEntryButton.addEventListener('click', resetForm);
        focusSearchButton.addEventListener('click', function() {
            document.getElementById('checkinDirectoryShell').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            setTimeout(() => searchKeyword.focus(), 250);
        });
        form.addEventListener('submit', function(event) {
            generatedRegNoField.value = generatedRegNoField.value.trim().toUpperCase();
            const dateDisplays = ['CheckInDateDisplay', 'BirthDateDisplay', 'ExpiredDateDisplay',
                'EstimationOutDisplay'
            ];
            for (const id of dateDisplays) {
                const display = document.getElementById(id);
                if (!display) {
                    continue;
                }
                const group = display.closest('[data-date-field]');
                const hidden = group.querySelector('input[type="hidden"]');
                if (display.value.trim() === '') {
                    hidden.value = '';
                    continue;
                }
                const iso = normalizeDisplayDate(display.value);
                if (!iso) {
                    event.preventDefault();
                    showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.');
                    display.focus();
                    return;
                }
                hidden.value = iso;
                group.querySelector('[data-date-native]').value = iso;
                display.value = formatDisplayDate(iso);
            }
            if (document.getElementById('EstimationOut').value < document.getElementById('CheckInDate').value) {
                event.preventDefault();
                showCrudAlert('Estimation Out tidak boleh lebih kecil dari Check In.');
                document.getElementById('EstimationOutDisplay').focus();
                return;
            }
            if (!ensureSelectValueExists(xTypeId, 'Type of ID')) {
                event.preventDefault();
                return;
            }
            roomCodeField.value = roomCodeField.value.trim().toUpperCase();
            packageCodeField.value = packageCodeField.value.trim().toUpperCase();
            nominalField.value = normalizeNumber(nominalField.value);
            Array.from(additionalRoomBody.querySelectorAll('.room-code-input')).forEach(input => input.value = input
                .value.trim().toUpperCase());
            Array.from(additionalRoomBody.querySelectorAll('.package-code-input')).forEach(input => input.value =
                input.value.trim().toUpperCase());
            Array.from(additionalRoomBody.querySelectorAll('.nominal-input')).forEach(input => input.value =
                normalizeNumber(input.value));
            numberOfPersonField.value = primaryRoomPaxDisplay.value || numberOfPersonField.value || '1';
        });
        Array.from(document.querySelectorAll('.checkin-record-row')).forEach(function(row) {
            row.addEventListener('click', function(event) {
                if (event.target.closest('.checkin-delete-link')) {
                    return;
                }
                Array.from(document.querySelectorAll('.checkin-record-row')).forEach(item => item.classList
                    .remove('is-active'));
                row.classList.add('is-active');
                try {
                    applyRecord(JSON.parse(row.dataset.record));
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } catch (error) {
                    showCrudAlert('Data baris tidak bisa dimuat ke form.');
                }
            });
        });
        form.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter' || event.target.tagName === 'TEXTAREA') {
                return;
            }
            const fields = Array.from(form.querySelectorAll(
                'input[data-flow], select[data-flow], textarea[data-flow]')).filter(field => !field.disabled &&
                field.offsetParent !== null);
            const index = fields.indexOf(event.target);
            if (index >= 0 && index < fields.length - 1) {
                event.preventDefault();
                fields[index + 1].focus();
                fields[index + 1].select?.();
            }
        });
        if (oldAdditionalRoomRows.length) {
            oldAdditionalRoomRows.forEach(detail => addAdditionalRoomRow(detail));
        }
        updateRoomHelper();
        updatePackageHelper();
        updatePrimaryGuestDisplay();
        primaryRoomPaxDisplay.value = numberOfPersonField.value || primaryRoomPaxDisplay.value || '2';
        updateRoomSummary();
        if (initialHasOld) {
            document.getElementById('GuestName').focus();
        } else {
            resetForm();
        }
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.transition = 'opacity .3s ease, transform .3s ease';
                successAlert.style.opacity = '0';
                successAlert.style.transform = 'translateY(-8px)';
                setTimeout(() => successAlert.remove(), 300);
            }, 3000);
        }
    </script>

@endsection
