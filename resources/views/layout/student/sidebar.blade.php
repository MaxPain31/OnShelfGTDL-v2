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
            ['label' => 'My Shelf', 'route' => 'student.my-shelf', 'icon' => 'library'],
            ['label' => 'Books', 'route' => 'student.books', 'icon' => 'book-open'],
            ['label' => 'E-Books', 'route' => 'student.ebooks', 'icon' => 'file-text'],
            ['label' => 'Borrowed Books', 'route' => 'student.borrowed-books', 'icon' => 'book-copy'],
            ['label' => 'Reserved Books', 'route' => 'student.reserved-books', 'icon' => 'bookmark'],
            ['label' => 'Rules & Regulation', 'route' => 'student.rules', 'icon' => 'shield-check'],
        ];

        $isDashboardActive = request()->routeIs('student.dashboard');
    @endphp

    <nav class="flex-1 space-y-2">
        {{-- Home Link --}}
        <a
            href="{{ route('student.dashboard') }}"
            class="flex items-center gap-3 rounded-[14px] px-3 py-3 transition"
            :class="[
                '{{ $isDashboardActive ? 'bg-white text-[#a03464]' : 'text-white/90 hover:bg-white/10' }}',
                $store.sidebar.collapsed ? 'justify-center' : ''
            ]"
        >
            <i data-lucide="home" class="w-5 h-5"></i>
            <span class="text-sm font-medium" x-show="!$store.sidebar.collapsed" x-cloak>Home</span>
        </a>

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

