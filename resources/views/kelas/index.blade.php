<!DOCTYPE html>
<html>
<head>
    <title>Class Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: #f4f6f9; }

        .card {
            border: none;
            border-radius: 12px;
        }

        .table-hover tbody tr:hover {
            background: #eef4ff;
            cursor: pointer;
        }

        .form-control { border-radius: 8px; }
        .btn { border-radius: 8px; }

        .title { font-weight: 600; }
    </style>
</head>

<body>

<div class="container-fluid p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="title">Class Management</h3>
            <small class="text-muted">Manage room class and pricing</small>
        </div>

        <button class="btn btn-primary" onclick="clearForm()">
            <i class="bi bi-plus"></i> New
        </button>
    </div>

    <!-- FORM -->
    <div class="card p-3 mb-4 shadow-sm">
        <form method="POST" action="/kelas/save">
            @csrf

            <div class="row g-3">
                <div class="col-md-2">
                    <label>Class</label>
                    <input id="kode" name="kode" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label>Fasilitas</label>
                    <input id="nama" name="nama" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>Basic Rate</label>
                    <input id="rate1" name="rate1" class="form-control text-end">
                </div>

                <div class="col-md-2">
                    <label>Service</label>
                    <input id="rate2" name="rate2" class="form-control text-end">
                </div>

                <div class="col-md-2">
                    <label>Deposit</label>
                    <input id="depo1" name="depo1" class="form-control text-end">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Save
                </button>

                <button type="button" class="btn btn-secondary" onclick="clearForm()">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="card p-3 shadow-sm">

        <!-- SEARCH -->
        <div class="d-flex justify-content-between mb-3">
            <input type="text" id="search" class="form-control w-25" placeholder="🔍 Search..." onkeyup="filterTable()">

            <small class="text-muted align-self-center">
                Total: {{ count($data) }} data
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Deposit</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    @foreach($data as $d)
                    <tr>

                        <td>{{ $d->Kode }}</td>
                        <td>{{ $d->Nama }}</td>
                        <td class="text-end">{{ number_format($d->Rate1) }}</td>
                        <td class="text-end">{{ number_format($d->Depo1) }}</td>

                        <td class="text-center">

                            <!-- VIEW -->
                            <button class="btn btn-sm btn-info text-white"
                                onclick="viewData(
                                    '{{ $d->Kode }}',
                                    '{{ $d->Nama }}',
                                    '{{ $d->Rate1 }}',
                                    '{{ $d->Rate2 }}',
                                    '{{ $d->Depo1 }}'
                                )">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- EDIT -->
                            <button class="btn btn-sm btn-warning"
                                onclick="pilihData(
                                    '{{ $d->Kode }}',
                                    '{{ $d->Nama }}',
                                    '{{ $d->Rate1 }}',
                                    '{{ $d->Rate2 }}',
                                    '{{ $d->Depo1 }}',
                                    this
                                )">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- DELETE -->
                            <a href="/kelas/delete/{{ $d->Kode }}"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus data ini?')">
                                <i class="bi bi-trash"></i>
                            </a>

                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>

function pilihData(kode, nama, rate1, rate2, depo1, btn) {
    document.getElementById('kode').value = kode;
    document.getElementById('nama').value = nama;
    document.getElementById('rate1').value = rate1;
    document.getElementById('rate2').value = rate2;
    document.getElementById('depo1').value = depo1;

    // highlight row
    document.querySelectorAll("tbody tr").forEach(tr => tr.classList.remove("table-primary"));
    btn.closest('tr').classList.add("table-primary");
}

function clearForm() {
    document.getElementById('kode').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('rate1').value = '';
    document.getElementById('rate2').value = '';
    document.getElementById('depo1').value = '';

    document.querySelectorAll("tbody tr").forEach(tr => tr.classList.remove("table-primary"));
}

function viewData(kode, nama, rate1, rate2, depo1) {
    alert(
        "Class: " + kode + "\n" +
        "Fasilitas: " + nama + "\n" +
        "Rate: " + rate1 + "\n" +
        "Service: " + rate2 + "\n" +
        "Deposit: " + depo1
    );
}

function filterTable() {
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#tableBody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

</script>

</body>
</html>