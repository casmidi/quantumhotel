<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quantum Hotel System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        body {
            background: #edf2f7;
            color: #10233b;
        }

        .main-header .navbar-nav {
            align-items: center;
        }

        .navbar-brand {
            font-weight: 700;
            white-space: normal;
            line-height: 1.2;
        }

        .main-sidebar {
            min-height: 100vh;
        }

        .sidebar {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nav-sidebar .nav-link p {
            white-space: normal;
            line-height: 1.25;
        }

        .nav-sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
        }

        .content-wrapper {
            min-height: calc(100vh - 57px);
            overflow-x: hidden;
            max-width: 100%;
        }

        .content-shell {
            width: 100%;
            max-width: 100%;
        }

        .table-responsive,
        .package-table-wrap,
        .package-grid-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive {
            width: 100%;
            max-width: 100%;
        }

        .responsive-table-shell {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            border-radius: 8px;
        }

        .responsive-table-shell table {
            margin-bottom: 0;
        }

        .responsive-table-shell table:not(.table-borderless) {
            min-width: max(100%, 760px);
        }

        .responsive-table-shell.is-compact table:not(.table-borderless) {
            min-width: max(100%, 640px);
        }

        .responsive-table-shell.is-wide table:not(.table-borderless) {
            min-width: max(100%, 960px);
        }

        .content-shell table th,
        .content-shell table td {
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .content-shell table td .btn,
        .content-shell table td .badge,
        .content-shell table td a.btn {
            white-space: nowrap;
        }

        .table-responsive-hint {
            display: none;
            align-items: center;
            justify-content: flex-end;
            gap: 0.45rem;
            margin-bottom: 0.55rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #5b6f88;
        }

        img,
        table,
        input,
        select,
        textarea,
        button {
            max-width: 100%;
        }

        @media (max-width: 991.98px) {
            body:not(.sidebar-open) .main-sidebar {
                margin-left: -250px;
            }

            body.sidebar-open .main-sidebar {
                margin-left: 0;
            }

            .main-header,
            .content-wrapper {
                margin-left: 0 !important;
            }

            .content-wrapper {
                padding: 0.85rem !important;
            }

            .content-wrapper > h3 {
                font-size: 1.25rem;
                margin-bottom: 0.85rem;
            }
        }

        @media (max-width: 767.98px) {
            html,
            body {
                max-width: 100%;
                overflow-x: hidden;
            }

            .main-header {
                min-height: 56px;
            }

            .navbar-brand {
                font-size: 0.98rem;
            }

            .content-wrapper {
                padding: 0.65rem !important;
            }

            .content-shell {
                min-width: 0;
            }

            .card,
            .modal-content {
                border-radius: 8px;
            }

            .btn {
                min-height: 42px;
            }

            .responsive-table-shell {
                margin: 0 -0.15rem;
                padding-bottom: 0.2rem;
            }

            .content-shell table th,
            .content-shell table td {
                padding-top: 0.7rem;
                padding-bottom: 0.7rem;
                font-size: 0.86rem;
                vertical-align: middle;
            }

            .content-shell .table thead th {
                font-size: 0.72rem;
                white-space: nowrap;
            }

            .table-responsive-hint.is-visible {
                display: flex;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
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
                        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header">TABLE</li>

                    <li class="nav-item">
                        <a href="/kelas" class="nav-link {{ request()->is('kelas') || request()->is('kelas/*') ? 'active' : '' }}">
                            <p>Room Class</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/room" class="nav-link {{ request()->is('room') || request()->is('room/*') ? 'active' : '' }}">
                            <p>Room</p>
                        </a>
                    </li>

                    <li class="nav-header">TRANSACTION</li>

                    <li class="nav-item">
                        <a href="/checkin" class="nav-link {{ request()->is('checkin') || request()->is('checkin/*') ? 'active' : '' }}">
                            <p>Check-in</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/checkout" class="nav-link {{ request()->is('checkout') || request()->is('checkout/*') ? 'active' : '' }}">
                            <p>Check-out</p>
                        </a>
                    </li>

                    <li class="nav-header">REPORT</li>

                    <li class="nav-item">
                        <a href="/guest-in-house" class="nav-link {{ request()->is('guest-in-house') ? 'active' : '' }}">
                            <p>Guest In House</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/expected-departure" class="nav-link {{ request()->is('expected-departure') ? 'active' : '' }}">
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
        <div class="content-shell">

        <!-- TITLE -->
        @if(trim($__env->yieldContent('title')) !== '')
            <h3>@yield('title')</h3>
        @endif

        <!-- CONTENT -->
        @yield('content')

        </div>
    </div>

</div>

<!-- REQUIRED SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    (function () {
        var tables = document.querySelectorAll('.content-shell table');

        tables.forEach(function (table) {
            if (table.closest('.responsive-table-shell')) {
                return;
            }

            var existingWrapper = table.parentElement && table.parentElement.classList.contains('table-responsive')
                ? table.parentElement
                : null;
            var shell = existingWrapper || document.createElement('div');

            if (!existingWrapper) {
                shell.className = 'responsive-table-shell';
            } else {
                shell.classList.add('responsive-table-shell');
            }

            if (table.classList.contains('package-table') || table.classList.contains('room-table')) {
                shell.classList.add('is-wide');
            }

            if (table.classList.contains('kelas-table') || table.classList.contains('crud-table')) {
                shell.classList.add('is-compact');
            }

            var hint = document.createElement('div');
            hint.className = 'table-responsive-hint';
            hint.innerHTML = '<i class="fas fa-arrows-left-right"></i><span>Geser tabel untuk melihat kolom lainnya</span>';

            if (!existingWrapper) {
                table.parentNode.insertBefore(shell, table);
                shell.appendChild(table);
            }

            shell.parentNode.insertBefore(hint, shell);

            var syncHint = function () {
                if (window.innerWidth > 767.98) {
                    hint.classList.remove('is-visible');
                    return;
                }

                hint.classList.toggle('is-visible', shell.scrollWidth > shell.clientWidth + 4);
            };

            syncHint();
            window.addEventListener('resize', syncHint);
        });
    }());
</script>

</body>
</html>
