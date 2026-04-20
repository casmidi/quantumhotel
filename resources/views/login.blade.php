<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Quantum Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">

    <style>
        :root {
            --bg: #edf5f2;
            --panel: rgba(255, 255, 255, 0.92);
            --panel-border: rgba(20, 35, 45, 0.10);
            --text: #14312b;
            --muted: #5f736c;
            --brand: #0f766e;
            --brand-deep: #115e59;
            --accent: #d97745;
            --soft: #dcebe5;
            --danger: #b94040;
            --shadow: 0 24px 60px rgba(20, 49, 43, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(217, 119, 69, 0.16), transparent 28%),
                radial-gradient(circle at bottom right, rgba(15, 118, 110, 0.16), transparent 34%),
                linear-gradient(180deg, #f6fbf8 0%, var(--bg) 100%);
            color: var(--text);
        }

        .login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }

        .login-shell {
            width: min(1080px, 100%);
            min-height: 680px;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(360px, 460px);
            background: rgba(255, 255, 255, 0.52);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .login-story {
            position: relative;
            padding: 34px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(160deg, rgba(15, 118, 110, 0.14), rgba(217, 119, 69, 0.10)),
                rgba(255, 255, 255, 0.34);
        }

        .login-story::after {
            content: "";
            position: absolute;
            inset: 20px;
            border: 1px solid rgba(20, 49, 43, 0.08);
            border-radius: 8px;
            pointer-events: none;
        }

        .story-top,
        .story-bottom {
            position: relative;
            z-index: 1;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            color: var(--text);
            text-decoration: none;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(180deg, var(--brand) 0%, var(--brand-deep) 100%);
            box-shadow: 0 12px 24px rgba(17, 94, 89, 0.25);
            font-size: 1.2rem;
        }

        .brand-copy {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-kicker {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--brand);
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.12;
        }

        .story-headline {
            max-width: 520px;
            margin: 0 0 14px;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1.02;
            font-weight: 800;
        }

        .story-text {
            max-width: 520px;
            margin: 0;
            font-size: 1.02rem;
            line-height: 1.75;
            color: var(--muted);
        }

        .story-visual {
            margin: 34px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .story-visual img {
            display: block;
            width: min(100%, 560px);
            height: auto;
            max-height: 320px;
            filter: drop-shadow(0 20px 30px rgba(20, 49, 43, 0.16));
        }

        .story-points {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .story-point {
            padding: 14px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(20, 49, 43, 0.08);
        }

        .story-point strong {
            display: block;
            margin-bottom: 6px;
            font-size: 0.96rem;
        }

        .story-point span {
            display: block;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .login-panel {
            padding: 34px;
            background: var(--panel);
            border-left: 1px solid var(--panel-border);
            display: flex;
            align-items: center;
        }

        .panel-inner {
            width: 100%;
            max-width: 100%;
        }

        .panel-kicker {
            margin: 0 0 12px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--brand);
        }

        .panel-title {
            margin: 0;
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .panel-subtitle {
            margin: 12px 0 28px;
            color: var(--muted);
            line-height: 1.7;
        }

        .flash {
            margin-bottom: 18px;
            padding: 13px 14px;
            border-radius: 8px;
            font-size: 0.94rem;
            line-height: 1.6;
        }

        .flash-success {
            background: rgba(15, 118, 110, 0.10);
            border: 1px solid rgba(15, 118, 110, 0.18);
            color: var(--brand-deep);
        }

        .flash-error {
            background: rgba(185, 64, 64, 0.08);
            border: 1px solid rgba(185, 64, 64, 0.16);
            color: var(--danger);
        }

        .login-form {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            font-size: 0.92rem;
            font-weight: 600;
        }

        .field-shell {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) 48px;
            align-items: center;
            border: 1px solid rgba(20, 49, 43, 0.12);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .field-shell:focus-within {
            border-color: rgba(15, 118, 110, 0.5);
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.10);
        }

        .field-icon,
        .field-action {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: var(--muted);
        }

        .field-action {
            border: 0;
            background: rgba(220, 235, 229, 0.6);
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .field-action:hover {
            background: rgba(15, 118, 110, 0.12);
            color: var(--brand-deep);
        }

        .field input {
            width: 100%;
            min-width: 0;
            height: 48px;
            border: 0;
            outline: 0;
            padding: 0 14px 0 0;
            font: inherit;
            color: var(--text);
            background: transparent;
        }

        .field input::placeholder {
            color: #91a39d;
        }

        .field input[type="password"]::-ms-reveal,
        .field input[type="password"]::-ms-clear {
            display: none;
        }

        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            background: rgba(220, 235, 229, 0.72);
            color: var(--brand-deep);
            font-weight: 600;
        }

        .submit-btn {
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--brand) 0%, #1b8a81 55%, var(--accent) 100%);
            color: #fff;
            font: inherit;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 16px 28px rgba(15, 118, 110, 0.24);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 30px rgba(15, 118, 110, 0.28);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .panel-footer {
            margin-top: 18px;
            text-align: center;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.65;
        }

        .panel-footer strong {
            color: var(--text);
        }

        @media (max-width: 991.98px) {
            .login-shell {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .login-panel {
                order: 1;
            }

            .login-story {
                order: 2;
                padding-bottom: 28px;
            }

            .story-points {
                grid-template-columns: 1fr;
            }

            .login-panel {
                border-left: 0;
                border-top: 1px solid var(--panel-border);
            }

            .story-headline,
            .story-text {
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .login-page {
                padding: 0;
            }

            .login-shell {
                border-radius: 0;
                box-shadow: none;
                min-height: 100vh;
            }

            .login-story,
            .login-panel {
                padding: 24px 18px;
            }

            .story-headline {
                font-size: 2rem;
            }

            .panel-title {
                font-size: 1.7rem;
            }

            .story-visual {
                margin: 22px 0;
            }

            .story-visual img {
                max-height: 240px;
            }

            .form-meta {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 479.98px) {
            .brand {
                gap: 10px;
                margin-bottom: 20px;
            }

            .brand-mark {
                width: 42px;
                height: 42px;
            }

            .brand-title {
                font-size: 1.18rem;
            }

            .story-headline {
                font-size: 1.7rem;
            }

            .story-text,
            .panel-subtitle,
            .panel-footer {
                font-size: 0.9rem;
            }

            .story-point {
                padding: 12px;
            }

            .field-shell {
                grid-template-columns: 42px minmax(0, 1fr) 42px;
            }

            .field-icon,
            .field-action {
                width: 42px;
                height: 46px;
            }

            .field input {
                height: 46px;
                font-size: 0.95rem;
            }

            .submit-btn {
                min-height: 48px;
            }
        }
    </style>
</head>
<body>
<main class="login-page">
    <section class="login-shell" aria-label="Quantum Hotel login">
        <div class="login-story">
            <div class="story-top">
                <a href="/" class="brand" aria-label="Quantum Hotel">
                    <span class="brand-mark">
                        <i class="fas fa-hotel"></i>
                    </span>
                    <span class="brand-copy">
                        <span class="brand-kicker">Quantum Hotel</span>
                        <span class="brand-title">Front Office System</span>
                    </span>
                </a>

                <h1 class="story-headline">Masuk cepat. Kerja rapi. Shift tetap tenang.</h1>
                <p class="story-text">
                    Akses transaksi harian, status kamar, dan pergerakan tamu dari satu pintu yang lebih jelas
                    sejak layar pertama.
                </p>
            </div>

            <div class="story-visual">
                <img src="{{ asset('images/login-portal-illustration.svg') }}" alt="Ilustrasi lobi Quantum Hotel">
            </div>

            <div class="story-bottom">
                <div class="story-points">
                    <div class="story-point">
                        <strong>Check-in lebih cepat</strong>
                        <span>Data utama, room, dan paket bisa masuk dengan alur yang tetap enak dibaca.</span>
                    </div>
                    <div class="story-point">
                        <strong>Pantau kamar aktif</strong>
                        <span>Status room dan transaksi harian tetap dekat dengan operasional front office.</span>
                    </div>
                    <div class="story-point">
                        <strong>Siap untuk integrasi</strong>
                        <span>Web dan API sudah tumbuh di jalur yang sama supaya pengembangan berikutnya lebih rapi.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-panel">
            <div class="panel-inner">
                <p class="panel-kicker">Selamat datang kembali</p>
                <h2 class="panel-title">Masuk ke Quantum Hotel</h2>
                <p class="panel-subtitle">
                    Gunakan akun operasional untuk melanjutkan transaksi, kontrol kamar, dan layanan tamu hari ini.
                </p>

                @if(session('success'))
                    <div class="flash flash-success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="flash flash-error" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form class="login-form" method="POST" action="/login">
                    @csrf

                    <div class="field">
                        <label for="username">Username</label>
                        <div class="field-shell">
                            <span class="field-icon" aria-hidden="true">
                                <i class="fas fa-user"></i>
                            </span>
                            <input
                                id="username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Masukkan username"
                                autocomplete="username"
                                required
                            >
                            <span class="field-icon" aria-hidden="true">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="field-shell">
                            <span class="field-icon" aria-hidden="true">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >
                            <button class="field-action" type="button" id="toggle-password" aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-meta">
                        <span class="meta-badge">
                            <i class="fas fa-shield-alt" aria-hidden="true"></i>
                            Akses internal operasional
                        </span>
                        <span>{{ now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <button class="submit-btn" type="submit">Masuk ke Dashboard</button>
                </form>

                <div class="panel-footer">
                    <strong>Quantum Hotel System</strong><br>
                    Front office, kamar, dan transaksi harian dalam satu alur kerja.
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    (function () {
        var passwordInput = document.getElementById('password');
        var toggleButton = document.getElementById('toggle-password');

        if (!passwordInput || !toggleButton) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            var icon = toggleButton.querySelector('i');
            var showing = passwordInput.type === 'text';

            passwordInput.type = showing ? 'password' : 'text';
            toggleButton.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');

            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    }());
</script>
</body>
</html>
