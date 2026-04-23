@extends('layouts.app')

@section('title', '')

@section('content')

    @include('partials.crud-package-theme')

    <style>
        .room-code {
            display: inline-flex;
            align-items: center;
            min-width: 72px;
            justify-content: center;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: var(--package-badge-bg);
            color: var(--package-badge-text);
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .room-class {
            font-weight: 700;
        }

        .room-money {
            font-weight: 700;
            color: var(--package-title);
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
            color: var(--package-muted);
        }
    </style>

    <div class="container-fluid room-page">
        @if (session('success'))
            <div class="alert room-alert mb-4" id="successAlert">
                {{ session('success') }}
            </div>
        @endif

        <section class="room-shell">
            <div class="room-shell-header">
                <div>
                    <h2 class="room-shell-title">Input Room Master</h2>
                    <p class="room-shell-subtitle">Press Enter to move through each field. At the last field, Enter saves the
                        room immediately.</p>
                </div>
                <span class="room-shell-badge">Linked to Class Master</span>
            </div>

            <div class="room-shell-body">
                <form method="POST" action="/room" id="formRoom">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-lg-2 col-md-3">
                            <label class="room-label" for="RecordId">ID</label>
                            <input type="text" id="RecordId" class="form-control room-input" readonly>
                        </div>

                        <div class="form-group col-lg-2 col-md-3">
                            <label class="room-label" for="Kode">Room</label>
                            <input type="text" name="Kode" id="Kode" class="form-control room-input" required>
                            <small class="text-muted d-block mt-2">If the room code already exists, pressing Enter loads
                                that room into edit mode.</small>
                        </div>

                        <div class="form-group col-lg-4 col-md-5">
                            <label class="room-label" for="Nama">Class</label>
                            <select name="Nama" id="Nama" class="form-control room-input" required>
                                <option value="">Select room class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->Kode }}" data-fasilitas="{{ $class->Nama }}"
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
                            <input type="text" name="Rate1" id="Rate1" class="form-control room-input text-right"
                                inputmode="numeric">
                        </div>

                        <div class="form-group col-lg-3 col-md-6">
                            <label class="room-label" for="Rate2">Basic Rate</label>
                            <input type="text" name="Rate2" id="Rate2" class="form-control room-input text-right"
                                inputmode="numeric">
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
                        <button type="button" class="btn room-btn-secondary" onclick="resetRoomForm()"
                            id="resetButton">Reset Form</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="room-shell">
            <div class="room-shell-header">
                <div>
                    <h2 class="room-shell-title">Room Directory</h2>
                    <p class="room-shell-subtitle">Click any row to load the room into the form and continue directly in
                        update mode.</p>
                </div>
            </div>

            <div class="room-table-wrap">
                <div class="table-responsive">
                    <table class="table room-table" id="tableRoom">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                                <tr data-id="{{ $room->id }}" data-kode="{{ $room->Kode }}"
                                    data-nama="{{ $room->Nama }}" data-fasilitas="{{ $room->Fasilitas }}"
                                    data-rate1="{{ $room->Rate1 }}" data-rate2="{{ $room->Rate2 }}"
                                    data-extno="{{ $room->ExtNo }}" data-kunci="{{ $room->KUNCI }}">
                                    <td>{{ $room->id ?? '-' }}</td>
                                    <td><span class="room-code">{{ $room->Kode }}</span></td>
                                    <td class="room-class">{{ $room->Nama }}</td>
                                    <td>{{ $room->Fasilitas }}</td>
                                    <td class="text-right room-money">{{ number_format($room->Rate1 ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right room-money">{{ number_format($room->Rate2 ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td>{{ $room->ExtNo }}</td>
                                    <td>{{ $room->KUNCI }}</td>
                                    <td class="text-center">
                                        <a href="/room/{{ $room->Kode }}/delete" class="room-delete" title="Delete"
                                            aria-label="Delete"
                                            data-confirm-delete="Are you sure you want to delete this room?">&#128465;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="room-empty">No room records yet. Create the first room to
                                        start building inventory.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($rooms->hasPages())
                <div class="room-pagination-wrap">
                    {{ $rooms->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            @endif
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
        const recordIdField = document.getElementById('RecordId');
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
            recordIdField.value = row.dataset.id || '';
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
            field.addEventListener('input', function() {
                const numbers = this.value.replace(/\D/g, '');
                this.value = formatRibuan(numbers);
            });
        });

        classField.addEventListener('change', function() {
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

        document.querySelector('#tableRoom tbody').addEventListener('click', function(event) {
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
            recordIdField.value = '';
            formRoom.reset();
            kodeField.readOnly = false;
            formRoom.action = '/room';
            saveButton.textContent = 'Save Room';
            resetButton.textContent = 'Reset Form';
            kodeField.focus();
        }

        formRoom.addEventListener('submit', function() {
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
