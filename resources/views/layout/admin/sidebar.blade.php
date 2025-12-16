<aside
    x-data
    class="fixed inset-y-0 left-0 bg-[#a03464] text-white flex flex-col py-6 px-4 gap-8 shadow-xl z-50 transition-all duration-300 transform lg:translate-x-0"
    :class="[
        $store.sidebar.collapsed
            ? 'w-64 -translate-x-full lg:w-20 lg:translate-x-0'
            : 'w-64 translate-x-0 lg:w-64'
    ]"
>
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3" x-bind:class="$store.sidebar.collapsed ? 'justify-center w-full' : ''">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                <img src="{{ asset('img/logo.png') }}" alt="OnShelf GTDL" class="w-10 h-10 object-contain" />
            </div>
            <div x-show="!$store.sidebar.collapsed" x-cloak class="transition-opacity duration-200">
                <p class="text-xs uppercase tracking-[0.25em] text-white/70">OnShelf</p>
                <p class="text-lg font-semibold">GTDL</p>
            </div>
        </div>
    </div>

    @php
        $links = [
            ['label' => 'Manage Books', 'route' => 'admin.manage-books', 'icon' => 'book-open'],
            ['label' => 'Manage E-Books', 'route' => 'admin.manage-ebooks', 'icon' => 'file-text'],
            ['label' => 'Borrowed books', 'route' => 'admin.manage-borrows', 'icon' => 'book-copy'],
            ['label' => 'Reserved Books', 'route' => 'admin.manage-reservations', 'icon' => 'bookmark'],
            ['label' => 'Attendance', 'route' => 'admin.manage-attendance', 'icon' => 'clipboard-list'],
            ['label' => 'Reports', 'route' => 'admin.reports', 'icon' => 'bar-chart-2'],
            ['label' => 'Rules & Regulations', 'route' => 'admin.manage-rules', 'icon' => 'shield-check'],
        ];

        $isManageUsersActive = request()->routeIs('admin.manage-students') || request()->routeIs('admin.manage-teachers');
        $isDashboardActive = request()->routeIs('admin.dashboard');
    @endphp

    <nav class="flex-1 space-y-2">
        {{-- Dashboard Link --}}
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 rounded-[14px] px-3 py-3 transition"
            :class="[
                '{{ $isDashboardActive ? 'bg-white text-[#a03464]' : 'text-white/90 hover:bg-white/10' }}',
                $store.sidebar.collapsed ? 'justify-center' : ''
            ]"
        >
            <i data-lucide="home" class="w-5 h-5"></i>
            <span class="text-sm font-medium" x-show="!$store.sidebar.collapsed" x-cloak>Dashboard</span>
        </a>

        {{-- Manage Users Dropdown --}}
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button
                type="button"
                @click="open = !open"
                class="flex items-center gap-3 rounded-[14px] px-3 py-3 transition w-full {{ $isManageUsersActive ? 'bg-white text-[#a03464]' : 'text-white/90 hover:bg-white/10' }}"
                :class="$store.sidebar.collapsed ? 'justify-center' : ''"
            >
                <i data-lucide="users" class="w-5 h-5"></i>
                <span class="text-sm font-medium flex-1 text-left" x-show="!$store.sidebar.collapsed" x-cloak>Manage Users</span>
                <svg
                    x-show="!$store.sidebar.collapsed"
                    x-cloak
                    class="w-4 h-4 transition-transform"
                    :class="open ? 'rotate-90' : ''"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            {{-- Dropdown Menu --}}
            <div
                x-show="open && !$store.sidebar.collapsed"
                x-cloak
                @click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-1"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-1"
                class="mt-1 ml-4 space-y-1"
            >
                <a
                    href="{{ route('admin.manage-students') }}"
                    class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-sm transition {{ request()->routeIs('admin.manage-students') ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                >
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    <span>Students</span>
                </a>
                <a
                    href="{{ route('admin.manage-teachers') }}"
                    class="flex items-center gap-3 rounded-[10px] px-3 py-2 text-sm transition {{ request()->routeIs('admin.manage-teachers') ? 'bg-white/20 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}"
                >
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Teachers</span>
                </a>
            </div>

            {{-- Collapsed Sidebar Tooltip Menu --}}
            <div
                x-show="open && $store.sidebar.collapsed"
                x-cloak
                @click.away="open = false"
                class="absolute left-full ml-4 top-0 bg-[#a03464] rounded-lg shadow-xl py-2 min-w-[160px] z-50 border border-white/20"
            >
                <a
                    href="{{ route('admin.manage-students') }}"
                    class="flex items-center gap-3 px-4 py-2 text-sm transition {{ request()->routeIs('admin.manage-students') ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10' }}"
                    @click="open = false"
                >
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    <span>Students</span>
                </a>
                <a
                    href="{{ route('admin.manage-teachers') }}"
                    class="flex items-center gap-3 px-4 py-2 text-sm transition {{ request()->routeIs('admin.manage-teachers') ? 'bg-white/20 text-white' : 'text-white hover:bg-white/10' }}"
                    @click="open = false"
                >
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Teachers</span>
                </a>
            </div>
        </div>

        {{-- Other Links --}}
        @foreach ($links as $link)
            @php
                $isActive = $link['route'] !== '#' && request()->routeIs($link['route']);
            @endphp
            <a
                href="{{ $link['route'] === '#' ? '#' : route($link['route']) }}"
                class="flex items-center gap-3 rounded-[14px] px-3 py-3 transition"
                :class="[
                    '{{ $isActive ? 'bg-white text-[#a03464]' : 'text-white/90 hover:bg-white/10' }}',
                    $store.sidebar.collapsed ? 'justify-center' : ''
                ]"
            >
                <i data-lucide="{{ $link['icon'] }}" class="w-5 h-5"></i>
                <span class="text-sm font-medium" x-show="!$store.sidebar.collapsed" x-cloak>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-auto flex flex-col gap-2">
        <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.collapsed" x-cloak>
            @csrf
            <button
                type="submit"
                class="w-full rounded-[12px] border border-white/40 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition flex items-center gap-2 justify-center"
            >
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Logout
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}" x-show="$store.sidebar.collapsed" x-cloak>
            @csrf
            <button
                type="submit"
                class="rounded-full border border-white/40 w-11 h-11 flex items-center justify-center text-white hover:bg-white/10 transition"
            >
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span class="sr-only">Logout</span>
            </button>
        </form>
    </div>
</aside>
