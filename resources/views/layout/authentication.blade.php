<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'OnShelf GTDL')</title>
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script>
            window.tailwind = window.tailwind || {};
            window.tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                primary: '#a03464',
                                light: '#fde7f0',
                                soft: '#dfa8c3',
                                dark: '#661d44'
                            }
                        },
                        fontFamily: {
                            sans: ['Manrope', 'ui-sans-serif', 'system-ui']
                        }
                    }
                },
                plugins: []
            };
        </script>
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <style>
            :root {
                --brand-primary: #a03464;
                --brand-primary-soft: #dfa8c3;
                --brand-primary-light: #fde7f0;
                --brand-secondary: #683058;
                --brand-surface: rgba(255, 255, 255, 0.92);
                --brand-success: #0f766e;
                --brand-error: #dc2626;
                --brand-idle: #6b6b6b;
            }

            body {
                font-family: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: #fff5fb;
                color: #2d0d1b;
                position: relative;
            }

            body::after {
                content: '';
                position: fixed;
                inset: 0;
                background: url('{{ asset('img/logo.png') }}') center center / 100% no-repeat;
                opacity: 0.05;
                pointer-events: none;
                z-index: 0;
            }

            .bg-hero-ring::before {
                content: '';
                position: absolute;
                inset: -120px;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.35) 35%, rgba(255, 255, 255, 0) 70%),
                    radial-gradient(circle at center, rgba(160, 52, 100, 0.08), transparent);
                border-radius: 9999px;
                border: 22px solid rgba(160, 52, 100, 0.08);
                filter: blur(0.5px);
                z-index: -1;
            }

            .glass-panel {
                background: var(--brand-surface);
                backdrop-filter: blur(18px);
                box-shadow: 0 25px 70px rgba(160, 52, 100, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.6);
            }

            .validation-label {
                font-size: 0.8rem;
                margin-top: 0.5rem;
                transition: color 150ms ease;
            }

            .validation-label[data-state='idle'] {
                color: var(--brand-idle);
            }

            .validation-label[data-state='success'] {
                color: var(--brand-success);
            }

            .validation-label[data-state='error'] {
                color: var(--brand-error);
            }

            .hidden {
                display: none !important;
            }

            [data-input-state='success'] {
                border-color: var(--brand-success) !important;
                box-shadow: 0 0 0 1px var(--brand-success) inset;
            }

            [data-input-state='error'] {
                border-color: var(--brand-error) !important;
                box-shadow: 0 0 0 1px var(--brand-error) inset;
            }
        </style>
        @stack('head')
    </head>
    <body class="min-h-screen flex items-center justify-center px-4 py-8 lg:py-12 relative overflow-x-hidden @yield('body_class')">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <div class="w-[90vw] h-[90vw] max-w-[1100px] max-h-[1100px] relative opacity-20"></div>
        </div>
        <div class="relative z-10 w-full max-w-6xl">
            @yield('content')
        </div>
        @stack('scripts')
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var input = document.querySelector(button.dataset.target);
                        if (!input) return;
                        var isHidden = input.type === 'password';
                        input.type = isHidden ? 'text' : 'password';
                        button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                        var eyeOpen = button.querySelector('[data-eye-open]');
                        var eyeClosed = button.querySelector('[data-eye-closed]');
                        if (eyeOpen && eyeClosed) {
                            eyeOpen.classList.toggle('hidden', !isHidden);
                            eyeClosed.classList.toggle('hidden', isHidden);
                        }
                        var labelShow = button.querySelector('[data-label-show]');
                        var labelHide = button.querySelector('[data-label-hide]');
                        if (labelShow && labelHide) {
                            labelShow.classList.toggle('hidden', isHidden);
                            labelHide.classList.toggle('hidden', !isHidden);
                        }
                    });
                });

                document.querySelectorAll('form').forEach(function (form) {
                    form.addEventListener('input', function (event) {
                        var target = event.target;
                        if (!(target instanceof HTMLElement)) return;
                        var wrapper = target.closest('[data-field]');
                        if (!wrapper) return;

                        target.classList.remove('border-rose-400', 'focus:ring-rose-200');
                        target.classList.add('border-[#f3cbe0]', 'focus:ring-[#d96a9f]');

                        var label = wrapper.querySelector('.validation-label');
                        if (label) {
                            label.dataset.state = 'idle';
                            var defaultMessage = label.dataset.default;
                            if (defaultMessage) {
                                label.textContent = defaultMessage;
                            }
                        }

                        var summary = form.querySelector('[data-error-summary]');
                        if (summary) {
                            summary.classList.add('hidden');
                        }
                    });
                });
            });
        </script>
    </body>
</html>
