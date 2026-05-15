<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <style>
        :root {
            --bg-1: #0f172a;
            --bg-2: #111827;
            --panel: rgba(15, 23, 42, 0.78);
            --panel-border: rgba(255, 255, 255, 0.10);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --input-bg: rgba(255, 255, 255, 0.06);
            --input-border: rgba(255, 255, 255, 0.10);
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
            --radius: 10px;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background: #888;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .login-shell {
            width: 100%;
            max-width: 520px;
        }

        .login-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--panel-border);
            background: #eee;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0));
        }

        .login-header {
            padding: 32px 32px 18px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .brand-logo {
            max-width: 250px;
            width: 100%;
            height: auto;
            display: block;
        }

        .login-body {
            padding: 28px 32px 32px;
        }

        .form-group + .form-group,
        .form-group + .form-options,
        .form-options + .form-actions {
            margin-top: 18px;
        }

        .field-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            height: 50px;
            border-radius: 14px;
            border: 1px solid var(--input-border);
            background: #fff;
            color: #333;
            padding: 0 16px;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .form-control::placeholder { color: rgba(148, 163, 184, 0.9); }

        .form-control:focus {
            border-color: rgba(37, 99, 235, 0.9);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-control.is-invalid {
            border-color: rgba(239, 68, 68, 0.9);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
        }

        .invalid-feedback {
            display: block;
            margin-top: 8px;
            font-size: 0.875rem;
            color: #fca5a5;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .check-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #333;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .check-wrap input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 14px;
            height: 50px;
            padding: 0 22px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, background .2s ease, opacity .2s ease, box-shadow .2s ease;
        }

        .btn:active { transform: translateY(1px); }

        .btn-primary {
            min-width: 140px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
        }

        .btn-primary:hover { background: linear-gradient(135deg, var(--primary-hover), #2563eb); }

        .btn-link {
            height: auto;
            padding: 0;
            color: #93c5fd;
            background: transparent;
            font-weight: 600;
        }

        .btn-link:hover { color: #bfdbfe; }

        .helper-text {
            margin-top: 14px;
            font-size: 0.85rem;
            color: #333;
            line-height: 1.5;
        }

        @media (max-width: 640px) {
            body { padding: 20px 12px; }
            .login-header { padding: 24px 20px 16px; }
            .login-body { padding: 22px 20px 24px; }
            .form-actions { flex-direction: column; align-items: stretch; }
            .btn-primary { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-card">
            <div class="login-header">
                <a class="brand-link" href="https://www.allstars-web.com/" target="_blank" rel="noopener noreferrer">
                    <img src="/admin/images/allstarsweb.gif" alt="All Stars Web" class="brand-logo">
                </a>
            </div>

            <div class="login-body">
                <form method="POST" id="login_form" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="form-group">
                        <label for="email" class="field-label">{{ __('Email Address') }}</label>
                        <input
                            id="email"
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                            placeholder="{{ __('Enter your email or scan QR code') }}"
                        >
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="field-label">{{ __('Password') }}</label>
                        <input
                            id="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="{{ __('Enter your password') }}"
                        >
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="check-wrap" for="remember">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>{{ __('Remember Me') }}</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" id="login_submit_btn" style="width: 100%;">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const form = document.getElementById('login_form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const submitButton = document.getElementById('login_submit_btn');
            const asmEmailDomain = @json(config('allstars.stores.ASM.email_domain'));

            function splitMulti(str, tokens) {
                let normalized = str;
                const tempToken = tokens[0];

                for (let i = 1; i < tokens.length; i++) {
                    normalized = normalized.split(tokens[i]).join(tempToken);
                }

                return normalized.split(tempToken);
            }

            function shouldUseQrLogin(value) {
                return value.includes('|') || value.includes('^') || value.includes('$');
            }

            function loginWithQRCode() {
                const qrCodeString = (emailInput.value || '').trim();

                if (!qrCodeString) {
                    form.submit();
                    return;
                }

                if (qrCodeString.includes('@')) {
                    form.submit();
                    return;
                }

                let qrCodeArray = splitMulti(qrCodeString, ['||', '^^', '$$']).filter(Boolean);

                if (qrCodeArray.length < 2) {
                    qrCodeArray = splitMulti(qrCodeString, ['|', '^', '$']).filter(Boolean);
                }

                if (qrCodeArray.length >= 2) {
                    emailInput.value = qrCodeArray[0].trim() + '@' + asmEmailDomain;
                    passwordInput.value = qrCodeArray[1].trim();
                }

                form.submit();
            }

            function submitForm() {
                const value = (emailInput.value || '').trim();

                if (shouldUseQrLogin(value)) {
                    loginWithQRCode();
                    return;
                }

                form.submit();
            }

            submitButton.addEventListener('click', submitForm);
            emailInput.addEventListener('change', function () {
                const value = (emailInput.value || '').trim();
                if (shouldUseQrLogin(value)) {
                    loginWithQRCode();
                }
            });
            form.addEventListener('submit', function () {
                submitButton.disabled = true;
                submitButton.style.opacity = '0.75';
            });

            window.submitForm = submitForm;
            window.loginWithQRCode = loginWithQRCode;
            window.splitMulti = splitMulti;
        })();
    </script>
</body>
</html>
