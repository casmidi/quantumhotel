@extends('layouts.app')

@section('title', '')

@section('content')

@php
    $todayIso = now()->format('Y-m-d');
    $tomorrowIso = now()->addDay()->format('Y-m-d');
    $checkInIso = old('CheckInDate', $todayIso);
    $checkOutIso = old('EstimationOut', $tomorrowIso);
    $birthIso = old('BirthDate', '');
    $expiredIso = old('ExpiredDate', '');
@endphp

@include('partials.crud-package-theme')

<style>
.checkin-page {
    padding: 0 0 2rem;
    color: #10233b;
}

.checkin-shell + .checkin-shell {
    margin-top: 1.5rem;
}

.checkin-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.checkin-badge-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.checkin-regno {
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.85rem 1rem;
    border-radius: 20px;
    border: 1px solid rgba(199, 165, 106, 0.28);
    background: linear-gradient(180deg, rgba(233, 213, 162, 0.18), rgba(255, 255, 255, 0.92));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.checkin-regno small,
.checkin-room-note small,
.checkin-package-note small {
    display: block;
    margin-bottom: 0.18rem;
    font-size: 0.68rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #8f6a2d;
}

.checkin-regno strong,
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
    padding: 0.78rem 0.95rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(199, 165, 106, 0.2);
}

.checkin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.05rem 1.2rem;
}

.checkin-column {
    display: grid;
    gap: 0.68rem;
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
    height: calc(2.45rem + 2px);
    font-size: 0.96rem;
    font-weight: 700;
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

.checkin-actions-side .package-btn-secondary[disabled] {
    opacity: 0.52;
    cursor: not-allowed;
}

.checkin-directory-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
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
    background: rgba(199, 165, 106, 0.1) !important;
    box-shadow: inset 4px 0 0 #b38a51;
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
    .checkin-form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .checkin-row {
        grid-template-columns: 1fr;
        gap: 0.45rem;
    }

    .checkin-row-note,
    .checkin-row-note.is-empty {
        min-width: 0;
    }

    .checkin-actions {
        align-items: flex-start;
    }
}
</style>

