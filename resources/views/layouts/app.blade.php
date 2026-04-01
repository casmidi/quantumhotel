<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quantum Hotel System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <span class="navbar-brand">Quantum Hotel System</span>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <!-- BRAND -->
        <a href="/dashboard" class="brand-link">
            <span class="brand-text font-weight-light">Quantum Hotel</span>
        </a>

        <!-- MENU -->
        <div class="sidebar">
            <nav>
                <ul class="nav nav-pills nav-sidebar flex-column">

                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link">
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header">TABLE</li>

                    <li class="nav-item">
                        <a href="/kelas" class="nav-link">
                            <p>Room Class</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/room" class="nav-link">
                            <p>Room</p>
                        </a>
                    </li>

                    <li class="nav-header">TRANSACTION</li>

                    <li class="nav-item">
                        <a href="/checkin" class="nav-link">
                            <p>Check-in</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/checkout" class="nav-link">
                            <p>Check-out</p>
                        </a>
                    </li>

                    <li class="nav-header">REPORT</li>

                    <li class="nav-item">
                        <a href="/guest-in-house" class="nav-link">
                            <p>Guest In House</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/expected-departure" class="nav-link">
                            <p>Expected Departure</p>
                        </a>
                    </li>

                    <li class="nav-item mt-3">
                        <a href="/logout" class="nav-link text-danger">
                            <p>Logout</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>

    </aside>

    <!-- CONTENT -->
    <div class="content-wrapper p-3">

        <!-- TITLE -->
        <h3>@yield('title')</h3>

        <!-- CONTENT -->
        @yield('content')

    </div>

</div>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>