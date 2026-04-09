@extends('layouts.app')

@section('title', '')

@section('content')

@include('partials.crud-package-theme')

<style>
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
</style>

<div class="container-fluid kelas-page">
    @if(session('success'))
    <div class="alert kelas-alert mb-4" id="successAlert">
        {{ session('success') }}
    </div>
    @endif

    <section class="kelas-shell">
        <div class="kelas-shell-header">
            <div>
                <h2 class="kelas-shell-title">Input Room Class</h2>
                <p class="kelas-shell-subtitle">Press Enter to move through each field, and the last field submits the form automatically.</p>
            </div>
            <span class="kelas-shell-badge">Ready for Fast Entry</span>
        </div>

        <div class="kelas-shell-body">
            <form method="POST" action="/kelas" id="formKelas">
                @csrf

                <div class="form-row">
                    <div class="form-group col-lg-2 col-md-3">
                        <label class="kelas-label" for="Kode">Kode</label>
                        <input type="text" name="Kode" id="Kode" class="form-control kelas-input" required>
                        <small class="text-muted d-block mt-2">If the code already exists, pressing Enter will load that room class for editing.</small>
                    </div>

                    <div class="form-group col-lg-5 col-md-9">
                        <label class="kelas-label" for="Nama">Nama</label>
                        <input type="text" name="Nama" id="Nama" class="form-control kelas-input" required>
                    </div>

                    <div class="form-group col-lg-2 col-md-6">
                        <label class="kelas-label" for="Rate1">Rate</label>
                        <input type="text" name="Rate1" id="Rate1" class="form-control kelas-input text-right" inputmode="numeric">
                    </div>

                    <div class="form-group col-lg-2 col-md-6">
                        <label class="kelas-label" for="Depo1">Deposit</label>
                        <input type="text" name="Depo1" id="Depo1" class="form-control kelas-input text-right" inputmode="numeric">
                    </div>
                </div>

                <div class="kelas-actions">
                    <button class="btn text-white kelas-btn-primary" id="saveButton">Save Room Class</button>
                    <button type="button" class="btn kelas-btn-secondary" onclick="resetForm()" id="resetButton">Reset Form</button>
                </div>
            </form>
        </div>
    </section>

    <section class="kelas-shell">
        <div class="kelas-shell-header">
            <div>
                <h2 class="kelas-shell-title">Class Directory</h2>
                <p class="kelas-shell-subtitle">Click any row to load its data into the form and switch directly into edit mode.</p>
            </div>
            <span class="kelas-shell-badge">{{ number_format($kelas->total(), 0, ',', '.') }} Records</span>
        </div>

        <div class="kelas-table-wrap">
            <div class="table-responsive">
                <table class="table kelas-table" id="tableKelas">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Deposit</th>
                            <th class="text-center" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kelas as $k)
                        <tr data-kode="{{ $k->Kode }}"
                            data-nama="{{ $k->Nama }}"
                            data-rate="{{ $k->Rate1 }}"
                            data-depo="{{ $k->Depo1 }}">
                            <td><span class="kelas-code">{{ $k->Kode }}</span></td>
                            <td class="kelas-name">{{ $k->Nama }}</td>
                            <td class="text-right kelas-money">{{ number_format($k->Rate1 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right kelas-money">{{ number_format($k->Depo1 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="/kelas/{{ $k->Kode }}/delete" class="kelas-delete" title="Delete" aria-label="Delete" data-confirm-delete="Are you sure you want to delete this room class?">&#128465;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="kelas-empty">No room class records yet. Create the first one to get started.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($kelas->hasPages())
        <div class="kelas-pagination-wrap">
            {{ $kelas->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
        @endif
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
    field.addEventListener('input', function () {
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

document.querySelector('#tableKelas tbody').addEventListener('click', function (event) {
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
    formKelas.reset();
    kodeField.readOnly = false;
    formKelas.action = '/kelas';
    saveButton.textContent = 'Save Room Class';
    resetButton.textContent = 'Reset Form';
    kodeField.focus();
}

formKelas.addEventListener('submit', function () {
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