<div class="container-fluid checkin-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="alert package-error mb-4">{{ $errors->first() }}</div>
    @endif

    <section class="package-shell checkin-shell">
        <div class="package-shell-header">
            <div class="package-shell-heading-block">
                <h1 class="package-shell-title">Check In</h1>
                <p class="package-shell-subtitle">Input tamu aktif dengan format padat, cepat, dan tetap satu bahasa visual dengan Package Transaction.</p>
            </div>
        </div>
        <div class="package-shell-body">
            <form method="POST" action="/checkin" id="checkinForm" autocomplete="off">
                @csrf
                <input type="hidden" name="GeneratedRegNo" id="GeneratedRegNo" value="{{ old('GeneratedRegNo', $nextRegNo) }}">
                <input type="hidden" id="CurrentRegNo" value="">

                <div class="checkin-toolbar">
                    <div class="checkin-badge-row">
                        <div class="checkin-regno">
                            <div>
                                <small>Reg. Number</small>
                                <strong id="DisplayRegNo">{{ old('GeneratedRegNo', $nextRegNo) }}</strong>
                            </div>
                        </div>
                        <div class="checkin-room-note" id="roomHelper">
                            <small>Room Status</small>
                            <strong>Pilih room yang masih tersedia</strong>
                        </div>
                        <div class="checkin-package-note" id="packageHelper">
                            <small>Package Note</small>
                            <strong>Nominal akan terisi otomatis bila package ditemukan</strong>
                        </div>
                    </div>
                </div>

                <div class="checkin-form-grid">
                    <div class="checkin-column">
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="CheckInDateDisplay">Check In</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="CheckInDate" id="CheckInDate" value="{{ $checkInIso }}">
                                <input type="text" id="CheckInDateDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse($checkInIso)->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" data-flow required>
                                <button type="button" class="package-date-picker" data-date-button aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ $checkInIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="CheckInTime">Time</label>
                            <input type="time" name="CheckInTime" id="CheckInTime" class="form-control package-input" value="{{ old('CheckInTime', now()->format('H:i')) }}" data-flow required>
                            <span class="checkin-row-note">24 jam</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="ReservationNumber">Res. Number</label>
                            <input type="text" name="ReservationNumber" id="ReservationNumber" class="form-control package-input" value="{{ old('ReservationNumber') }}" data-flow>
                            <span class="checkin-row-note">Opsional</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="GuestName">Guest Name I</label>
                            <input type="text" name="GuestName" id="GuestName" class="form-control package-input" value="{{ old('GuestName') }}" data-flow required>
                            <span class="checkin-row-note">Utama</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="GuestName2">Name II</label>
                            <input type="text" name="GuestName2" id="GuestName2" class="form-control package-input" value="{{ old('GuestName2') }}" data-flow>
                            <span class="checkin-row-note">Tambahan</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Address">Address</label>
                            <input type="text" name="Address" id="Address" class="form-control package-input" value="{{ old('Address') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Kelurahan">Kel</label>
                            <input type="text" name="Kelurahan" id="Kelurahan" class="form-control package-input" value="{{ old('Kelurahan') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Kecamatan">Kec</label>
                            <input type="text" name="Kecamatan" id="Kecamatan" class="form-control package-input" value="{{ old('Kecamatan') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="KabCity">Kab/City</label>
                            <input type="text" name="KabCity" id="KabCity" class="form-control package-input" value="{{ old('KabCity') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="ProvinceCountry">Province / Country</label>
                            <input type="text" name="ProvinceCountry" id="ProvinceCountry" class="form-control package-input" value="{{ old('ProvinceCountry') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="TypeOfId">Type of ID</label>
                            <select name="TypeOfId" id="TypeOfId" class="form-control package-select" data-flow>
                                <option value="">Select</option>
                                @foreach($idTypeOptions as $option)
                                    <option value="{{ $option }}" {{ old('TypeOfId') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note">Identitas</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="IdNumber">ID Number</label>
                            <input type="text" name="IdNumber" id="IdNumber" class="form-control package-input" value="{{ old('IdNumber') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="ExpiredDateDisplay">Expired Date</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="ExpiredDate" id="ExpiredDate" value="{{ $expiredIso }}">
                                <input type="text" id="ExpiredDateDisplay" class="form-control package-input" value="{{ $expiredIso ? \Carbon\Carbon::parse($expiredIso)->format('d-m-Y') : '' }}" placeholder="dd-MM-yyyy" inputmode="numeric" data-flow>
                                <button type="button" class="package-date-picker" data-date-button aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ $expiredIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                            <span class="checkin-row-note">ID</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="GroupPosition">Group Position</label>
                            <input type="text" name="GroupPosition" id="GroupPosition" class="form-control package-input" value="{{ old('GroupPosition') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="PackageCode">Package Code</label>
                            <input type="text" name="PackageCode" id="PackageCode" class="form-control package-input" value="{{ old('PackageCode') }}" list="packageCodeOptions" data-flow>
                            <span class="checkin-row-note">Lookup</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="NominalDisplay">Nominal Rp</label>
                            <input type="text" id="NominalDisplay" name="Nominal" class="form-control package-input text-right" value="{{ old('Nominal') ? number_format((float) preg_replace('/[^\d]/', '', (string) old('Nominal')), 0, ',', '.') : '' }}" inputmode="numeric" data-flow>
                            <span class="checkin-row-note">Auto</span>
                        </div>
                    </div>

                    <div class="checkin-column">
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="TypeOfCheckIn">Type of Check In</label>
                            <select name="TypeOfCheckIn" id="TypeOfCheckIn" class="form-control package-select" data-flow required>
                                @foreach($typeOptions as $option)
                                    <option value="{{ $option }}" {{ old('TypeOfCheckIn', 'GROUP RESERVATION') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note">Mode</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="PlaceOfBirth">Place of Birth</label>
                            <input type="text" name="PlaceOfBirth" id="PlaceOfBirth" class="form-control package-input" value="{{ old('PlaceOfBirth') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="BirthDateDisplay">Date of Birth</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="BirthDate" id="BirthDate" value="{{ $birthIso }}">
                                <input type="text" id="BirthDateDisplay" class="form-control package-input" value="{{ $birthIso ? \Carbon\Carbon::parse($birthIso)->format('d-m-Y') : '' }}" placeholder="dd-MM-yyyy" inputmode="numeric" data-flow>
                                <button type="button" class="package-date-picker" data-date-button aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ $birthIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Religion">Religion</label>
                            <select name="Religion" id="Religion" class="form-control package-select" data-flow>
                                <option value="">Select</option>
                                @foreach($religionOptions as $option)
                                    <option value="{{ $option }}" {{ old('Religion') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Nationality">Nationality</label>
                            <select name="Nationality" id="Nationality" class="form-control package-select" data-flow>
                                @foreach($nationalityOptions as $option)
                                    <option value="{{ $option }}" {{ old('Nationality', 'INA') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="NumberOfPerson">Number Of Person</label>
                            <input type="number" name="NumberOfPerson" id="NumberOfPerson" class="form-control package-input text-right" value="{{ old('NumberOfPerson', 2) }}" min="1" max="20" data-flow required>
                            <span class="checkin-row-note">Guest</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="EstimationOutDisplay">Estimation Out</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="EstimationOut" id="EstimationOut" value="{{ $checkOutIso }}">
                                <input type="text" id="EstimationOutDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse($checkOutIso)->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" data-flow required>
                                <button type="button" class="package-date-picker" data-date-button aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ $checkOutIso }}" tabindex="-1" aria-hidden="true">
                            </div>
                            <span class="checkin-row-note">Out</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="PaymentMethod">Payment Method</label>
                            <select name="PaymentMethod" id="PaymentMethod" class="form-control package-select" data-flow required>
                                @foreach($paymentOptions as $option)
                                    <option value="{{ $option }}" {{ old('PaymentMethod', 'OTA') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note">Pay</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Company">Company</label>
                            <input type="text" name="Company" id="Company" class="form-control package-input" value="{{ old('Company') }}" data-flow>
                            <span class="checkin-row-note">Opsional</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="CreditCardNumber">Credit Card #</label>
                            <input type="text" name="CreditCardNumber" id="CreditCardNumber" class="form-control package-input" value="{{ old('CreditCardNumber') }}" data-flow>
                            <label class="checkin-row-note is-stack"><input type="checkbox" name="CheckDeposit" id="CheckDeposit" value="1" {{ old('CheckDeposit') ? 'checked' : '' }}> Check Deposit</label>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Segment">Segment</label>
                            <select name="Segment" id="Segment" class="form-control package-select" data-flow>
                                <option value="">Select</option>
                                @foreach($segmentOptions as $option)
                                    <option value="{{ $option }}" {{ old('Segment', 'TRAVEL') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="checkin-row-note">Sales</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Phone">Phone</label>
                            <input type="text" name="Phone" id="Phone" class="form-control package-input" value="{{ old('Phone') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Email">Email</label>
                            <input type="text" name="Email" id="Email" class="form-control package-input" value="{{ old('Email') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Breakfast">Breakfast</label>
                            <input type="number" name="Breakfast" id="Breakfast" class="form-control package-input text-right" value="{{ old('Breakfast', 2) }}" min="0" max="20" data-flow>
                            <span class="checkin-row-note">Pax</span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Remarks">Remarks</label>
                            <input type="text" name="Remarks" id="Remarks" class="form-control package-input" value="{{ old('Remarks') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Member">Member 1</label>
                            <input type="text" name="Member" id="Member" class="form-control package-input" value="{{ old('Member') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="Sales">Sales</label>
                            <input type="text" name="Sales" id="Sales" class="form-control package-input" value="{{ old('Sales') }}" data-flow>
                            <span class="checkin-row-note is-empty"></span>
                        </div>
                        <div class="checkin-row">
                            <label class="checkin-row-label" for="RoomCode">Room</label>
                            <input type="text" name="RoomCode" id="RoomCode" class="form-control package-input" value="{{ old('RoomCode') }}" list="roomCodeOptions" data-flow required>
                            <span class="checkin-row-note">Lookup</span>
                        </div>
                    </div>
                </div>

                <div class="checkin-actions">
                    <div class="checkin-actions-main">
                        <button type="submit" class="btn package-btn-primary" id="saveButton">Save Check In</button>
                        <button type="button" class="btn package-btn-secondary" id="newEntryButton">New Entry</button>
                        <button type="button" class="btn package-btn-secondary" id="focusSearchButton">Search Data</button>
                    </div>
                    <div class="checkin-actions-side">
                        <button type="button" class="btn package-btn-secondary" disabled>Deposit</button>
                        <button type="button" class="btn package-btn-secondary" disabled>Print</button>
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
                    Rooms Ready: <strong>{{ number_format($summary['rooms_ready'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
                    Active Packages: <strong>{{ number_format($summary['packages'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <form method="GET" action="/checkin" class="checkin-search-form" id="checkinSearchForm">
                <div class="checkin-search-group">
                    <label class="package-label" for="searchKeyword">Search Keyword</label>
                    <input type="text" name="search" id="searchKeyword" class="form-control package-input" value="{{ $search }}" placeholder="Search reg number, room, guest, or package">
                </div>
                <div class="checkin-search-actions">
                    <button type="submit" class="btn package-btn-primary"><i class="fa-solid fa-magnifying-glass mr-2"></i>Search</button>
                    <a href="/checkin" class="btn package-btn-secondary">Clear</a>
                </div>
            </form>
            <div class="package-table-wrap mt-4">
                <table class="table package-table checkin-table mb-0">
                    <thead>
                        <tr>
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
                            <tr class="checkin-record-row" data-record="{{ e($record->record_json) }}" data-regno="{{ $record->RegNo }}">
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
                                    <a href="/checkin/{{ urlencode($record->RegNo) }}/delete" class="checkin-delete-link" onclick="event.stopPropagation(); return confirm('Hapus data check in ini?');" title="Delete record"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="package-empty">Belum ada data check in aktif untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($checkins->hasPages())
                <div class="package-pagination-wrap">
                    <ul class="package-pagination">
                        @php
                            $start = max($checkins->currentPage() - 1, 1);
                            $end = min($start + 2, $checkins->lastPage());
                            $start = max($end - 2, 1);
                        @endphp
                        <li class="package-page-item {{ $checkins->onFirstPage() ? 'disabled' : '' }}"><a class="package-page-link" href="{{ $checkins->previousPageUrl() ?: '#' }}">&laquo;</a></li>
                        @for($page = $start; $page <= $end; $page++)
                            <li class="package-page-item {{ $page === $checkins->currentPage() ? 'active' : '' }}"><a class="package-page-link" href="{{ $checkins->url($page) }}">{{ $page }}</a></li>
                        @endfor
                        <li class="package-page-item {{ $checkins->hasMorePages() ? '' : 'disabled' }}"><a class="package-page-link" href="{{ $checkins->nextPageUrl() ?: '#' }}">&raquo;</a></li>
                    </ul>
                </div>
            @endif
        </div>
    </section>

    <datalist id="packageCodeOptions">
        @foreach($packages as $package)
            <option value="{{ $package['kode'] }}" data-name="{{ $package['nama'] }}" data-nominal="{{ $package['nominal'] }}" data-expired="{{ $package['expired'] }}">{{ $package['kode'] }} - {{ $package['nama'] }}</option>
        @endforeach
    </datalist>

    <datalist id="roomCodeOptions">
        @foreach($rooms as $room)
            <option value="{{ $room['kode'] }}" data-kelas="{{ $room['kelas'] }}" data-status="{{ $room['status'] }}" data-status2="{{ $room['status2'] }}" data-available="{{ $room['available'] ? '1' : '0' }}">{{ $room['kode'] }} - {{ $room['kelas'] }}</option>
        @endforeach
    </datalist>
</div>

<script>
function normalizeNumber(value){return (value||'').toString().replace(/[^\d]/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value); return normalized ? normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '';}
function formatDisplayDate(value){if(!value){return '';} const normalized=value.toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length===3 && parts[0].length===4){return [parts[2],parts[1],parts[0]].join('-');} if(parts.length===3 && parts[2].length===4){return [parts[0].padStart(2,'0'),parts[1].padStart(2,'0'),parts[2]].join('-');} return value;}
function normalizeDisplayDate(value){const normalized=(value||'').toString().trim().replace(/\//g,'-'); if(!normalized){return '';} const parts=normalized.split('-'); if(parts.length!==3){return '';} const day=parts[0].padStart(2,'0'); const month=parts[1].padStart(2,'0'); const year=parts[2]; if(year.length!==4){return '';} const iso=year+'-'+month+'-'+day; const testDate=new Date(iso+'T00:00:00'); if(Number.isNaN(testDate.getTime())){return '';} return testDate.getFullYear().toString()===year && (testDate.getMonth()+1).toString().padStart(2,'0')===month && testDate.getDate().toString().padStart(2,'0')===day ? iso : '';}
function showCrudAlert(message){if(typeof window.showCrudNotice==='function'){window.showCrudNotice(message,'Check In'); return;} window.alert(message);}
function findOptionByValue(listId, value){const normalized=(value||'').toString().trim().toUpperCase(); return Array.from(document.querySelectorAll('#'+listId+' option')).find(option => option.value.trim().toUpperCase()===normalized) || null;}

const form=document.getElementById('checkinForm');
const generatedRegNoField=document.getElementById('GeneratedRegNo');
const currentRegNoField=document.getElementById('CurrentRegNo');
const displayRegNo=document.getElementById('DisplayRegNo');
const saveButton=document.getElementById('saveButton');
const newEntryButton=document.getElementById('newEntryButton');
const focusSearchButton=document.getElementById('focusSearchButton');
const searchKeyword=document.getElementById('searchKeyword');
const roomCodeField=document.getElementById('RoomCode');
const packageCodeField=document.getElementById('PackageCode');
const nominalField=document.getElementById('NominalDisplay');
const roomHelper=document.getElementById('roomHelper');
const packageHelper=document.getElementById('packageHelper');
const defaultRegNo=@json(old('GeneratedRegNo', $nextRegNo));
const defaultCheckIn=@json($checkInIso);
const defaultCheckOut=@json($checkOutIso);

function bindDateGroup(group){const hidden=group.querySelector('input[type="hidden"]'); const display=group.querySelector('input[type="text"]'); const native=group.querySelector('[data-date-native]'); const button=group.querySelector('[data-date-button]'); if(!hidden||!display||!native){return;} display.addEventListener('blur', function(){if(!this.value.trim()){hidden.value=''; native.value=''; return;} const iso=normalizeDisplayDate(this.value); if(!iso){showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.'); this.focus(); return;} hidden.value=iso; native.value=iso; this.value=formatDisplayDate(iso);}); native.addEventListener('change', function(){hidden.value=this.value||''; display.value=formatDisplayDate(this.value);}); if(button){button.addEventListener('click', function(){if(typeof native.showPicker==='function'){native.showPicker();} else {native.focus(); native.click();}});} display.value=formatDisplayDate(hidden.value); native.value=hidden.value;}
function updateRoomHelper(){const option=findOptionByValue('roomCodeOptions', roomCodeField.value); if(!option){roomHelper.querySelector('strong').textContent='Pilih room yang masih tersedia'; return;} const kelas=option.dataset.kelas || '-'; const status=option.dataset.status || '-'; const available=option.dataset.available === '1'; roomHelper.querySelector('strong').textContent = roomCodeField.value.toUpperCase()+' • '+kelas+' • '+status+(available ? '' : ' • tidak tersedia');}
function updatePackageHelper(){const option=findOptionByValue('packageCodeOptions', packageCodeField.value); if(!option){packageHelper.querySelector('strong').textContent='Nominal akan terisi otomatis bila package ditemukan'; return;} const nominal=Number(option.dataset.nominal || 0); const expired=option.dataset.expired || '-'; if(!normalizeNumber(nominalField.value)){nominalField.value=formatRibuan(String(nominal));} packageHelper.querySelector('strong').textContent=(option.dataset.name || 'Package')+' • Exp '+expired+' • Rp '+formatRibuan(String(nominal));}
function setFormModeCreate(){form.action='/checkin'; currentRegNoField.value=''; generatedRegNoField.value=defaultRegNo; displayRegNo.textContent=defaultRegNo; saveButton.textContent='Save Check In'; Array.from(document.querySelectorAll('.checkin-record-row')).forEach(row => row.classList.remove('is-active'));}
function applyRecord(record){setFormModeCreate(); currentRegNoField.value=record.RegNo || ''; generatedRegNoField.value=record.RegNo || defaultRegNo; displayRegNo.textContent=record.RegNo || defaultRegNo; form.action='/checkin/'+encodeURIComponent(record.RegNo)+'/update'; saveButton.textContent='Update Check In'; const mappings=['ReservationNumber','GuestName','GuestName2','Address','Kelurahan','Kecamatan','KabCity','ProvinceCountry','TypeOfId','IdNumber','GroupPosition','PackageCode','TypeOfCheckIn','PlaceOfBirth','Religion','Nationality','NumberOfPerson','PaymentMethod','Company','CreditCardNumber','Segment','Phone','Email','Breakfast','Remarks','Member','Sales','RoomCode']; mappings.forEach(function(id){const field=document.getElementById(id); if(field){field.value=record[id] ?? '';}}); document.getElementById('CheckDeposit').checked=String(record.CheckDeposit || '0') === '1'; document.getElementById('CheckInDate').value=record.CheckInDate || ''; document.getElementById('CheckInDateDisplay').value=formatDisplayDate(record.CheckInDate || ''); document.querySelector('#CheckInDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=record.CheckInDate || ''; document.getElementById('BirthDate').value=record.BirthDate || ''; document.getElementById('BirthDateDisplay').value=formatDisplayDate(record.BirthDate || ''); document.querySelector('#BirthDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=record.BirthDate || ''; document.getElementById('ExpiredDate').value=record.ExpiredDate || ''; document.getElementById('ExpiredDateDisplay').value=formatDisplayDate(record.ExpiredDate || ''); document.querySelector('#ExpiredDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=record.ExpiredDate || ''; document.getElementById('EstimationOut').value=record.EstimationOut || ''; document.getElementById('EstimationOutDisplay').value=formatDisplayDate(record.EstimationOut || ''); document.querySelector('#EstimationOutDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=record.EstimationOut || ''; document.getElementById('CheckInTime').value=record.CheckInTime || ''; nominalField.value=formatRibuan(String(record.Nominal || '')); updateRoomHelper(); updatePackageHelper();}
function resetForm(){form.reset(); setFormModeCreate(); document.getElementById('CheckInDate').value=defaultCheckIn; document.getElementById('CheckInDateDisplay').value=formatDisplayDate(defaultCheckIn); document.querySelector('#CheckInDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=defaultCheckIn; document.getElementById('EstimationOut').value=defaultCheckOut; document.getElementById('EstimationOutDisplay').value=formatDisplayDate(defaultCheckOut); document.querySelector('#EstimationOutDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=defaultCheckOut; document.getElementById('BirthDate').value=''; document.getElementById('BirthDateDisplay').value=''; document.querySelector('#BirthDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=''; document.getElementById('ExpiredDate').value=''; document.getElementById('ExpiredDateDisplay').value=''; document.querySelector('#ExpiredDateDisplay').closest('[data-date-field]').querySelector('[data-date-native]').value=''; roomHelper.querySelector('strong').textContent='Pilih room yang masih tersedia'; packageHelper.querySelector('strong').textContent='Nominal akan terisi otomatis bila package ditemukan'; nominalField.value=''; document.getElementById('CheckInTime').value='{{ now()->format('H:i') }}'; document.getElementById('TypeOfCheckIn').value='GROUP RESERVATION'; document.getElementById('PaymentMethod').value='OTA'; document.getElementById('Segment').value='TRAVEL'; document.getElementById('Nationality').value='INA'; document.getElementById('NumberOfPerson').value='2'; document.getElementById('Breakfast').value='2'; document.getElementById('GuestName').focus();}

Array.from(document.querySelectorAll('[data-date-field]')).forEach(bindDateGroup);
roomCodeField.addEventListener('input', updateRoomHelper);
packageCodeField.addEventListener('input', updatePackageHelper);
nominalField.addEventListener('input', function(){this.value=formatRibuan(this.value);});
newEntryButton.addEventListener('click', resetForm);
focusSearchButton.addEventListener('click', function(){document.getElementById('checkinDirectoryShell').scrollIntoView({behavior:'smooth', block:'start'}); setTimeout(() => searchKeyword.focus(), 250);});
form.addEventListener('submit', function(event){const dateDisplays=['CheckInDateDisplay','BirthDateDisplay','ExpiredDateDisplay','EstimationOutDisplay']; for(const id of dateDisplays){const display=document.getElementById(id); if(!display){continue;} const group=display.closest('[data-date-field]'); const hidden=group.querySelector('input[type="hidden"]'); if(display.value.trim()===''){hidden.value=''; continue;} const iso=normalizeDisplayDate(display.value); if(!iso){event.preventDefault(); showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.'); display.focus(); return;} hidden.value=iso; group.querySelector('[data-date-native]').value=iso; display.value=formatDisplayDate(iso);} if(document.getElementById('EstimationOut').value < document.getElementById('CheckInDate').value){event.preventDefault(); showCrudAlert('Estimation Out tidak boleh lebih kecil dari Check In.'); document.getElementById('EstimationOutDisplay').focus(); return;} roomCodeField.value=roomCodeField.value.trim().toUpperCase(); packageCodeField.value=packageCodeField.value.trim().toUpperCase(); nominalField.value=normalizeNumber(nominalField.value);});
Array.from(document.querySelectorAll('.checkin-record-row')).forEach(function(row){row.addEventListener('click', function(event){if(event.target.closest('.checkin-delete-link')){return;} Array.from(document.querySelectorAll('.checkin-record-row')).forEach(item => item.classList.remove('is-active')); row.classList.add('is-active'); try {applyRecord(JSON.parse(row.dataset.record)); window.scrollTo({top:0, behavior:'smooth'});} catch(error){showCrudAlert('Data baris tidak bisa dimuat ke form.');}});});
form.addEventListener('keydown', function(event){if(event.key !== 'Enter' || event.target.tagName === 'TEXTAREA'){return;} const fields=Array.from(form.querySelectorAll('input[data-flow], select[data-flow], textarea[data-flow]')).filter(field => !field.disabled && field.offsetParent !== null); const index=fields.indexOf(event.target); if(index >= 0 && index < fields.length - 1){event.preventDefault(); fields[index + 1].focus(); fields[index + 1].select?.();}});
updateRoomHelper();
updatePackageHelper();
if({{ old('GuestName') || old('RoomCode') ? 'true' : 'false' }}){document.getElementById('GuestName').focus();} else {resetForm();}
const successAlert=document.getElementById('successAlert'); if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);}
</script>

@endsection
