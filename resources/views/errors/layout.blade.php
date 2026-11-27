<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') — Propertywala Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pw-primary: #6d28d9;
            --pw-primary-hover: #5b21b6;
            --pw-primary-light: #f5f3ff;
            --pw-surface: #f8fafc;
            --pw-card: #ffffff;
            --pw-border: #e2e8f0;
            --pw-text: #0f172a;
            --pw-text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--pw-surface);
            color: var(--pw-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .error-card {
            background: var(--pw-card);
            border: 1px solid var(--pw-border);
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.03);
            max-width: 540px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #7c3aed, #4f46e5, #06b6d4);
        }

        .brand-header {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            text-decoration: none;
        }

        .brand-logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            box-shadow: 0 4px 10px rgba(109, 40, 217, 0.35);
        }

        .brand-logo-icon svg {
            width: 20px;
            height: 20px;
            stroke: #ffffff;
        }

        .brand-name {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            line-height: 1.2;
            text-align: left;
        }

        .brand-name span {
            color: #7c3aed;
        }

        .brand-sub {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #7c3aed;
        }

        .illustration-container {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .illustration-container svg {
            width: 44px;
            height: 44px;
        }

        .error-code-badge {
            display: inline-block;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 14px;
        }

        .error-title {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .error-gujarati-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #7c3aed;
            margin-bottom: 12px;
        }

        .error-description {
            font-size: 0.90rem;
            color: var(--pw-text-muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        @media (min-width: 480px) {
            .btn-group {
                flex-direction: row;
                justify-content: center;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 11px 22px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--pw-primary);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(109, 40, 217, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--pw-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(109, 40, 217, 0.35);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .footer-note {
            margin-top: 24px;
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .footer-note a {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <a href="{{ url('/admin') }}" class="brand-header">
            <div class="brand-logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5L12 3l9 7.5" />
                    <path d="M5.5 9.5V20a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V9.5" />
                    <path d="M9.5 21v-6a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v6" />
                </svg>
            </div>
            <div>
                <div class="brand-name">Property<span>wala</span></div>
                <span class="brand-sub">Admin Console</span>
            </div>
        </a>

        @yield('content')

        <div class="footer-note">
            Propertywala Enterprise Management • <a href="{{ url('/admin') }}">Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
