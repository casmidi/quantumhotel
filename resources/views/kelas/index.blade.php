@extends('layouts.app')

@section('title', 'Room Class')

@section('content')

<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success" id="successAlert">
        {{ session('success') }}
    </div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            Input Room Class
        </div>

        <div class="card-body">
            <form method="POST" action="/kelas" id="formKelas">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Kode</label>
                        <input type="text" name="Kode" id="Kode" class="form-control" required>
                    </div>

                    <div class="form-group col-md-5">
                        <label>Nama</label>
                        <input type="text" name="Nama" id="Nama" class="form-control" required>
                    </div>

                    <div class="form-group col-md-2">
                        <label>Rate</label>
                        <input type="text" name="Rate1" id="Rate1" class="form-control text-end" inputmode="numeric">
                    </div>

                    <div class="form-group col-md-2">
                        <label>Deposit</label>
                        <input type="text" name="Depo1" id="Depo1" class="form-control text-end" inputmode="numeric">
                    </div>
                </div>

                <div>
                    <button class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Data Room Class
        </div>

        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0" id="tableKelas">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Deposit</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelas as $k)
                    <tr data-kode="{{ $k->Kode }}"
                        data-nama="{{ $k->Nama }}"
                        data-rate="{{ $k->Rate1 }}"
                        data-depo="{{ $k->Depo1 }}">
                        <td><strong>{{ $k->Kode }}</strong></td>
                        <td>{{ $k->Nama }}</td>
                        <td class="text-end">{{ number_format($k->Rate1 ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($k->Depo1 ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <a href="/kelas/{{ $k->Kode }}/delete" class="btn btn-danger btn-sm" title="Delete" aria-label="Delete">&#128465;</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

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

const formKelas = document.getElementById('formKelas');
const kodeField = document.getElementById('Kode');
const namaField = document.getElementById('Nama');
const rateField = document.getElementById('Rate1');
const depoField = document.getElementById('Depo1');

const fields = [kodeField, namaField, rateField, depoField];
const numericFields = [rateField, depoField];

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

    kodeField.value = row.dataset.kode;
    namaField.value = row.dataset.nama;
    rateField.value = formatRibuan(row.dataset.rate || '');
    depoField.value = formatRibuan(row.dataset.depo || '');

    kodeField.readOnly = true;
    formKelas.action = '/kelas/' + row.dataset.kode + '/update';
    namaField.focus();
    namaField.select();
});

function resetForm() {
    formKelas.reset();
    kodeField.readOnly = false;
    formKelas.action = '/kelas';
    kodeField.focus();
}

formKelas.addEventListener('submit', function () {
    rateField.value = unformat(rateField.value);
    depoField.value = unformat(depoField.value);
});

const successAlert = document.getElementById('successAlert');

if (successAlert) {
    setTimeout(() => {
        successAlert.style.transition = 'opacity 0.3s ease';
        successAlert.style.opacity = '0';

        setTimeout(() => {
            successAlert.remove();
        }, 300);
    }, 3000);
}

kodeField.focus();
</script>

@endsection
