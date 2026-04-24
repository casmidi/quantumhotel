<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Quantum Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">

    <style>
        :root {
            --page-deep: #05141c;
            --page-mid: #0a2730;
            --panel: rgba(252, 250, 244, 0.84);
            --panel-border: rgba(255, 255, 255, 0.48);
            --shell-border: rgba(255, 255, 255, 0.22);
            --text: #102a2d;
            --muted: #5c7378;
            --story-text: rgba(226, 245, 245, 0.8);
            --brand: #2ce5c8;
            --brand-deep: #0d7d74;
            --brand-bright: #8afff2;
            --accent: #ff9c31;
            --accent-soft: #ffd37f;
            --gold: #c89a44;
            --gold-soft: #f5e2ae;
            --danger: #c14b4b;
            --shadow: 0 34px 90px rgba(3, 20, 27, 0.28), 0 18px 44px rgba(8, 82, 84, 0.18);
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
            position: relative;
            font-family: "Manrope", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 18% 14%, rgba(57, 233, 211, 0.84) 0%, rgba(57, 233, 211, 0.34) 18%, transparent 40%),
                radial-gradient(circle at 84% 10%, rgba(255, 193, 92, 0.84) 0%, rgba(255, 193, 92, 0.32) 18%, transparent 40%),
                radial-gradient(circle at 82% 86%, rgba(255, 148, 60, 0.56) 0%, transparent 28%),
                radial-gradient(circle at 16% 82%, rgba(58, 246, 225, 0.54) 0%, transparent 30%),
                linear-gradient(135deg, #dff8f7 0%, #eff8f6 36%, #fff4e4 100%);
            color: var(--text);
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 44vw;
            height: 44vw;
            border-radius: 50%;
            filter: blur(72px);
            opacity: 0.62;
            pointer-events: none;
            z-index: 0;
            animation: drift-glow 16s ease-in-out infinite;
        }

        body::before {
            top: 4vh;
            left: -10vw;
            background: rgba(31, 215, 195, 0.58);
        }

        body::after {
            top: -4vh;
            right: -12vw;
            background: rgba(255, 180, 74, 0.44);
            animation-delay: -8s;
        }

        .login-page {
            position: relative;
            isolation: isolate;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
        }

        .login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 96px 96px;
            opacity: 0.14;
            pointer-events: none;
        }

        .login-shell {
            position: relative;
            isolation: isolate;
            width: min(1080px, 100%);
            min-height: 700px;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(360px, 0.92fr);
            background: rgba(6, 26, 34, 0.26);
            border: 1px solid var(--shell-border);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow);
            backdrop-filter: blur(26px);
        }

        .login-shell::before {
            content: "";
            position: absolute;
            inset: 1px 1px auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.76), transparent);
            opacity: 0.9;
            z-index: 1;
        }

        .shell-aura {
            position: absolute;
            pointer-events: none;
            z-index: 1;
            mix-blend-mode: screen;
        }

        .shell-aura-primary {
            left: -14%;
            right: 18%;
            bottom: 19%;
            height: 260px;
            background:
                radial-gradient(circle at 18% 62%, rgba(141, 255, 240, 0.95) 0 4px, transparent 18px),
                radial-gradient(circle at 48% 48%, rgba(255, 255, 255, 0.94) 0 6px, transparent 22px),
                linear-gradient(180deg,
                    transparent 0%,
                    transparent 42%,
                    rgba(69, 255, 226, 0.82) 48%,
                    rgba(255, 255, 255, 0.98) 51%,
                    rgba(255, 181, 73, 0.86) 55%,
                    transparent 62%,
                    transparent 100%);
            filter: blur(0.6px) drop-shadow(0 0 22px rgba(68, 255, 226, 0.72)) drop-shadow(0 0 42px rgba(255, 181, 73, 0.34));
            transform: rotate(-14deg);
            opacity: 0.88;
            animation: aura-sweep-primary 14s ease-in-out infinite;
        }

        .shell-aura-secondary {
            left: 46%;
            right: -16%;
            bottom: 4%;
            height: 220px;
            background:
                radial-gradient(circle at 26% 46%, rgba(255, 255, 255, 0.9) 0 5px, transparent 20px),
                linear-gradient(180deg,
                    transparent 0%,
                    transparent 44%,
                    rgba(255, 232, 194, 0.18) 48%,
                    rgba(255, 255, 255, 0.84) 50%,
                    rgba(255, 181, 73, 0.78) 54%,
                    transparent 60%,
                    transparent 100%);
            filter: blur(0.5px) drop-shadow(0 0 18px rgba(255, 201, 117, 0.48));
            transform: rotate(10deg);
            opacity: 0.66;
            animation: aura-sweep-secondary 16s ease-in-out infinite;
        }

        .login-story {
            position: relative;
            z-index: 2;
            min-width: 0;
            padding: 40px 38px 34px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                radial-gradient(circle at 18% 18%, rgba(61, 255, 225, 0.24), transparent 30%),
                radial-gradient(circle at 78% 10%, rgba(255, 161, 53, 0.24), transparent 28%),
                linear-gradient(180deg, rgba(9, 48, 56, 0.94) 0%, rgba(8, 31, 39, 0.94) 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }

        .login-story::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(180, 250, 244, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(180, 250, 244, 0.08) 1px, transparent 1px);
            background-size: 52px 52px;
            opacity: 0.46;
            pointer-events: none;
        }

        .login-story::after {
            content: "";
            position: absolute;
            inset: 16px;
            border: 1px solid rgba(179, 251, 242, 0.14);
            border-radius: 28px;
            pointer-events: none;
        }

        .story-top,
        .story-visual,
        .story-bottom,
        .panel-inner {
            position: relative;
            z-index: 2;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
            color: #f4fbfb;
            text-decoration: none;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.04)),
                linear-gradient(145deg, #104a54 0%, #0b2028 100%);
            border: 1px solid rgba(173, 255, 243, 0.24);
            box-shadow:
                0 18px 32px rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.18),
                0 0 24px rgba(47, 228, 205, 0.14);
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
            color: rgba(154, 255, 239, 0.84);
        }

        .brand-title {
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 1.52rem;
            font-weight: 700;
            line-height: 1.08;
            letter-spacing: -0.04em;
            text-shadow: 0 10px 26px rgba(0, 0, 0, 0.22);
        }

        .story-headline {
            max-width: 520px;
            margin: 0 0 14px;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 0.98;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: #f4fbfc;
            text-shadow: 0 16px 32px rgba(0, 0, 0, 0.24);
        }

        .story-text {
            max-width: 500px;
            margin: 0;
            font-size: 1rem;
            line-height: 1.78;
            color: var(--story-text);
        }

        .story-visual {
            min-height: 320px;
            margin: 24px 0 28px;
            display: grid;
            place-items: center;
        }

        .visual-pulse,
        .visual-ring,
        .visual-node {
            position: absolute;
            pointer-events: none;
        }

        .visual-pulse {
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 248, 215, 0.96) 0%, rgba(255, 192, 90, 0.28) 18%, rgba(61, 255, 226, 0.24) 36%, transparent 68%);
            filter: blur(8px);
            animation: pulse-core 8s ease-in-out infinite;
        }

        .visual-ring {
            border-radius: 999px;
            mix-blend-mode: screen;
        }

        .visual-ring-a {
            width: min(122%, 680px);
            height: 220px;
            border: 2px solid rgba(108, 255, 238, 0.58);
            box-shadow: 0 0 26px rgba(77, 255, 226, 0.58), inset 0 0 18px rgba(255, 255, 255, 0.18);
            transform: translate(-12%, 20%) rotate(-15deg);
            animation: ring-a-float 12s ease-in-out infinite;
        }

        .visual-ring-b {
            width: min(126%, 700px);
            height: 200px;
            border: 2px solid rgba(255, 178, 75, 0.54);
            box-shadow: 0 0 28px rgba(255, 178, 75, 0.5), inset 0 0 18px rgba(255, 255, 255, 0.18);
            transform: translate(12%, -4%) rotate(16deg);
            animation: ring-b-float 11s ease-in-out infinite;
        }

        .visual-ring-c {
            width: min(96%, 520px);
            height: 128px;
            border: 1px solid rgba(255, 255, 255, 0.44);
            box-shadow: 0 0 18px rgba(255, 255, 255, 0.24);
            transform: translate(-1%, 18%) rotate(8deg);
            opacity: 0.72;
        }

        .visual-node {
            width: 18px;
            height: 18px;
            border-radius: 50%;
        }

        .visual-node-left {
            left: 12%;
            bottom: 26%;
            background: radial-gradient(circle, #ffffff 0%, #8effef 42%, rgba(142, 255, 239, 0.08) 78%, transparent 100%);
            box-shadow: 0 0 22px rgba(142, 255, 239, 0.76);
        }

        .visual-node-right {
            right: 12%;
            bottom: 27%;
            background: radial-gradient(circle, #ffffff 0%, #ffc975 42%, rgba(255, 201, 117, 0.08) 78%, transparent 100%);
            box-shadow: 0 0 20px rgba(255, 185, 91, 0.74);
        }

        .story-visual img {
            position: relative;
            z-index: 2;
            display: block;
            width: min(72%, 360px);
            height: auto;
            max-height: 340px;
            filter:
                drop-shadow(0 0 18px rgba(255, 255, 255, 0.46))
                drop-shadow(0 22px 42px rgba(0, 0, 0, 0.34))
                saturate(1.08);
        }

        .story-points {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .story-point {
            padding: 16px 15px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(252, 250, 243, 0.2), rgba(229, 247, 243, 0.1));
            border: 1px solid rgba(246, 219, 162, 0.18);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.16);
            backdrop-filter: blur(14px);
        }

        .story-point strong {
            display: block;
            margin-bottom: 6px;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 0.98rem;
            color: #f6fbfb;
        }

        .story-point span {
            display: block;
            color: rgba(224, 245, 243, 0.78);
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .login-panel {
            position: relative;
            z-index: 2;
            min-width: 0;
            padding: 40px 36px;
            background:
                linear-gradient(180deg, rgba(255, 253, 248, 0.96) 0%, rgba(249, 246, 238, 0.9) 100%);
            border-left: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .login-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 10% 20%, rgba(69, 243, 221, 0.12), transparent 26%),
                radial-gradient(circle at 100% 0%, rgba(255, 186, 74, 0.2), transparent 34%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.28), transparent 38%);
            pointer-events: none;
        }

        .login-panel::after {
            content: "";
            position: absolute;
            inset: 22px 24px auto;
            height: 1px;
            background: linear-gradient(90deg, rgba(69, 243, 221, 0.34), rgba(255, 255, 255, 0.86), rgba(255, 181, 73, 0.34));
            opacity: 0.92;
            pointer-events: none;
        }

        .panel-inner {
            width: 100%;
            max-width: 100%;
            padding: 28px 24px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.52), rgba(255, 250, 241, 0.38));
            border: 1px solid rgba(255, 255, 255, 0.58);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.92),
                0 22px 44px rgba(25, 45, 48, 0.1);
            backdrop-filter: blur(20px);
        }

        .panel-inner::before {
            content: "";
            position: absolute;
            inset: 12px 12px auto;
            height: 1px;
            background: linear-gradient(90deg, rgba(44, 229, 200, 0.1), rgba(200, 154, 68, 0.55), rgba(255, 255, 255, 0.8));
            pointer-events: none;
        }

        .panel-kicker {
            margin: 0 0 12px;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--brand-deep);
        }

        .panel-title {
            margin: 0;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 2.15rem;
            line-height: 1.04;
            font-weight: 700;
            letter-spacing: -0.05em;
            color: #132b2f;
        }

        .panel-subtitle {
            margin: 12px 0 28px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 26rem;
        }

        .flash {
            margin-bottom: 18px;
            padding: 13px 14px;
            border-radius: 16px;
            font-size: 0.94rem;
            line-height: 1.6;
            box-shadow: 0 16px 34px rgba(10, 34, 39, 0.08);
            backdrop-filter: blur(12px);
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
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: -0.02em;
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
            padding: 10px 12px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(255, 244, 220, 0.98), rgba(241, 219, 167, 0.84));
            color: #78510d;
            font-weight: 600;
            box-shadow: 0 14px 28px rgba(200, 154, 68, 0.18);
        }

        .submit-btn {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #0d7d74 0%, #18a895 34%, #c89a44 72%, #f0c97f 100%);
            color: #fff;
            font: inherit;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: -0.02em;
            cursor: pointer;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.28),
                0 22px 36px rgba(12, 92, 84, 0.24),
                0 12px 24px rgba(200, 154, 68, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-shadow: 0 1px 10px rgba(8, 52, 49, 0.2);
        }

        .submit-btn::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.08) 34%, rgba(255, 255, 255, 0.02) 100%),
                linear-gradient(120deg, rgba(255, 255, 255, 0.18), transparent 34%, transparent 66%, rgba(255, 228, 172, 0.12));
            pointer-events: none;
            mix-blend-mode: screen;
            opacity: 0.88;
            z-index: 0;
        }

        .submit-btn::after {
            display: none;
        }

        .submit-btn:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.34),
                0 26px 40px rgba(12, 92, 84, 0.28),
                0 16px 28px rgba(200, 154, 68, 0.24);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .panel-footer {
            margin-top: 20px;
            text-align: center;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .panel-footer strong {
            color: var(--text);
        }

        .panel-support {
            margin-top: 18px;
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.74), rgba(248, 243, 232, 0.66));
            border: 1px solid rgba(200, 154, 68, 0.2);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.84),
                0 16px 28px rgba(40, 41, 35, 0.08);
        }

        .support-title {
            margin: 0 0 6px;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 0.98rem;
            font-weight: 600;
            color: #163034;
        }

        .support-note {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .whatsapp-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 54px;
            padding: 12px 16px;
            border-radius: 18px;
            background: linear-gradient(135deg, #0c7a67 0%, #16a085 58%, #d4ab5a 100%);
            color: #fff;
            text-decoration: none;
            font-family: "Sora", "Segoe UI", sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            box-shadow:
                0 18px 30px rgba(12, 122, 103, 0.22),
                0 10px 18px rgba(212, 171, 90, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .whatsapp-link:hover {
            transform: translateY(-2px);
            box-shadow:
                0 22px 34px rgba(12, 122, 103, 0.26),
                0 12px 22px rgba(212, 171, 90, 0.18);
        }

        .whatsapp-link i {
            font-size: 1.12rem;
        }

        @keyframes drift-glow {
            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(22px, -18px, 0) scale(1.06);
            }
        }

        @keyframes aura-sweep-primary {
            0%,
            100% {
                transform: rotate(-14deg) translate3d(0, 0, 0);
            }
            50% {
                transform: rotate(-10deg) translate3d(18px, -10px, 0);
            }
        }

        @keyframes aura-sweep-secondary {
            0%,
            100% {
                transform: rotate(10deg) translate3d(0, 0, 0);
            }
            50% {
                transform: rotate(14deg) translate3d(-18px, 8px, 0);
            }
        }

        @keyframes ring-a-float {
            0%,
            100% {
                transform: translate(-12%, 20%) rotate(-15deg) scale(1);
                opacity: 0.86;
            }
            50% {
                transform: translate(-8%, 16%) rotate(-11deg) scale(1.03);
                opacity: 1;
            }
        }

        @keyframes ring-b-float {
            0%,
            100% {
                transform: translate(12%, -4%) rotate(16deg) scale(1);
                opacity: 0.84;
            }
            50% {
                transform: translate(8%, -1%) rotate(12deg) scale(1.03);
                opacity: 1;
            }
        }

        @keyframes pulse-core {
            0%,
            100% {
                transform: scale(0.96);
                opacity: 0.84;
            }
            50% {
                transform: scale(1.06);
                opacity: 1;
            }
        }


        @media (max-width: 991.98px) {
            .login-shell {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .shell-aura-primary {
                left: -12%;
                right: -4%;
                bottom: 38%;
            }

            .shell-aura-secondary {
                display: none;
            }

            .login-panel {
                order: 1;
            }

            .login-story {
                order: 2;
                padding-bottom: 28px;
                border-right: 0;
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
                padding: 10px;
            }

            .login-shell {
                border-radius: 28px;
                min-height: 100vh;
            }

            .login-story,
            .login-panel {
                padding: 24px 18px;
            }

            .panel-inner {
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
                width: min(86%, 320px);
                max-height: 240px;
            }

            .visual-ring-a,
            .visual-ring-b {
                width: min(130%, 560px);
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
                width: 46px;
                height: 46px;
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
                min-height: 50px;
            }
        }
    </style>
</head>
<body>
<main class="login-page">
    <section class="login-shell" aria-label="Quantum Hotel login">
        <div class="shell-aura shell-aura-primary" aria-hidden="true"></div>
        <div class="shell-aura shell-aura-secondary" aria-hidden="true"></div>
        <div class="login-story">
            <div class="story-top">
                <a href="/" class="brand" aria-label="Quantum Hotel">
                    <span class="brand-mark">
                        <i class="fas fa-hotel"></i>
                    </span>
                    <span class="brand-copy">
                        <span class="brand-kicker">Quantum Hotel</span>
                        <span class="brand-title">Quantum Hotel System</span>
                    </span>
                </a>

                <h1 class="story-headline">Log in faster. Work neatly. Keep every shift calm.</h1>
                <p class="story-text">
                    Access daily transactions, room status, and guest activity from one clearer hub
                    from the very first screen.
                </p>
            </div>

            <div class="story-visual">
                <span class="visual-pulse" aria-hidden="true"></span>
                <span class="visual-ring visual-ring-a" aria-hidden="true"></span>
                <span class="visual-ring visual-ring-b" aria-hidden="true"></span>
                <span class="visual-ring visual-ring-c" aria-hidden="true"></span>
                <span class="visual-node visual-node-left" aria-hidden="true"></span>
                <span class="visual-node visual-node-right" aria-hidden="true"></span>
                <img src="{{ asset('images/login-portal-illustration.svg') }}" alt="Quantum Hotel lobby illustration">
            </div>

            <div class="story-bottom">
                <div class="story-points">
                    <div class="story-point">
                        <strong>Faster check-in</strong>
                        <span>Core data, rooms, and packages flow in through a layout that stays easy to read.</span>
                    </div>
                    <div class="story-point">
                        <strong>Monitor active rooms</strong>
                        <span>Room status and daily transactions stay closely connected to front office operations.</span>
                    </div>
                    <div class="story-point">
                        <strong>Ready for integration</strong>
                        <span>The web app and API are already growing along the same path so future development stays cleaner.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-panel">
            <div class="panel-inner">
                <p class="panel-kicker">Welcome back</p>
                <h2 class="panel-title">Sign in to Quantum Hotel</h2>
                <p class="panel-subtitle">
                    Use your operational account to continue today's transactions, room control, and guest services.
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
                                placeholder="Enter username"
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
                                placeholder="Enter password"
                                autocomplete="current-password"
                                required
                            >
                            <button class="field-action" type="button" id="toggle-password" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-meta">
                        <span class="meta-badge">
                            <i class="fas fa-shield-alt" aria-hidden="true"></i>
                            Internal operational access
                        </span>
                        <span>{{ now()->timezone('Asia/Jakarta')->format('l, d F Y') }}</span>
                    </div>

                    <button class="submit-btn" type="submit">Enter Dashboard</button>
                </form>

                <div class="panel-footer">
                    <strong>Quantum Hotel System</strong><br>
                    Front office, housekeeping, restaurant, laundry, stock, and accounting in one connected workflow.
                </div>

                <div class="panel-support" aria-label="Quantum support contact">
                    <p class="support-title">Need help or interested in the system?</p>
                    <p class="support-note">Chat Team Developer Quantum directly for support, questions, or a product introduction.</p>
                    <a
                        class="whatsapp-link"
                        href="https://wa.me/628128621234?text=Hello%20Team%20Quantum%20Hotel%20System,%20I%20would%20like%20to%20ask%20about%20your%20software."
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Chat Team Developer Quantum on WhatsApp"
                    >
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        Chat Team Developer Quantum
                    </a>
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
            toggleButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');

            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    }());
</script>
</body>
</html>
