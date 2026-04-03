@extends('layouts.app')

@section('title', 'Room')

@section('content')

@php
    $totalRooms = $rooms->count();
    $avgRate = $rooms->avg('Rate1') ?? 0;
    $avgBasicRate = $rooms->avg('Rate2') ?? 0;
@endphp

<style>
    .content-wrapper {
        background:
            radial-gradient(circle at top right, rgba(183, 148, 92, 0.12), transparent 22%),
            radial-gradient(circle at left top, rgba(17, 24, 39, 0.08), transparent 28%),
            linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%);
        min-height: 100vh;
    }

    .content-wrapper > h3 {
        display: none;
    }

    .room-page {
        padding: 0 0 2rem;
        color: #10233b;
    }

    .room-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #10233b 0%, #19395f 55%, #b38a51 140%);
        border-radius: 24px;
        color: #fff;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 24px 60px rgba(16, 35, 59, 0.2);
    }

    .room-hero::after {
        content: '';
        position: absolute;
        top: -80px;
        right: -20px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
    }

    .room-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .room-hero h1 {
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1.1;
        margin: 0 0 0.75rem;
    }

    .room-hero p {
        max-width: 760px;
        margin: 0;
        color: rgba(255, 255, 255, 0.82);
        font-size: 1rem;
    }

    .room-summary {
        margin-top: 1.5rem;
    }

    .room-stat {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 18px;
        padding: 1rem 1.1rem;
        backdrop-filter: blur(12px);
        min-height: 100%;
    }

    .room-stat-label {
        display: block;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 0.5rem;
    }

    .room-stat-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: #fff;
    }

    .room-shell {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 18px 50px rgba(16, 35, 59, 0.1);
        backdrop-filter: blur(16px);
        border-radius: 24px;
    }

    .room-shell + .room-shell {
        margin-top: 1.5rem;
    }

    .room-shell-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.35rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
    }

    .room-shell-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #10233b;
    }

    .room-shell-subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.9rem;
        color: #5f6f84;
    }

    .room-shell-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.8rem;
        border-radius: 999px;
        background: rgba(179, 138, 81, 0.12);
        color: #8b6232;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .room-shell-body {
        padding: 1.5rem;
    }

    .room-label {
        display: block;
        font-size: 0.84rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #5f6f84;
        margin-bottom: 0.55rem;
    }

    .room-input {
        height: calc(2.6rem + 2px);
        border-radius: 14px;
        border: 1px solid rgba(16, 35, 59, 0.12);
        box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
        background: rgba(255, 255, 255, 0.92);
        color: #10233b;
        font-weight: 600;
    }

    .room-input:focus {
        border-color: rgba(179, 138, 81, 0.78);
        box-shadow: 0 0 0 0.2rem rgba(179, 138, 81, 0.14);
    }

    .room-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }

    .room-btn-primary {
        border: 0;
        border-radius: 999px;
        padding: 0.75rem 1.4rem;
        font-weight: 700;
        background: linear-gradient(135deg, #173761 0%, #1e4b80 55%, #b38a51 150%);
        box-shadow: 0 12px 26px rgba(23, 55, 97, 0.2);
        color: #fff;
    }

    .room-btn-secondary {
        border-radius: 999px;
        padding: 0.75rem 1.3rem;
        font-weight: 700;
        border: 1px solid rgba(16, 35, 59, 0.12);
        background: rgba(255, 255, 255, 0.78);
        color: #173761;
    }

    .room-alert {
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1.15rem;
        background: linear-gradient(135deg, rgba(33, 150, 83, 0.16), rgba(33, 150, 83, 0.08));
        color: #1c6b40;
        box-shadow: 0 14px 30px rgba(28, 107, 64, 0.1);
    }

    .room-table-wrap {
        border-radius: 0 0 24px 24px;
        overflow: hidden;
    }

    .room-table {
        margin-bottom: 0;
    }

    .room-table thead th {
        border-top: 0;
        border-bottom: 1px solid rgba(16, 35, 59, 0.08);
        background: linear-gradient(180deg, rgba(16, 35, 59, 0.02), rgba(16, 35, 59, 0.06));
        color: #53657d;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 1rem 1.2rem;
    }

    .room-table tbody tr {
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        cursor: pointer;
    }

    .room-table tbody tr:nth-child(odd) {
        background: rgba(16, 35, 59, 0.045);
    }

    .room-table tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.96);
    }

    .room-table tbody tr:hover {
        background: rgba(179, 138, 81, 0.06);
        transform: translateY(-1px);
        box-shadow: inset 4px 0 0 #b38a51;
    }

    .room-table tbody td {
        border-top: 1px solid rgba(16, 35, 59, 0.06);
        padding: 1rem 1.2rem;
        vertical-align: middle;
        color: #10233b;
    }

    .room-code {
        display: inline-flex;
        align-items: center;
        min-width: 72px;
        justify-content: center;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: rgba(23, 55, 97, 0.08);
        color: #173761;
        font-weight: 700;
        letter-spacing: 0.06em;
    }

    .room-class {
        font-weight: 700;
    }

    .room-money {
        font-weight: 700;
        color: #173761;
    }

    .room-delete {
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
        font-size: 1rem;
        transition: all 0.18s ease;
    }

    .room-delete:hover {
        background: #aa2f2f;
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .room-empty {
        text-align: center;
        padding: 2.2rem 1rem;
        color: #6b7b90;
    }

    @media (max-width: 991.98px) {
        .room-hero {
            padding: 1.5rem;
        }

        .room-hero h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container-fluid room-page">
    @if(session('success'))
    <div class="alert room-alert mb-4" id="successAlert">
        {{ session('success') }}
    </div>
    @endif

    <section class="room-hero">
        <div class="room-kicker">
            <span>Quantum Hotel</span>
            <span>Room Master</span>
        </div>
        <h1>Room Master Management</h1>
        <p>Build room inventory with the same premium CRUD workflow as Room Class, while keeping class-based autofill and room-code driven editing fast for front office operators.</p>

        <div class="row room-summary">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="room-stat">
                    <span class="room-stat-label">Total Room</span>
                    <span class="room-stat-value">{{ number_format($totalRooms, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="room-stat">
                    <span class="room-stat-label">Average Sell Rate</span>
                    <span class="room-stat-value">Rp {{ number_format($avgRate, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="room-stat">
                    <span class="room-stat-label">Average Basic Rate</span>
                    <span class="room-stat-value">Rp {{ number_format($avgBasicRate, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="room-shell">
        <div class="room-shell-header">
            <div>
                <h2 class="room-shell-title">Input Room Master</h2>
                <p class="room-shell-subtitle">Press Enter to move through each field. At the last field, Enter saves the room immediately.</p>
            </div>
            <span class="room-shell-badge">Linked to Class Master</span>
        </div>

        <div class="room-shell-body">
            <form method="POST" action="/room" id="formRoom">
                @csrf

                <div class="form-row">
                    <div class="form-group col-lg-2 col-md-3">
                        <label class="room-label" for="Kode">Room</label>
                        <input type="text" name="Kode" id="Kode" class="form-control room-input" required>
                        <small class="text-muted d-block mt-2">If the room code already exists, pressing Enter loads that room into edit mode.</small>
                    </div>

                    <div class="form-group col-lg-4 col-md-5">
                        <label class="room-label" for="Nama">Class</label>
                        <select name="Nama" id="Nama" class="form-control room-input" required>
                            <option value="">Select room class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->Kode }}"
                                data-fasilitas="{{ $class->Nama }}"
                                data-rate="{{ $class->Rate1 ?? 0 }}">
                                {{ $class->Kode }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-lg-6 col-md-4">
                        <label class="room-label" for="Fasilitas">Facility</label>
                        <input type="text" name="Fasilitas" id="Fasilitas" class="form-control room-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-lg-3 col-md-6">
                        <label class="room-label" for="Rate1">Rate + Tax &amp; Serv</label>
                        <input type="text" name="Rate1" id="Rate1" class="form-control room-input text-right" inputmode="numeric">
                    </div>

                    <div class="form-group col-lg-3 col-md-6">
                        <label class="room-label" for="Rate2">Basic Rate</label>
                        <input type="text" name="Rate2" id="Rate2" class="form-control room-input text-right" inputmode="numeric">
                    </div>

                    <div class="form-group col-lg-3 col-md-6">
                        <label class="room-label" for="ExtNo">Extension No</label>
                        <input type="text" name="ExtNo" id="ExtNo" class="form-control room-input">
                    </div>

                    <div class="form-group col-lg-3 col-md-6">
                        <label class="room-label" for="KUNCI">Room Key</label>
                        <input type="text" name="KUNCI" id="KUNCI" class="form-control room-input">
                    </div>
                </div>

                <div class="room-actions">
                    <button class="btn room-btn-primary" id="saveButton">Save Room</button>
                    <button type="button" class="btn room-btn-secondary" onclick="resetRoomForm()" id="resetButton">Reset Form</button>
                </div>
            </form>
        </div>
    </section>

    <section class="room-shell">
        <div class="room-shell-header">
            <div>
                <h2 class="room-shell-title">Room Directory</h2>
                <p class="room-shell-subtitle">Click any row to load the room into the form and continue directly in update mode.</p>
            </div>
            <span class="room-shell-badge">{{ number_format($totalRooms, 0, ',', '.') }} Records</span>
        </div>

        <div class="room-table-wrap">
            <div class="table-responsive">
                <table class="table room-table" id="tableRoom">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Class</th>
                            <th>Facility</th>
                            <th class="text-right">Rate + Tax</th>
                            <th class="text-right">Basic Rate</th>
                            <th>Ext No</th>
                            <th>Room Key</th>
                            <th class="text-center" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        <tr data-kode="{{ $room->Kode }}"
                            data-nama="{{ $room->Nama }}"
                            data-fasilitas="{{ $room->Fasilitas }}"
                            data-rate1="{{ $room->Rate1 }}"
                            data-rate2="{{ $room->Rate2 }}"
                            data-extno="{{ $room->ExtNo }}"
                            data-kunci="{{ $room->KUNCI }}">
                            <td><span class="room-code">{{ $room->Kode }}</span></td>
                            <td class="room-class">{{ $room->Nama }}</td>
                            <td>{{ $room->Fasilitas }}</td>
                            <td class="text-right room-money">{{ number_format($room->Rate1 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right room-money">{{ number_format($room->Rate2 ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $room->ExtNo }}</td>
                            <td>{{ $room->KUNCI }}</td>
                            <td class="text-center">
                                <a href="/room/{{ $room->Kode }}/delete" class="room-delete" title="Delete" aria-label="Delete">&#128465;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="room-empty">No room records yet. Create the first room to start building inventory.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
function normalizeNumber(value) {
    if (value === null || value === undefined) {
        return '';
    }

    const raw = value.toString().trim();

    if (raw === '') {
        return '';
    }

    if (raw.includes('.')) {
        const [integerPart, decimalPart = ''] = raw.split('.');
        const cleanInteger = integerPart.replace(/\D/g, '');

        if (/^0+$/.test(decimalPart)) {
            return cleanInteger;
        }
    }

    return raw.replace(/\D/g, '');
}

function formatRibuan(value) {
    const normalized = normalizeNumber(value);

    if (!normalized) {
        return '';
    }

    return normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function unformat(value) {
    return (value || '').toString().replace(/\./g, '');
}

function normalizeCode(value) {
    return (value || '').toString().trim().toUpperCase();
}

const formRoom = document.getElementById('formRoom');
const kodeField = document.getElementById('Kode');
const classField = document.getElementById('Nama');
const fasilitasField = document.getElementById('Fasilitas');
const rate1Field = document.getElementById('Rate1');
const rate2Field = document.getElementById('Rate2');
const extNoField = document.getElementById('ExtNo');
const roomKeyField = document.getElementById('KUNCI');
const saveButton = document.getElementById('saveButton');
const resetButton = document.getElementById('resetButton');
const tableRows = Array.from(document.querySelectorAll('#tableRoom tbody tr[data-kode]'));

const fields = [kodeField, classField, fasilitasField, rate1Field, rate2Field, extNoField, roomKeyField];
const numericFields = [rate1Field, rate2Field];

function findExistingRowByCode(kode) {
    const normalizedCode = normalizeCode(kode);

    return tableRows.find((row) => normalizeCode(row.dataset.kode) === normalizedCode) || null;
}

function autofillFromSelectedClass() {
    const selectedOption = classField.options[classField.selectedIndex];

    if (!selectedOption || !selectedOption.value) {
        return;
    }

    fasilitasField.value = selectedOption.dataset.fasilitas || '';
    rate1Field.value = formatRibuan(selectedOption.dataset.rate || '');
    rate2Field.value = formatRibuan(selectedOption.dataset.rate || '');
}

function loadRowIntoForm(row) {
    kodeField.value = row.dataset.kode;
    classField.value = row.dataset.nama;
    fasilitasField.value = row.dataset.fasilitas || '';
    rate1Field.value = formatRibuan(row.dataset.rate1 || '');
    rate2Field.value = formatRibuan(row.dataset.rate2 || '');
    extNoField.value = row.dataset.extno || '';
    roomKeyField.value = row.dataset.kunci || '';

    kodeField.readOnly = true;
    formRoom.action = '/room/' + row.dataset.kode + '/update';
    saveButton.textContent = 'Update Room';
    resetButton.textContent = 'Cancel Edit';
    classField.focus();
}

numericFields.forEach((field) => {
    field.addEventListener('input', function () {
        const numbers = this.value.replace(/\D/g, '');
        this.value = formatRibuan(numbers);
    });
});

classField.addEventListener('change', function () {
    autofillFromSelectedClass();
});

fields.forEach((field, index) => {
    field.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();

        if (field === kodeField) {
            const existingRow = findExistingRowByCode(kodeField.value);

            if (existingRow) {
                loadRowIntoForm(existingRow);
                return;
            }

            kodeField.value = normalizeCode(kodeField.value);

            if (!roomKeyField.value) {
                roomKeyField.value = kodeField.value;
            }
        }

        if (field === classField) {
            autofillFromSelectedClass();
        }

        if (index < fields.length - 1) {
            fields[index + 1].focus();

            if (typeof fields[index + 1].select === 'function') {
                fields[index + 1].select();
            }

            return;
        }

        formRoom.requestSubmit();
    });
});

document.querySelector('#tableRoom tbody').addEventListener('click', function (event) {
    if (event.target.closest('a')) {
        return;
    }

    const row = event.target.closest('tr');

    if (!row || !row.dataset.kode) {
        return;
    }

    loadRowIntoForm(row);
});

function resetRoomForm() {
    formRoom.reset();
    kodeField.readOnly = false;
    formRoom.action = '/room';
    saveButton.textContent = 'Save Room';
    resetButton.textContent = 'Reset Form';
    kodeField.focus();
}

formRoom.addEventListener('submit', function () {
    kodeField.value = normalizeCode(kodeField.value);
    classField.value = normalizeCode(classField.value);
    rate1Field.value = unformat(rate1Field.value);
    rate2Field.value = unformat(rate2Field.value);
});

const successAlert = document.getElementById('successAlert');

if (successAlert) {
    setTimeout(() => {
        successAlert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        successAlert.style.opacity = '0';
        successAlert.style.transform = 'translateY(-8px)';

        setTimeout(() => {
            successAlert.remove();
        }, 300);
    }, 3000);
}

kodeField.focus();
</script>

@endsection
