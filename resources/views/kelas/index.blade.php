@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="kelas-topbar-title">Room Class</div>
@endsection

@section('content')

    @include('partials.crud-package-theme')
    <style>
        .kelas-topbar-title {
            color: #173761;
            font-size: 2rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
            font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
        }

        .kelas-page {
            padding: 0 0 2rem;
            color: var(--package-text);
        }

        .kelas-shell {
            border-radius: 28px;
        }

        .kelas-shell+.kelas-shell {
            margin-top: 1.5rem;
        }

        .kelas-shell-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.4rem 1.9rem 0.9rem;
        }

        .kelas-shell-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--package-text);
        }

        .kelas-shell-subtitle {
            margin: 0.35rem 0 0;
            font-size: 0.9rem;
            color: var(--package-muted);
        }

        .kelas-shell-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 0.8rem;
            border-radius: 999px;
            background: var(--package-badge-bg);
            color: var(--package-badge-text);
            font-weight: 700;
            font-size: 0.82rem;
        }

        .kelas-shell-body {
            padding: 1.25rem 1.9rem 1.75rem;
        }

        .kelas-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--package-label);
            margin-bottom: 0.55rem;
        }

        .kelas-input {
            height: calc(2.7rem + 2px);
        }

        .kelas-input:focus {
            border-color: var(--package-input-focus);
        }

        .kelas-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .kelas-btn-primary {
            padding: 0.82rem 1.5rem;
        }

        .kelas-btn-secondary {
            padding: 0.78rem 1.35rem;
        }

        .kelas-alert {
            border: 0;
            border-radius: 18px;
            padding: 0.95rem 1.15rem;
            background: linear-gradient(135deg, rgba(33, 150, 83, 0.16), rgba(33, 150, 83, 0.08));
            color: #1c6b40;
            box-shadow: 0 14px 30px rgba(28, 107, 64, 0.1);
        }

        .kelas-table-wrap {
            border-radius: 0 0 28px 28px;
        }

        .kelas-table {
            margin-bottom: 0;
        }

        .kelas-table thead th {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .kelas-table tbody tr {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            cursor: pointer;
        }

        .kelas-table tbody tr:nth-child(odd) {
            background: var(--package-table-odd);
        }

        .kelas-table tbody tr:nth-child(even) {
            background: var(--package-table-even);
        }

        .kelas-table tbody tr:hover,
        .kelas-table tbody tr:focus-within {
            background: var(--package-row-focus-bg);
            transform: translateY(-1px);
            box-shadow:
                0 0 0 2px var(--package-row-focus-ring),
                0 10px 22px var(--package-row-focus-shadow);
        }

        .kelas-table tbody tr:hover td,
        .kelas-table tbody tr:focus-within td {
            background: var(--package-row-focus-bg) !important;
            border-top-color: var(--package-row-focus-ring);
            border-bottom-color: var(--package-row-focus-ring);
            color: var(--package-row-focus-text);
        }

        .kelas-table tbody tr:hover td:first-child,
        .kelas-table tbody tr:focus-within td:first-child {
            box-shadow: inset 7px 0 0 var(--package-row-focus-ring);
        }

        .kelas-table tbody tr:hover td:last-child,
        .kelas-table tbody tr:focus-within td:last-child {
            box-shadow: inset -2px 0 0 var(--package-row-focus-ring);
        }

        .kelas-table tbody tr:hover .kelas-code,
        .kelas-table tbody tr:focus-within .kelas-code {
            background: #fff8e7;
            color: #0f513c;
            border: 1px solid var(--package-row-focus-ring);
            box-shadow: 0 4px 10px rgba(6, 46, 35, 0.18);
        }

        .kelas-table tbody td {
            vertical-align: middle;
            color: var(--package-text);
        }

        .kelas-code {
            display: inline-flex;
            align-items: center;
            min-width: 68px;
            justify-content: center;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: var(--package-badge-bg);
            color: var(--package-badge-text);
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .kelas-name {
            font-weight: 700;
        }

        .kelas-money {
            font-weight: 700;
            color: var(--package-title);
        }

        .kelas-delete {
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

        .kelas-delete:hover {
            background: #aa2f2f;
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .kelas-empty {
            text-align: center;
            padding: 2.2rem 1rem;
            color: var(--package-muted);
        }

    </style>

    <div class="container-fluid kelas-page">
        @if (session('success'))
            <div class="alert kelas-alert mb-4" id="successAlert">
                {{ session('success') }}
            </div>
        @endif

        <section class="kelas-shell">
            <div class="kelas-shell-header">
                <div>
                    <h2 class="kelas-shell-title">Input Room Class</h2>
                    <p class="kelas-shell-subtitle">Press Enter to move through each field, and the last field submits the
                        form automatically.</p>
                </div>
                <span class="kelas-shell-badge">Ready for Fast Entry</span>
            </div>

            <div class="kelas-shell-body">
                <form method="POST" action="/kelas" id="formKelas">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-lg-2 col-md-3">
                            <label class="kelas-label" for="RecordId">ID</label>
                            <input type="text" id="RecordId" class="form-control kelas-input" readonly>
                        </div>

                        <div class="form-group col-lg-2 col-md-3">
                            <label class="kelas-label" for="Kode">Kode</label>
                            <input type="text" name="Kode" id="Kode" class="form-control kelas-input" required>
                            <small class="text-muted d-block mt-2">If the code already exists, pressing Enter will load that
                                room class for editing.</small>
                        </div>

                        <div class="form-group col-lg-5 col-md-9">
                            <label class="kelas-label" for="Nama">Nama</label>
                            <input type="text" name="Nama" id="Nama" class="form-control kelas-input" required>
                        </div>

                        <div class="form-group col-lg-2 col-md-6">
                            <label class="kelas-label" for="Rate1">Rate</label>
                            <input type="text" name="Rate1" id="Rate1" class="form-control kelas-input text-right"
                                inputmode="numeric">
                        </div>

                        <div class="form-group col-lg-2 col-md-6">
                            <label class="kelas-label" for="Depo1">Deposit</label>
                            <input type="text" name="Depo1" id="Depo1" class="form-control kelas-input text-right"
                                inputmode="numeric">
                        </div>
                    </div>

                    <div class="kelas-actions">
                        <button class="btn text-white kelas-btn-primary" id="saveButton">Save Room Class</button>
                        <button type="button" class="btn kelas-btn-secondary" onclick="resetForm()" id="resetButton">Reset
                            Form</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="kelas-shell">
            <div class="kelas-shell-header">
                <div>
                    <h2 class="kelas-shell-title">Class Directory</h2>
                    <p class="kelas-shell-subtitle">Click any row to load its data into the form and switch directly into
                        edit mode.</p>
                </div>
            </div>

            <div class="kelas-table-wrap">
                <div class="table-responsive">
                    <table class="table kelas-table" id="tableKelas">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Deposit</th>
                                <th class="text-center" width="90">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelas as $k)
                                <tr data-id="{{ $k->id }}" data-kode="{{ $k->Kode }}"
                                    data-nama="{{ $k->Nama }}" data-rate="{{ $k->Rate1 }}"
                                    data-depo="{{ $k->Depo1 }}">
                                    <td>{{ $k->id ?? '-' }}</td>
                                    <td><span class="kelas-code">{{ $k->Kode }}</span></td>
                                    <td class="kelas-name">{{ $k->Nama }}</td>
                                    <td class="text-right kelas-money">{{ number_format($k->Rate1 ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right kelas-money">{{ number_format($k->Depo1 ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="/kelas/{{ $k->Kode }}/delete" class="kelas-delete" title="Delete"
                                            aria-label="Delete">&#128465;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="kelas-empty">No room class records yet. Create the first one
                                        to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script>
        function normalizeNumber(angka) {
            if (angka === null || angka === undefined) {
                return '';
            }

            const raw = angka.toString().trim();

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

        function formatRibuan(angka) {
            const normalized = normalizeNumber(angka);

            if (!normalized) {
                return '';
            }

            return normalized.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function unformat(angka) {
            return (angka || '').toString().replace(/\./g, '');
        }

        function normalizeCode(kode) {
            return (kode || '').toString().trim().toUpperCase();
        }

        const formKelas = document.getElementById('formKelas');
        const recordIdField = document.getElementById('RecordId');
        const kodeField = document.getElementById('Kode');
        const namaField = document.getElementById('Nama');
        const rateField = document.getElementById('Rate1');
        const depoField = document.getElementById('Depo1');
        const saveButton = document.getElementById('saveButton');
        const resetButton = document.getElementById('resetButton');
        const tableRows = Array.from(document.querySelectorAll('#tableKelas tbody tr[data-kode]'));

        const fields = [kodeField, namaField, rateField, depoField];
        const numericFields = [rateField, depoField];

        function findExistingRowByCode(kode) {
            const normalizedCode = normalizeCode(kode);

            return tableRows.find((row) => normalizeCode(row.dataset.kode) === normalizedCode) || null;
        }

        function loadRowIntoForm(row) {
            recordIdField.value = row.dataset.id || '';
            kodeField.value = row.dataset.kode;
            namaField.value = row.dataset.nama;
            rateField.value = formatRibuan(row.dataset.rate || '');
            depoField.value = formatRibuan(row.dataset.depo || '');

            kodeField.readOnly = true;
            formKelas.action = '/kelas/' + row.dataset.kode + '/update';
            saveButton.textContent = 'Update Room Class';
            resetButton.textContent = 'Cancel Edit';
            namaField.focus();
            namaField.select();
        }

        numericFields.forEach((field) => {
            field.addEventListener('input', function() {
                const angka = this.value.replace(/\D/g, '');
                this.value = formatRibuan(angka);
            });
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
                }

                if (index < fields.length - 1) {
                    fields[index + 1].focus();
                    fields[index + 1].select();
                    return;
                }

                formKelas.requestSubmit();
            });
        });

        document.querySelector('#tableKelas tbody').addEventListener('click', function(event) {
            if (event.target.closest('a')) {
                return;
            }

            const row = event.target.closest('tr');

            if (!row || !row.dataset.kode) {
                return;
            }

            loadRowIntoForm(row);
        });

        function resetForm() {
            recordIdField.value = '';
            formKelas.reset();
            kodeField.readOnly = false;
            formKelas.action = '/kelas';
            saveButton.textContent = 'Save Room Class';
            resetButton.textContent = 'Reset Form';
            kodeField.focus();
        }

        formKelas.addEventListener('submit', function() {
            kodeField.value = normalizeCode(kodeField.value);
            rateField.value = unformat(rateField.value);
            depoField.value = unformat(depoField.value);
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
