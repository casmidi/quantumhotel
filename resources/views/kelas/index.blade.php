@extends('layouts.app')

@section('title', 'Room Class')

@section('content')

<style>
/* Zebra */
#tableKelas tbody tr:nth-child(even) {
    background-color: #f2f2f2;
}

/* Hover */
#tableKelas tbody tr:hover {
    background-color: #d6e9ff !important;
    cursor: pointer;
}
</style>

<div class="row">

    <!-- ERROR -->
    @if ($errors->any())
    <div class="col-md-12">
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- SUCCESS -->
    @if(session('success'))
    <div class="col-md-12">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
    @endif

    <!-- FORM -->
    <div class="col-md-12">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">Room Class Form</h3>
            </div>

            <form method="POST" action="/kelas" id="formKelas">
                @csrf

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-3">
                            <label>Kode</label>
                            <input type="text" name="Kode" id="Kode" class="form-control" required>
                        </div>

                        <div class="col-md-5">
                            <label>Nama</label>
                            <input type="text" name="Nama" id="Nama" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Rate</label>
                            <input type="text" name="Rate1" id="Rate1" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Deposit</label>
                            <input type="text" name="Depo1" id="Depo1" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-secondary" id="btnReset">Reset</button>
                </div>

            </form>

        </div>
    </div>

    <!-- TABLE -->
    <div class="col-md-12">
        <div class="card mt-3">

            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">Room Class List</h3>

                <form method="GET">
                    <input type="text" name="q" placeholder="Search..."
                        class="form-control form-control-sm"
                        value="{{ request('q') }}">
                </form>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered" id="tableKelas">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Rate</th>
                            <th>Deposit</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($kelas as $k)
                        <tr>
                            <td>{{ $k->Kode }}</td>
                            <td>{{ $k->Nama }}</td>
                            <td>{{ number_format($k->Rate1,0,',','.') }}</td>
                            <td>{{ number_format($k->Depo1,0,',','.') }}</td>
                            <td>
                                <a href="/kelas/{{ $k->Kode }}/delete"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this data?')">
                                   🗑
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // =========================
    // FORMAT RIBUAN (VB6 STYLE)
    // =========================
    function formatRibuan(angka) {
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function handleInput(el) {
        let cursorPos = el.selectionStart;
        let oldLength = el.value.length;

        let angka = el.value.replace(/[^0-9]/g, '');
        let formatted = formatRibuan(angka);

        el.value = formatted;

        let newLength = formatted.length;
        cursorPos = cursorPos + (newLength - oldLength);

        el.setSelectionRange(cursorPos, cursorPos);
    }

    // APPLY FORMAT
    document.querySelectorAll("#Rate1, #Depo1").forEach(function(input) {
        input.addEventListener("input", function () {
            handleInput(this);
        });
    });

    // =========================
    // CLICK ROW → FILL FORM
    // =========================
    document.querySelectorAll("#tableKelas tbody tr").forEach(function(row){

        row.addEventListener("click", function(){

            let tds = row.querySelectorAll("td");

            let kode = tds[0].innerText.trim();
            let nama = tds[1].innerText.trim();
            let rate = tds[2].innerText.replace(/\./g,'');
            let depo = tds[3].innerText.replace(/\./g,'');

            document.getElementById("Kode").value = kode;
            document.getElementById("Nama").value = nama;
            document.getElementById("Rate1").value = rate;
            document.getElementById("Depo1").value = depo;

            // mode update
            document.getElementById("formKelas").action = "/kelas/" + kode + "/update";
        });

    });

    // =========================
    // RESET
    // =========================
    document.getElementById("btnReset").addEventListener("click", function(){
        document.getElementById("formKelas").action = "/kelas";
    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    if (document.querySelector('.alert-success')) {
        document.getElementById("formKelas").reset();
        document.getElementById("Kode").focus();
    }

});
</script>

@endsection