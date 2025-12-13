@php
    $authUser = auth()->user();
    $userName = $authUser
        ? ($authUser->userInfo->full_name ?? $authUser->name ?? $authUser->email)
        : 'Administrator';
    $userRole = $authUser
        ? ucwords(str_replace('_', ' ', optional($authUser->role)->display_name ?? optional($authUser->role)->name ?? 'User'))
        : 'Administrator';
@endphp

<header
    class="admin-top-bar fixed top-0 right-0 flex items-center justify-between px-4 py-6 sm:px-4 sm:py-6   lg:px-10 lg:py-6 border-b border-[#f1c9da] bg-white/80 backdrop-blur-lg z-40 shadow-sm transition-all duration-300"
    style="left: var(--sidebar-width);"
>
    <div class="flex items-center gap-2 lg:gap-4">
        <button
            type="button"
            class="w-11 h-11 rounded-full border border-[#f3cbe0] text-[#a03464] flex items-center justify-center hover:bg-[#fff2f8]"
            x-data
            x-on:click="$store.sidebar.toggle()"
        >
            <svg x-show="!$store.sidebar.collapsed" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h12M4 17h16" />
            </svg>
            <svg x-show="$store.sidebar.collapsed" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12M6 12h12M6 18h12" />
            </svg>
        </button>
        <h1 class="text-xl   sm:text-2xl font-semibold text-[#4b2036]">@yield('page_title')</h1>
    </div>
    <div class="flex items-center gap-2 lg:gap-6">
        {{-- Notifications --}}
        <div 
            class="relative" 
            x-data="{
                open: false,
                notifications: [],
                unreadCount: 0,
                isLoading: false,
                async fetchNotifications() {
                    this.isLoading = true;
                    try {
                        const response = await fetch('{{ route('notifications.index') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        }
                    } catch (error) {
                        console.error('Error fetching notifications:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },
                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content'),
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.unreadCount = data.unread_count;
                            const notification = this.notifications.find(n => n.id === notificationId);
                            if (notification) {
                                notification.read_at = new Date().toISOString();
                            }
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                },
                getNotificationColor(type) {
                    const colors = {
                        'book_borrowed': '#6ddccf',
                        'book_reserved': '#f9c74f',
                        'book_returned': '#10b981',
                        'due_date_approaching': '#ff7ab8',
                        'claim_deadline_approaching': '#f9c74f',
                        'reservation_expired': '#ef4444',
                        'admin_book_borrowed': '#6ddccf',
                        'admin_book_reserved': '#f9c74f',
                        'admin_due_date_approaching': '#ff7ab8',
                    };
                    return colors[type] || '#a03464';
                },
                init() {
                    this.fetchNotifications();
                    // Refresh notifications every 30 seconds
                    setInterval(() => {
                        if (!this.open) {
                            this.fetchNotifications();
                        }
                    }, 30000);
                }
            }"
            @click.away="open = false"
        >
            <button
                type="button"
                class="w-11 h-11 rounded-full border border-[#f3cbe0] text-[#a03464] flex items-center justify-center hover:bg-[#fff2f8] relative"
                @click="open = !open; if (open) fetchNotifications()"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-12 0v5l-2 2h16l-2-2z" />
                </svg>
                <span 
                    x-show="unreadCount > 0"
                    x-cloak
                    class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full bg-[#ff7ab8] border-2 border-white"
                ></span>
                <span class="sr-only">Toggle notifications</span>
            </button>
            <div
                x-cloak
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute right-0 mt-3 w-72 rounded-3xl border border-[#f3cbe0] bg-white shadow-[0_20px_40px_rgba(160,52,100,0.2)] p-5 space-y-4 max-h-[500px] overflow-y-auto"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-[#4b2036]">Notifications</p>
                    <span 
                        x-show="unreadCount > 0"
                        x-cloak
                        x-text="unreadCount + ' new'"
                        class="text-xs text-[#a03464] bg-[#fde7f0] px-2 py-0.5 rounded-full"
                    ></span>
                </div>
                
                <div x-show="isLoading" class="text-center py-4">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-[#a03464]"></div>
                </div>
                
                <ul x-show="!isLoading && notifications.length > 0" class="space-y-3 text-sm text-[#4b2036]">
                    <template x-for="notification in notifications" :key="notification.id">
                        <li 
                            class="flex gap-3 cursor-pointer hover:bg-[#fff7fb] p-2 rounded-lg transition"
                            @click="window.location.href = '{{ route('admin.notifications') }}?notification=' + notification.id"
                            :class="notification.read_at ? 'opacity-60' : ''"
                        >
                            <span 
                                class="w-2.5 h-2.5 mt-1 rounded-full flex-shrink-0"
                                :style="'background-color: ' + getNotificationColor(notification.type)"
                            ></span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold" x-text="notification.title"></p>
                                <p class="text-xs text-[#7c4c63] mt-0.5" x-text="notification.message"></p>
                                <p class="text-xs text-[#7c4c63] mt-1" x-text="notification.created_at"></p>
                            </div>
                        </li>
                    </template>
                </ul>
                
                <div x-show="!isLoading && notifications.length === 0" class="text-center py-4">
                    <p class="text-sm text-[#7c4c63]">No notifications</p>
                </div>
                
                <a
                    href="{{ route('admin.notifications') }}"
                    class="block text-center text-xs font-semibold text-[#a03464] hover:text-[#661d44] pt-2 border-t border-[#f3cbe0]"
                >
                    View all notifications
                </a>
            </div>
        </div>

        {{-- Profile --}}
        <div class="flex items-center gap-3 relative" x-data="{ open: false }" @click.away="open = false">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-[#4b2036]">{{ $userName }}</p>
                <p class="text-xs text-[#7c4c63]">{{ $userRole }}</p>
            </div>
            <button
                type="button"
                class="relative focus:outline-none"
                @click="open = !open"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <img
                    src="{{ asset('img/dp.jpg') }}"
                    alt="Profile"
                    class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-md"
                />
                <span class="sr-only">Toggle profile options</span>
            </button>

            {{-- Profile dropdown --}}
            <div
                x-cloak
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute right-0 top-14 w-60 rounded-3xl border border-[#f3cbe0] bg-white shadow-[0_20px_40px_rgba(160,52,100,0.2)] p-5 space-y-4"
            >
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('img/dp.jpg') }}"
                        alt="Profile"
                        class="w-12 h-12 rounded-full object-cover border border-[#f3cbe0]/60"
                    />
                    <div>
                        <p class="text-sm font-semibold text-[#4b2036]">{{ $userName }}</p>
                        <p class="text-xs text-[#7c4c63]">{{ $userRole }}</p>
                    </div>
                </div>
                <a
                    href="{{ route('admin.profile') }}"
                    class="flex items-center justify-between rounded-2xl border border-[#f3cbe0]/60 px-4 py-2 text-xs font-semibold text-[#a03464] hover:bg-[#fff2f8] hover:border-[#a03464]"
                >
                    <span>View profile</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                <button
                        type="submit"
                    class="w-full rounded-2xl bg-[#a03464] text-white text-xs font-semibold py-2 hover:bg-[#661d44]"
                >
                    Sign out
                </button>
                </form>
            </div>
        </div>
    </div>
</header>
