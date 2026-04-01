@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="row">

    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>Room Class</h4>
                <p>Manage room class data</p>
            </div>
            <a href="/kelas" class="small-box-footer">Go <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

</div>

@endsection