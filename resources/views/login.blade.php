<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login - Quantum Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --navy-950: #08131f;
            --navy-900: #10233b;
            --navy-800: #173761;
            --gold-500: #b38a51;
            --gold-300: #e5c98f;
            --ivory: #f7f1e7;
            --mist: rgba(255, 255, 255, 0.14);
            --text-soft: rgba(255, 255, 255, 0.72);
            --panel: rgba(255, 255, 255, 0.88);
            --panel-border: rgba(255, 255, 255, 0.24);
            --shadow: 0 30px 80px rgba(8, 19, 31, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #10233b;
            background:
                radial-gradient(circle at top left, rgba(229, 201, 143, 0.18), transparent 24%),
                radial-gradient(circle at top right, rgba(86, 120, 167, 0.18), transparent 28%),
                linear-gradient(135deg, #08131f 0%, #10233b 48%, #173761 100%);
        }

        .login-scene {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-scene::before,
        .login-scene::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(10px);
            pointer-events: none;
        }

        .login-scene::before {
            width: 460px;
            height: 460px;
            background: radial-gradient(circle, rgba(229, 201, 143, 0.2), rgba(229, 201, 143, 0));
            top: -120px;
            left: -80px;
        }

        .login-scene::after {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(53, 101, 156, 0.22), rgba(53, 101, 156, 0));
            bottom: -180px;
            right: -100px;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(1180px, 100%);
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: var(--shadow);
            border-radius: 32px;
            overflow: hidden;
            backdrop-filter: blur(22px);
        }

        .login-brand {
            position: relative;
            padding: 3.25rem;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(8, 19, 31, 0.28), rgba(8, 19, 31, 0.78)),
                linear-gradient(135deg, rgba(16, 35, 59, 0.75), rgba(23, 55, 97, 0.42)),
                radial-gradient(circle at top left, rgba(229, 201, 143, 0.2), rgba(229, 201, 143, 0));
        }

        .login-brand::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(229, 201, 143, 0.12), transparent 38%),
                linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.03));
            pointer-events: none;
        }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 0.8rem;
            letter-spacing: 0.16em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .brand-title {
            margin: 1.6rem 0 1rem;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.7rem, 4vw, 4.4rem);
            line-height: 0.98;
            letter-spacing: 0.02em;
        }

        .brand-copy {
            margin: 0;
            max-width: 520px;
            color: var(--text-soft);
            font-size: 1rem;
            line-height: 1.8;
        }

        .brand-points {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .brand-point {
            padding: 1rem 1rem 1.15rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-point strong {
            display: block;
            font-size: 1.05rem;
            color: #fff;
            margin-bottom: 0.3rem;
        }

        .brand-point span {
            color: var(--text-soft);
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .login-panel {
            padding: 3rem;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(247, 241, 231, 0.92));
        }

        .panel-card {
            background: var(--panel);
            border: 1px solid rgba(16, 35, 59, 0.08);
            border-radius: 28px;
            padding: 2rem;
            box-shadow: 0 18px 45px rgba(16, 35, 59, 0.1);
        }

        .panel-kicker {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--gold-500);
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .panel-title {
            margin: 0.75rem 0 0.45rem;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            line-height: 1.05;
            color: var(--navy-900);
        }

        .panel-copy {
            margin: 0 0 1.6rem;
            color: #66768d;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .login-alert {
            margin-bottom: 1.2rem;
            padding: 0.95rem 1rem;
            border-radius: 16px;
            background: rgba(173, 52, 52, 0.08);
            border: 1px solid rgba(173, 52, 52, 0.14);
            color: #a12d2d;
            font-weight: 600;
        }

        .field-group+.field-group {
            margin-top: 1rem;
        }

        .field-label {
            display: block;
            margin-bottom: 0.55rem;
            font-size: 0.82rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #5b6c82;
        }

        .field-input {
            width: 100%;
            height: 58px;
            border-radius: 18px;
            border: 1px solid rgba(16, 35, 59, 0.12);
            background: rgba(255, 255, 255, 0.94);
            padding: 0 1.15rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--navy-900);
            transition: all 0.2s ease;
        }

        .field-input::placeholder {
            color: #a1acba;
            font-weight: 500;
        }

        .field-input:focus {
            outline: none;
            border-color: rgba(179, 138, 81, 0.75);
            box-shadow: 0 0 0 0.22rem rgba(179, 138, 81, 0.12);
            transform: translateY(-1px);
        }

        .login-actions {
            margin-top: 1.5rem;
        }

        .login-button {
            width: 100%;
            height: 58px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #10233b 0%, #173761 52%, #b38a51 135%);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            box-shadow: 0 18px 35px rgba(16, 35, 59, 0.18);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 38px rgba(16, 35, 59, 0.22);
        }

        .panel-footer {
            margin-top: 1.35rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            color: #748396;
            font-size: 0.88rem;
        }

        .panel-footer span strong {
            color: var(--navy-900);
        }

        @media (max-width: 1024px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-brand,
            .login-panel {
                padding: 2rem;
            }
        }

        @media (max-width: 640px) {
            .login-scene {
                padding: 1rem;
            }

            .login-brand,
            .login-panel,
            .panel-card {
                padding: 1.5rem;
            }

            .brand-points {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="login-scene">
        <section class="login-shell">
            <div class="login-brand">
                <div class="brand-kicker">Quantum Hotel System</div>
                <h1 class="brand-title">Luxury Operations. Refined Control.</h1>
                <p class="brand-copy">
                    A premium hospitality platform crafted for five-star service flow, elegant front-office operations,
                    and high-precision control across your hotel business.
                </p>

                <div class="brand-points">
                    <div class="brand-point">
                        <strong>Front Desk Ready</strong>
                        <span>Fast, polished workflows for room-class, room, and reservation management.</span>
                    </div>
                    <div class="brand-point">
                        <strong>Executive Feel</strong>
                        <span>Luxury-inspired interface language designed to reflect premium hospitality
                            standards.</span>
                    </div>
                    <div class="brand-point">
                        <strong>Always Connected</strong>
                        <span>Accessible online through your public domain for smooth operations across
                            locations.</span>
                    </div>
                </div>
            </div>

            <div class="login-panel">
                <div class="panel-card">
                    <div class="panel-kicker">Secure Access</div>
                    <h2 class="panel-title">Welcome Back</h2>
                    <p class="panel-copy">Sign in to continue into the Quantum Hotel control center.</p>

                    @if (session('error'))
                        <div class="login-alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}">
                        @csrf

                        <div class="field-group">
                            <label class="field-label" for="username">User Code</label>
                            <input type="text" name="username" id="username" class="field-input"
                                placeholder="Enter your user code" autocomplete="username" required autofocus>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="password">Password</label>
                            <input type="password" name="password" id="password" class="field-input"
                                placeholder="Enter your password" autocomplete="current-password" required>
                        </div>

                        <div class="login-actions">
                            <button type="submit" class="login-button">Enter Quantum Hotel</button>
                        </div>
                    </form>

                    <div class="panel-footer">
                        <span><strong>Property Grade:</strong> Premium Hospitality</span>
                        <span><strong>Platform:</strong> Quantum Hotel System</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>
