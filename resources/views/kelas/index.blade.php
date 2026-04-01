@extends('layouts.app')

@section('title', 'Room Class')

@section('content')

<div class="row">

    <!-- FORM -->
    <div class="col-md-12">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">Add Room Class</h3>
            </div>

            <form method="POST" action="/kelas">
                @csrf

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label>Kode</label>
                            <input type="text" name="Kode" class="form-control" required>
                        </div>

                        <div class="col-md-5">
                            <label>Nama</label>
                            <input type="text" name="Nama" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Rate</label>
                            <input type="number" name="Rate1" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label>Deposit</label>
                            <input type="number" name="Depo1" class="form-control">
                        </div>

                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>

            </form>

        </div>
    </div>

    <!-- NOTIF -->
    @if(session('success'))
    <div class="col-md-12">
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    </div>
    @endif

    <!-- TABLE -->
    <div class="col-md-12">
        <div class="card mt-3">

            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">Room Class List</h3>

                <form method="GET" action="/kelas">
                    <input type="text" name="q" placeholder="Search..."
                        class="form-control form-control-sm"
                        value="{{ request('q') }}">
                </form>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Rate</th>
                            <th>Deposit</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($kelas as $k)
                        <tr>
                            <td><b>{{ $k->Kode }}</b></td>
                            <td>{{ $k->Nama }}</td>
                            <td>{{ number_format($k->Rate1,0,',','.') }}</td>
                            <td>{{ number_format($k->Depo1,0,',','.') }}</td>
                            <td>
                                <a href="/kelas/{{ $k->Kode }}/delete"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete data?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No data</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

@endsection