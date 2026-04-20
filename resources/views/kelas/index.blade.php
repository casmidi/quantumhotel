@extends('layouts.app')

@section('title', 'Room Class')

@section('content')

    @php
        $totalKelas = $kelas->count();
        $avgRate = $kelas->avg('Rate1') ?? 0;
        $avgDepo = $kelas->avg('Depo1') ?? 0;
    @endphp

    <style>
        .content-wrapper {
            background:
                radial-gradient(circle at top right, rgba(183, 148, 92, 0.12), transparent 22%),
                radial-gradient(circle at left top, rgba(17, 24, 39, 0.08), transparent 28%),
                linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%);
            min-height: calc(100vh - 57px);
        }

        .content-wrapper>h3 {
            display: none;
        }

        .kelas-page {
            padding: 1.25rem 0 2rem;
            color: #10233b;
        }

        .kelas-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #10233b 0%, #19395f 55%, #b38a51 140%);
            border-radius: 24px;
            color: #fff;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 24px 60px rgba(16, 35, 59, 0.2);
        }

        .kelas-hero::after {
            content: '';
            position: absolute;
            top: -80px;
            right: -20px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
        }

        .kelas-kicker {
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

        .kelas-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.1;
            margin: 0 0 0.75rem;
        }

        .kelas-hero p {
            max-width: 680px;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 1rem;
        }

        .kelas-summary {
            margin-top: 1.5rem;
        }

        .kelas-stat {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 18px;
            padding: 1rem 1.1rem;
            backdrop-filter: blur(12px);
            min-height: 100%;
        }

        .kelas-stat-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.72);
            margin-bottom: 0.5rem;
        }

        .kelas-stat-value {
            display: block;
            font-size: 1.35rem;
            font-weight: 700;
            color: #fff;
        }

        .kelas-shell {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 18px 50px rgba(16, 35, 59, 0.1);
            backdrop-filter: blur(16px);
            border-radius: 24px;
        }

        .kelas-shell+.kelas-shell {
            margin-top: 1.5rem;
        }

        .kelas-shell-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.35rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(16, 35, 59, 0.08);
        }

        .kelas-shell-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #10233b;
        }

        .kelas-shell-subtitle {
            margin: 0.35rem 0 0;
            font-size: 0.9rem;
            color: #5f6f84;
        }

        .kelas-shell-badge {
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

        .kelas-shell-body {
            padding: 1.5rem;
        }

        .kelas-label {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5f6f84;
            margin-bottom: 0.55rem;
        }

        .kelas-input {
            height: calc(2.6rem + 2px);
            border-radius: 14px;
            border: 1px solid rgba(16, 35, 59, 0.12);
            box-shadow: inset 0 1px 2px rgba(16, 35, 59, 0.04);
            background: rgba(255, 255, 255, 0.92);
            color: #10233b;
            font-weight: 600;
        }

        .kelas-input:focus {
            border-color: rgba(179, 138, 81, 0.78);
            box-shadow: 0 0 0 0.2rem rgba(179, 138, 81, 0.14);
        }

        .kelas-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .kelas-btn-primary {
            border: 0;
            border-radius: 999px;
            padding: 0.75rem 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #173761 0%, #1e4b80 55%, #b38a51 150%);
            box-shadow: 0 12px 26px rgba(23, 55, 97, 0.2);
        }

        .kelas-btn-secondary {
            border-radius: 999px;
            padding: 0.75rem 1.3rem;
            font-weight: 700;
            border: 1px solid rgba(16, 35, 59, 0.12);
            background: rgba(255, 255, 255, 0.78);
            color: #173761;
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
            border-radius: 0 0 24px 24px;
            overflow: hidden;
        }

        .kelas-table {
            margin-bottom: 0;
        }

        .kelas-table thead th {
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

        .kelas-table tbody tr {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            cursor: pointer;
        }

        .kelas-table tbody tr:nth-child(odd) {
            background: rgba(16, 35, 59, 0.045);
        }

        .kelas-table tbody tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.96);
        }

        .kelas-table tbody tr:hover {
            background: rgba(179, 138, 81, 0.06);
            transform: translateY(-1px);
            box-shadow: inset 4px 0 0 #b38a51;
        }

        .kelas-table tbody td {
            border-top: 1px solid rgba(16, 35, 59, 0.06);
            padding: 1rem 1.2rem;
            vertical-align: middle;
            color: #10233b;
        }

        .kelas-code {
            display: inline-flex;
            align-items: center;
            min-width: 68px;
            justify-content: center;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            background: rgba(23, 55, 97, 0.08);
            color: #173761;
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .kelas-name {
            font-weight: 700;
        }

        .kelas-money {
            font-weight: 700;
            color: #173761;
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
            color: #6b7b90;
        }

        @media (max-width: 991.98px) {
            .kelas-hero {
                padding: 1.5rem;
            }

            .kelas-hero h1 {
                font-size: 1.8rem;
            }
        }
    </style>

    <div class="container-fluid kelas-page">
        @if (session('success'))
            <div class="alert kelas-alert mb-4" id="successAlert">
                {{ session('success') }}
            </div>
        @endif

        <section class="kelas-hero">
            <div class="kelas-kicker">
                <span>Quantum Hotel</span>
                <span>Master Data</span>
            </div>
            <h1>Room Class Management</h1>
            <p>Manage room classes with a more elegant interface, faster operator flow, and a polished CRUD foundation for
                the next hotel system modules.</p>

            <div class="row kelas-summary">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="kelas-stat">
                        <span class="kelas-stat-label">Total Class</span>
                        <span class="kelas-stat-value">{{ number_format($totalKelas, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="kelas-stat">
                        <span class="kelas-stat-label">Average Rate</span>
                        <span class="kelas-stat-value">Rp {{ number_format($avgRate, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="kelas-stat">
                        <span class="kelas-stat-label">Average Deposit</span>
                        <span class="kelas-stat-value">Rp {{ number_format($avgDepo, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </section>

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
                <span class="kelas-shell-badge">{{ number_format($totalKelas, 0, ',', '.') }} Records</span>
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
                                @php $rowNo = ($kelas->currentPage() - 1) * $kelas->perPage() + $loop->iteration; @endphp
                                <tr data-id="{{ $rowNo }}" data-kode="{{ $k->Kode }}"
                                    data-nama="{{ $k->Nama }}" data-rate="{{ $k->Rate1 }}"
                                    data-depo="{{ $k->Depo1 }}">
                                    <td>{{ $rowNo }}</td>
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
