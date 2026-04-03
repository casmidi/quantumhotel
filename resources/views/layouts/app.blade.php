<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quantum Hotel System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        :root {
            --quantum-navy: #0f1f36;
            --quantum-navy-soft: #193459;
            --quantum-gold: #c7a56a;
            --quantum-gold-soft: #ead6b2;
            --quantum-ice: #eef3f8;
            --quantum-text: #10233b;
            --quantum-muted: #7b8798;
            --quantum-border: rgba(255, 255, 255, 0.12);
            --quantum-sidebar-glow: rgba(199, 165, 106, 0.2);
        }

        body {
            background: linear-gradient(180deg, #eff4fa 0%, #edf2f7 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--quantum-text);
        }

        .wrapper {
            min-height: 100vh;
        }

        .main-sidebar {
            background:
                radial-gradient(circle at top right, rgba(199, 165, 106, 0.2), transparent 26%),
                radial-gradient(circle at left bottom, rgba(55, 106, 174, 0.15), transparent 24%),
                linear-gradient(180deg, #0c1930 0%, #10233b 48%, #173761 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 20px 0 40px rgba(15, 31, 54, 0.18);
            top: 0;
        }

        .main-sidebar::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0));
        }

        .brand-link {
            border-bottom: 1px solid var(--quantum-border) !important;
            padding: 1.15rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            position: relative;
            z-index: 1;
        }

        .brand-link:hover {
            text-decoration: none;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(199, 165, 106, 0.2), rgba(255, 255, 255, 0.08));
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #f6e8c8;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 10px 24px rgba(0, 0, 0, 0.16);
        }

        .brand-copy {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .brand-copy strong {
            color: #fff;
            font-size: 1rem;
            line-height: 1.1;
            letter-spacing: 0.02em;
        }

        .brand-copy span {
            margin-top: 0.18rem;
            color: rgba(255, 255, 255, 0.62);
            font-size: 0.72rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .sidebar {
            position: relative;
            z-index: 1;
            padding: 1rem 0.8rem 1.2rem;
            height: calc(100vh - 85px);
        }

        .sidebar-panel {
            border-radius: 22px;
            padding: 0.9rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 18px 34px rgba(0, 0, 0, 0.18);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.2rem 0.2rem 1rem;
            margin-bottom: 0.45rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-user-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(199, 165, 106, 0.22));
            color: #f6e8c8;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .sidebar-user-copy {
            display: flex;
            flex-direction: column;
        }

        .sidebar-user-copy strong {
            color: #fff;
            font-size: 0.95rem;
        }

        .sidebar-user-copy span {
            color: rgba(255, 255, 255, 0.62);
            font-size: 0.76rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nav-sidebar > .nav-header {
            color: rgba(255, 255, 255, 0.52);
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-weight: 700;
            padding: 1rem 0.65rem 0.45rem;
        }

        .nav-sidebar .nav-item {
            margin-bottom: 0.35rem;
        }

        .nav-sidebar .nav-link {
            border-radius: 16px;
            color: rgba(255, 255, 255, 0.8);
            padding: 0.82rem 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            transition: all 0.18s ease;
            font-weight: 600;
        }

        .nav-sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(199, 165, 106, 0.25));
            color: #fff;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.18), 0 0 0 1px rgba(255, 255, 255, 0.06) inset;
        }

        .nav-sidebar .nav-link.logout-link {
            color: #ffd7dc;
            background: rgba(166, 48, 62, 0.16);
        }

        .nav-sidebar .nav-link.logout-link:hover {
            background: rgba(166, 48, 62, 0.28);
            color: #fff3f5;
        }

        .nav-sidebar .nav-icon {
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.08);
            color: #f4e7ca;
            flex-shrink: 0;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .nav-sidebar .nav-link.active .nav-icon {
            background: rgba(255, 255, 255, 0.16);
            color: #fff8ee;
        }

        .nav-sidebar .nav-link p {
            margin: 0;
            flex: 1;
            white-space: normal;
            line-height: 1.2;
        }

        .menu-caption {
            display: block;
            margin-top: 0.15rem;
            font-size: 0.73rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.48);
        }

        .content-wrapper {
            background:
                radial-gradient(circle at top right, rgba(199, 165, 106, 0.12), transparent 18%),
                linear-gradient(180deg, #f4f7fb 0%, #edf2f8 100%);
            min-height: 100vh;
            padding: 1.2rem;
            margin-top: 0 !important;
        }

        .content-wrapper > h3 {
            display: none;
        }

        .content-shell {
            min-height: calc(100vh - 2.4rem);
        }

        @media (max-width: 991.98px) {
            .content-wrapper {
                padding: 0.85rem;
            }

            .sidebar-panel {
                border-radius: 18px;
            }

            .sidebar {
                height: auto;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <aside class="main-sidebar elevation-4">
        <a href="/dashboard" class="brand-link">
            <span class="brand-mark">
                <i class="fa-solid fa-crown"></i>
            </span>
            <span class="brand-copy">
                <strong>Quantum Hotel</strong>
                <span>Enterprise Suite</span>
            </span>
        </a>

        <div class="sidebar">
            <div class="sidebar-panel">
                <div class="sidebar-user">
                    <span class="sidebar-user-mark">
                        <i class="fa-solid fa-bell-concierge"></i>
                    </span>
                    <div class="sidebar-user-copy">
                        <strong>Front Office</strong>
                        <span>Premium Control Panel</span>
                    </div>
                </div>

                <nav>
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-chart-line"></i>
                                <p>
                                    Dashboard
                                    <span class="menu-caption">Live room-status overview</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-header">Master Data</li>

                        <li class="nav-item">
                            <a href="/kelas" class="nav-link {{ request()->is('kelas') || request()->is('kelas/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-layer-group"></i>
                                <p>
                                    Room Class
                                    <span class="menu-caption">Category and rate setup</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/room" class="nav-link {{ request()->is('room') || request()->is('room/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-bed"></i>
                                <p>
                                    Room
                                    <span class="menu-caption">Inventory and room profile</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/stock-package" class="nav-link {{ request()->is('stock-package') || request()->is('stock-package/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-box-open"></i>
                                <p>
                                    Stock Package
                                    <span class="menu-caption">Package items and auto builder</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-header">Transactions</li>

                        <li class="nav-item">
                            <a href="/checkin" class="nav-link {{ request()->is('checkin') || request()->is('checkin/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-door-open"></i>
                                <p>
                                    Check-in
                                    <span class="menu-caption">Guest arrival and registration</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/checkout" class="nav-link {{ request()->is('checkout') || request()->is('checkout/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-door-closed"></i>
                                <p>
                                    Check-out
                                    <span class="menu-caption">Departure and settlement</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-header">Reports</li>

                        <li class="nav-item">
                            <a href="/guest-in-house" class="nav-link {{ request()->is('guest-in-house') || request()->is('guest-in-house/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-users"></i>
                                <p>
                                    Guest In House
                                    <span class="menu-caption">Current staying guest list</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/expected-departure" class="nav-link {{ request()->is('expected-departure') || request()->is('expected-departure/*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-calendar-check"></i>
                                <p>
                                    Expected Departure
                                    <span class="menu-caption">Planned departures overview</span>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item mt-3">
                            <a href="/logout" class="nav-link logout-link {{ request()->is('logout') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-right-from-bracket"></i>
                                <p>
                                    Logout
                                    <span class="menu-caption">Securely exit the session</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="content-shell">
            <h3>@yield('title')</h3>
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>


