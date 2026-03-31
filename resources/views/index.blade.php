<!DOCTYPE html>
<html>
<head>
    <title>Class Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
        }
        .card {
            border-radius: 12px;
        }
        table tbody tr:hover {
            background: #f1f1f1;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container mt-4">

    <h3 class="mb-3">Class Master</h3>

    <!-- FORM -->
    <div class="card p-3 mb-4 shadow-sm">
        <form method="POST" action="/kelas/save">
            @csrf

            <div class="row">
                <div class="col-md-2">
                    <label>Class</label>
                    <input type="text" name="kode" class="form-control"
                           value="{{ $row->Kode ?? '' }}" required>
                </div>

                <div class="col-md-4">
                    <label>Fasilitas</label>
                    <input type="text" name="nama" class="form-control"
                           value="{{ $row->Nama ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label>Basic Rate</label>
                    <input type="number" name="rate1" class="form-control"
                           value="{{ $row->Rate1 ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label>Rate Service</label>
                    <input type="number" name="rate2" class="form-control"
                           value="{{ $row->Rate2 ?? '' }}">
                </div>

                <div class="col-md-2">
                    <label>Deposit</label>
                    <input type="number" name="depo1" class="form-control"
                           value="{{ $row->Depo1 ?? '' }}">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">💾 Save</button>

                @if(isset($row))
                    <a href="/kelas/delete/{{ $row->Kode }}" class="btn btn-danger">
                        🗑 Delete
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- GRID -->
    <div class="card p-3 shadow-sm">
        <h5>Data Kelas</h5>

        <table class="table table-bordered mt-2">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Rate</th>
                    <th>Deposit</th>
                </tr>
            </thead>

            <tbody>
                @foreach($data as $d)
                <tr onclick="window.location='/kelas/edit/{{ $d->Kode }}'">
                    <td>{{ $d->Kode }}</td>
                    <td>{{ $d->Nama }}</td>
                    <td>{{ number_format($d->Rate1) }}</td>
                    <td>{{ number_format($d->Depo1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</div>

</body>
</html>