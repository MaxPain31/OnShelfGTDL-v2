<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'OnShelf Reader')</title>
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
                                dark: '#661d44',
                            },
                        },
                        fontFamily: {
                            sans: ['Manrope', 'ui-sans-serif', 'system-ui'],
                        },
                    },
                },
                plugins: [],
            };
        </script>
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        <style>
            [x-cloak] {
                display: none !important;
            }
            body {
                overflow: hidden;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                -webkit-touch-callout: none;
            }
            /* Prevent text selection */
            * {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
            }
            /* Prevent image dragging */
            img, canvas {
                -webkit-user-drag: none;
                -khtml-user-drag: none;
                -moz-user-drag: none;
                -o-user-drag: none;
                user-drag: none;
                pointer-events: none;
            }
            /* Disable context menu */
            * {
                -webkit-touch-callout: none;
                -webkit-user-select: none;
            }
        </style>
    </head>
    <body class="bg-[#f6e5ef] text-[#4b2036] font-['Manrope',sans-serif]">
        @yield('content')
        <script src="{{ asset('js/app.js') }}" defer></script>
    </body>
</html>

