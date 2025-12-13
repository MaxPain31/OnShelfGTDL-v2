<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'OnShelf Student Panel')</title>
        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <style>
            [x-cloak] {
                display: none !important;
            }
            :root {
                --sidebar-width: 16rem;
            }
            .admin-content-wrapper {
                margin-left: var(--sidebar-width);
                transition: margin-left 300ms ease;
            }
            .admin-top-bar {
                left: var(--sidebar-width);
            }
            @media (max-width: 1023px) {
                .admin-content-wrapper {
                    margin-left: 0 !important;
                }
                .admin-top-bar {
                    left: 0 !important;
                }
            }
        </style>
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
        <script>
            document.addEventListener('alpine:init', () => {
                // Load saved sidebar state from localStorage
                const savedState = localStorage.getItem('sidebarCollapsed');
                const prefersCollapsedOnMobile = window.matchMedia('(max-width: 768px)').matches;
                const initialCollapsed = savedState !== null ? savedState === 'true' : prefersCollapsedOnMobile;

                // Set initial CSS variable based on saved state
                document.documentElement.style.setProperty('--sidebar-width', initialCollapsed ? '5rem' : '16rem');

                Alpine.store('sidebar', {
                    collapsed: initialCollapsed,
                    toggle() {
                        this.collapsed = !this.collapsed;
                        // Save state to localStorage
                        localStorage.setItem('sidebarCollapsed', this.collapsed.toString());
                        document.documentElement.style.setProperty('--sidebar-width', this.collapsed ? '5rem' : '16rem');
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    },
                });
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        </script>
    </head>
    <body class="bg-[#f6e5ef] text-[#4b2036] font-['Manrope',sans-serif]">
        <div class="relative min-h-screen">
            @include('layout.admin.sidebar')

            <div
                x-data
                x-cloak
                x-show="!$store.sidebar.collapsed"
                class="fixed inset-0 bg-black/40 z-40 lg:hidden"
                @click="$store.sidebar.toggle()"
            ></div>

            <div class="admin-content-wrapper">
                @include('layout.admin.top-bar')
                <main class="pt-24 min-h-screen sm:px-6 sm:pb-6 sm:pt-32 lg:px-10 lg:pb-10 lg:pt-32 bg-[#fdeff5]">
                    <div class="bg-white/80 sm:rounded-[24px] lg:rounded-[32px] shadow-[0_20px_60px_rgba(160,52,100,0.12)] p-4 sm:p-6 lg:p-8">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        <script src="{{ asset('js/app.js') }}" defer></script>
    </body>
</html>
