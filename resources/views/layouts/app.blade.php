<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $layoutBrandingProfile = \App\Support\HotelBranding::profile();
        $layoutTheme = \App\Support\HotelBranding::themeVariables($layoutBrandingProfile);
    @endphp
    <meta charset="utf-8">
    <title>Quantum Hotel System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        body {
            background: {{ $layoutTheme['page_bg'] }};
            color: {{ $layoutTheme['text'] }};
        }

        .main-header .navbar-nav {
            align-items: center;
        }

        .main-header.navbar {
            background: {{ $layoutTheme['header_bg'] }} !important;
            border-bottom: 1px solid {{ $layoutTheme['shell_border'] }};
            box-shadow: 0 12px 26px rgba(16, 35, 59, 0.08);
        }

        .main-header .nav-link,
        .main-header .navbar-brand,
        .main-header .navbar-brand:hover {
            color: {{ $layoutTheme['title'] }} !important;
        }

        .main-header .nav-link {
            border-radius: 12px;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .main-header .nav-link:hover {
            background: {{ $layoutTheme['badge_bg'] }};
            color: {{ $layoutTheme['badge_text'] }} !important;
        }

        .navbar-brand {
            font-weight: 700;
            white-space: normal;
            line-height: 1.2;
        }

        .navbar-topbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex: 1 1 auto;
            min-width: 0;
            flex-wrap: wrap;
        }

        .navbar-topbar-brand-slot {
            min-width: 0;
            flex: 1 1 260px;
        }

        .navbar-page-tools {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex: 1 1 360px;
            min-width: 0;
        }

        .main-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #101721 0%, #07111c 55%, #05070d 100%) !important;
            border-right: 1px solid rgba(232, 199, 119, 0.28);
            box-shadow: 16px 0 34px rgba(4, 10, 18, 0.28);
        }

        .brand-link.quantum-brand {
            display: flex;
            align-items: center;
            gap: 0.72rem;
            min-height: 72px;
            padding: 1rem 0.95rem;
            border-bottom: 1px solid rgba(232, 199, 119, 0.24);
            color: #fff;
        }

        .brand-link.quantum-brand:hover {
            color: #fff;
        }

        .quantum-brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(232, 199, 119, 0.72);
            border-radius: 8px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.12), rgba(232, 199, 119, 0.08));
            color: #f1d27a;
            font-size: 1.05rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 10px 22px rgba(0, 0, 0, 0.2);
        }

        .quantum-brand-copy {
            min-width: 0;
            line-height: 1.15;
        }

        .quantum-brand-copy .brand-text {
            display: block;
            color: #fff;
            font-weight: 700;
        }

        .quantum-brand-kicker {
            display: block;
            margin-top: 0.22rem;
            color: rgba(232, 199, 119, 0.82);
            font-size: 0.72rem;
            font-weight: 600;
        }

        .sidebar {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 0.9rem 0.72rem 1.2rem;
        }

        .nav-sidebar.quantum-sidebar-menu > .nav-item {
            width: 100%;
        }

        .nav-sidebar.quantum-sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            min-height: 42px;
            padding: 0.68rem 0.78rem;
            border: 1px solid transparent;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.78);
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .nav-sidebar.quantum-sidebar-menu .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(232, 199, 119, 0.22);
            color: #fff;
            transform: translateX(2px);
        }

        .nav-sidebar.quantum-sidebar-menu .nav-link p {
            white-space: normal;
            line-height: 1.25;
            margin: 0;
        }

        .nav-sidebar.quantum-sidebar-menu .nav-icon {
            width: 1.35rem;
            margin-right: 0.62rem;
            color: rgba(232, 199, 119, 0.88);
            text-align: center;
        }

        .nav-sidebar.quantum-sidebar-menu .nav-link.active {
            background: linear-gradient(90deg, rgba(232, 199, 119, 0.24), rgba(255, 255, 255, 0.08));
            border-color: rgba(232, 199, 119, 0.46);
            color: #fff;
            box-shadow: inset 3px 0 0 #f1d27a, 0 10px 22px rgba(0, 0, 0, 0.18);
        }

        .nav-sidebar.quantum-sidebar-menu .nav-link.active .nav-icon {
            color: #f7df9c;
        }

        .quantum-sidebar-root {
            margin-bottom: 0.65rem;
        }

        .quantum-sidebar-root .nav-link {
            font-weight: 700;
        }

        .nav-sidebar.quantum-sidebar-menu .quantum-sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin: 1rem 0.16rem 0.45rem;
            padding: 0.55rem 0.62rem;
            border-left: 2px solid rgba(232, 199, 119, 0.75);
            border-radius: 8px;
            background: linear-gradient(90deg, rgba(232, 199, 119, 0.16), rgba(255, 255, 255, 0.03));
            color: #f1d27a;
            font-size: 0.74rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .sidebar-header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 26px;
            width: 26px;
            height: 26px;
            border: 1px solid rgba(232, 199, 119, 0.42);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.07);
            color: #f7df9c;
        }

        .quantum-sidebar-child {
            position: relative;
            margin-left: 0.88rem;
        }

        .quantum-sidebar-child::before {
            content: "";
            position: absolute;
            top: 0.65rem;
            bottom: 0.65rem;
            left: -0.32rem;
            width: 1px;
            background: rgba(232, 199, 119, 0.22);
        }

        .quantum-sidebar-child .nav-link {
            min-height: 38px;
            padding-left: 0.72rem !important;
            font-size: 0.93rem;
        }

        .quantum-sidebar-child .nav-icon {
            width: 1.12rem !important;
            margin-right: 0.55rem !important;
            font-size: 0.82rem;
        }

        .quantum-sidebar-logout {
            margin-top: 1rem;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .quantum-sidebar-logout .nav-link {
            color: #ffb3b3 !important;
        }

        .quantum-sidebar-logout .nav-icon {
            color: #ffb3b3 !important;
        }

        .content-wrapper {
            background: {{ $layoutTheme['page_bg'] }};
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
            color: {{ $layoutTheme['muted'] }};
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

            .navbar-topbar-content,
            .navbar-page-tools {
                width: 100%;
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
        <div class="navbar-topbar-content">
            <div class="navbar-topbar-brand-slot">
                @if(trim($__env->yieldContent('topbar_brand')) !== '')
                    {!! $__env->yieldContent('topbar_brand') !!}
                @else
                    <span class="navbar-brand">Quantum Hotel System</span>
                @endif
            </div>
            @if(trim($__env->yieldContent('topbar_tools')) !== '')
                <div class="navbar-page-tools">
                    {!! $__env->yieldContent('topbar_tools') !!}
                </div>
            @endif
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <!-- BRAND -->
        <a href="/dashboard" class="brand-link quantum-brand">
            <span class="quantum-brand-mark">
                <i class="fas fa-hotel"></i>
            </span>
            <span class="quantum-brand-copy">
                <span class="brand-text">Quantum Hotel</span>
                <span class="quantum-brand-kicker">Five-Star Operations</span>
            </span>
        </a>

        <!-- MENU -->
        <div class="sidebar">
            <nav>
                <ul class="nav nav-pills nav-sidebar flex-column quantum-sidebar-menu">

                    <li class="nav-item quantum-sidebar-root">
                        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-header quantum-sidebar-header">
                        <span class="sidebar-header-icon"><i class="fas fa-database"></i></span>
                        <span>TABLE</span>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/kelas" class="nav-link {{ request()->is('kelas') || request()->is('kelas/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>Room Class</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/room" class="nav-link {{ request()->is('room') || request()->is('room/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>Room</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/settings/hotel-branding" class="nav-link {{ request()->is('settings/hotel-branding') || request()->is('settings/hotel-branding/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-paint-brush"></i>
                            <p>Hotel Branding</p>
                        </a>
                    </li>

                    <li class="nav-header quantum-sidebar-header">
                        <span class="sidebar-header-icon"><i class="fas fa-concierge-bell"></i></span>
                        <span>TRANSACTION</span>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/checkin" class="nav-link {{ request()->is('checkin') || request()->is('checkin/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sign-in-alt"></i>
                            <p>Check-in</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/checkout" class="nav-link {{ request()->is('checkout') || request()->is('checkout/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Check-out</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/night-audit" class="nav-link {{ request()->is('night-audit') || request()->is('night-audit/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-moon"></i>
                            <p>Night Audit</p>
                        </a>
                    </li>

                    <li class="nav-header quantum-sidebar-header">
                        <span class="sidebar-header-icon"><i class="fas fa-chart-line"></i></span>
                        <span>REPORT</span>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/guest-in-house" class="nav-link {{ request()->is('guest-in-house') || request()->is('guest-in-house/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Guest In House</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-child">
                        <a href="/expected-departure" class="nav-link {{ request()->is('expected-departure') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Expected Departure</p>
                        </a>
                    </li>

                    <li class="nav-item quantum-sidebar-logout">
                        <a href="/logout" class="nav-link text-danger">
                            <i class="nav-icon fas fa-power-off"></i>
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
