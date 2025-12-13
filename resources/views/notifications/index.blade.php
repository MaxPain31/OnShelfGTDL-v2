@php
    $user = auth()->user();
    $isStudent = $user && $user->isStudent();
    $isTeacher = $user && $user->isTeacher();
    $isAdmin = $user && $user->isAdmin();

    if ($isStudent) {
        $layout = 'layout.student.app';
        $backRoute = route('student.dashboard');
    } elseif ($isTeacher) {
        $layout = 'layout.teacher.app';
        $backRoute = route('teacher.dashboard');
    } elseif ($isAdmin) {
        $layout = 'layout.admin.app';
        $backRoute = route('admin.dashboard');
    } else {
        $layout = 'layout.student.app';
        $backRoute = route('student.dashboard');
    }
@endphp

@extends($layout)

@section('title', 'Notifications | OnShelf GTDL')
@section('page_title', 'Notifications')

@section('content')
    <div
        x-data="{
            showModal: false,
            selectedNotification: null,
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
                        // Reload page to update UI
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            },
            async markAllAsRead() {
                try {
                    const response = await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content'),
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error marking all notifications as read:', error);
                }
            },
            openNotificationModal(notification) {
                this.selectedNotification = notification;
                this.showModal = true;
                // Mark as read if unread
                if (!notification.read_at) {
                    this.markAsRead(notification.id);
                }
                // Refresh icons after modal opens
                setTimeout(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                }, 100);
            },
            closeModal() {
                this.showModal = false;
                this.selectedNotification = null;
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
            formatDate(dateString) {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }"
        x-init="
            if (window.lucide) { lucide.createIcons(); }
            @if($selectedNotificationId && $selectedNotification)
                // Auto-open modal for selected notification
                const notification = @js([
                    'id' => $selectedNotification->id,
                    'type' => $selectedNotification->type,
                    'title' => $selectedNotification->title,
                    'message' => $selectedNotification->message,
                    'data' => $selectedNotification->data,
                    'read_at' => $selectedNotification->read_at ? $selectedNotification->read_at->toISOString() : null,
                    'created_at' => $selectedNotification->created_at->toISOString(),
                    'created_at_human' => $selectedNotification->created_at->diffForHumans(),
                    'created_at_full' => $selectedNotification->created_at->format('M d, Y h:i A')
                ]);
                openNotificationModal(notification);
                // Remove notification parameter from URL
                const url = new URL(window.location);
                url.searchParams.delete('notification');
                window.history.replaceState({}, '', url);
            @endif
        "
        @keydown.escape.window="closeModal()"
    >
        <div class="space-y-6">
            {{-- Header with Actions --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-[#4b2036]">All Notifications</h2>
                    <p class="text-sm text-[#7c4c63] mt-1">
                        @if($unreadCount > 0)
                            You have <span class="font-semibold text-[#a03464]">{{ $unreadCount }}</span> unread notification{{ $unreadCount > 1 ? 's' : '' }}
                        @else
                            All notifications are read
                        @endif
                    </p>
                </div>
                @if($unreadCount > 0)
                    <button
                        @click="markAllAsRead()"
                        class="inline-flex items-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        <i data-lucide="check-double" class="w-4 h-4"></i>
                        <span>Mark All as Read</span>
                    </button>
                @endif
            </div>

            {{-- Filters --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-4">
                <form method="GET" action="{{ request()->url() }}" class="flex flex-col sm:flex-row gap-4">
                    {{-- Status Filter --}}
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-[#a03464] mb-2">Status</label>
                        <select
                            name="status"
                            class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2 text-sm text-[#4b2036] focus:border-[#a03464] focus:outline-none focus:ring-2 focus:ring-[#a03464]/20"
                            onchange="this.form.submit()"
                        >
                            <option value="">All</option>
                            <option value="unread" {{ $selectedStatus === 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ $selectedStatus === 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>

                    {{-- Type Filter --}}
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-[#a03464] mb-2">Type</label>
                        <select
                            name="type"
                            class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2 text-sm text-[#4b2036] focus:border-[#a03464] focus:outline-none focus:ring-2 focus:ring-[#a03464]/20"
                            onchange="this.form.submit()"
                        >
                            <option value="">All Types</option>
                            @foreach($notificationTypes as $type)
                                <option value="{{ $type }}" {{ $selectedType === $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Clear Filters --}}
                    @if($selectedStatus || $selectedType)
                        <div class="flex items-end">
                            <a
                                href="{{ request()->url() }}"
                                class="inline-flex items-center gap-2 rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] transition"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span>Clear</span>
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            {{-- Notifications List --}}
            @if($notifications->count() > 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white overflow-hidden">
                    <div class="divide-y divide-[#f7d6e6]">
                        @foreach($notifications as $notification)
                            <div
                                @class([
                                    'p-4 sm:p-6 transition',
                                    'bg-[#fff7fb]' => !$notification->read_at,
                                    'bg-white' => $notification->read_at,
                                    'opacity-60' => $notification->read_at,
                                    'cursor-pointer hover:bg-[#fff2f8]' => true,
                                ])
                                @click="openNotificationModal(@js([
                                    'id' => $notification->id,
                                    'type' => $notification->type,
                                    'title' => $notification->title,
                                    'message' => $notification->message,
                                    'data' => $notification->data,
                                    'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                                    'created_at' => $notification->created_at->toISOString(),
                                    'created_at_human' => $notification->created_at->diffForHumans(),
                                    'created_at_full' => $notification->created_at->format('M d, Y h:i A')
                                ]))"
                            >
                                <div class="flex gap-4">
                                    {{-- Notification Icon --}}
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center"
                                            :style="'background-color: ' + getNotificationColor('{{ $notification->type }}') + '20'"
                                        >
                                            <span
                                                class="w-3 h-3 rounded-full"
                                                :style="'background-color: ' + getNotificationColor('{{ $notification->type }}')"
                                            ></span>
                                        </div>
                                    </div>

                                    {{-- Notification Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-[#4b2036] mb-1">
                                                    {{ $notification->title }}
                                                </h3>
                                                <p class="text-sm text-[#7c4c63] mb-2">
                                                    {{ $notification->message }}
                                                </p>
                                                <div class="flex items-center gap-4 text-xs text-[#7c4c63]">
                                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                    <span>•</span>
                                                    <span>{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                                                    @if(!$notification->read_at)
                                                        <span class="bg-[#ff7ab8] text-white px-2 py-0.5 rounded-full text-xs font-semibold">New</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="mt-6 rounded-[24px] border border-[#f3cbe0] bg-white px-6 py-4">
                    @php
                        $prevUrl = $notifications->previousPageUrl();
                        $nextUrl = $notifications->nextPageUrl();
                    @endphp
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-xs text-[#7c4c63]">
                        <p class="leading-tight">
                            @if ($notifications->total())
                                Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notification{{ $notifications->total() > 1 ? 's' : '' }}
                            @else
                                Showing 0 to 0 of 0 notifications
                            @endif
                        </p>
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ $prevUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-3 py-1 text-xs font-semibold text-[#a03464] {{ $prevUrl ? 'hover:bg-[#fff2f8]' : 'opacity-60 cursor-not-allowed' }}"
                                @if(!$prevUrl) aria-disabled="true" @endif
                            >
                                Previous
                            </a>
                            <span class="text-[#a03464] font-semibold">{{ $notifications->currentPage() }}</span>
                            <a
                                href="{{ $nextUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-3 py-1 text-xs font-semibold text-[#a03464] {{ $nextUrl ? 'hover:bg-[#fff2f8]' : 'opacity-60 cursor-not-allowed' }}"
                                @if(!$nextUrl) aria-disabled="true" @endif
                            >
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-12 text-center">
                    <i data-lucide="bell-off" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                    <h2 class="text-xl font-semibold text-[#4b2036] mb-2">No Notifications</h2>
                    <p class="text-sm text-[#7c4c63] mb-4">
                        @if($selectedStatus || $selectedType)
                            No notifications match your filters.
                        @else
                            You don't have any notifications yet.
                        @endif
                    </p>
                    @if($selectedStatus || $selectedType)
                        <a
                            href="{{ request()->url() }}"
                            class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Clear Filters</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Notification Detail Modal --}}
        <div
            x-cloak
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="closeModal()"
        >
            <div
                x-show="showModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-[24px] shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden"
                @click.stop
            >
                <template x-if="selectedNotification">
                    <div class="p-6 overflow-y-auto flex-1 min-h-0">
                        {{-- Modal Header --}}
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-4 flex-1">
                                <div
                                    class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                    :style="'background-color: ' + getNotificationColor(selectedNotification.type) + '20'"
                                >
                                    <span
                                        class="w-4 h-4 rounded-full"
                                        :style="'background-color: ' + getNotificationColor(selectedNotification.type)"
                                    ></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xl font-semibold text-[#4b2036] mb-1" x-text="selectedNotification.title"></h3>
                                    <p class="text-xs text-[#7c4c63]">
                                        <span x-text="selectedNotification.created_at_human"></span>
                                        <span> • </span>
                                        <span x-text="selectedNotification.created_at_full"></span>
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="closeModal()"
                                class="w-8 h-8 rounded-full border border-[#f3cbe0] text-[#a03464] flex items-center justify-center hover:bg-[#fff2f8] transition flex-shrink-0"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Notification Type Badge --}}
                        <div class="mb-4">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                :style="'background-color: ' + getNotificationColor(selectedNotification.type) + '20; color: ' + getNotificationColor(selectedNotification.type)"
                                x-text="selectedNotification.type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"
                            ></span>
                            <span
                                x-show="!selectedNotification.read_at"
                                class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#ff7ab8] text-white"
                            >
                                New
                            </span>
                        </div>

                        {{-- Book Image (if available) --}}
                        <div x-show="selectedNotification.data && selectedNotification.data.book_image_path" class="mb-6">
                            <div class="flex justify-center">
                                <div class="rounded-[10px] bg-[#fde7f0] border border-[#f3cbe0] overflow-hidden" style="aspect-ratio: 2 / 3; max-width: 200px; width: 100%;">
                                    <img
                                        :src="selectedNotification.data.book_image_path"
                                        :alt="selectedNotification.data.book_image_alt || 'Book cover'"
                                        class="w-full h-full object-cover"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Notification Message --}}
                        <div class="mb-6">
                            <p class="text-sm text-[#7c4c63] leading-relaxed" x-text="selectedNotification.message"></p>
                        </div>

                        {{-- Additional Details --}}
                        <div x-show="selectedNotification.data && Object.keys(selectedNotification.data).filter(key => key !== 'book_image_path' && key !== 'book_image_alt').length > 0" class="space-y-4">
                            <div class="border-t border-[#f3cbe0] pt-4">
                                <h4 class="text-sm font-semibold text-[#4b2036] mb-3">Details</h4>
                                <div class="space-y-3">
                                    <template x-for="entry in Object.entries(selectedNotification.data || {}).filter(([k]) => k !== 'book_image_path' && k !== 'book_image_alt')" :key="entry[0]">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                            <span class="text-xs font-semibold text-[#a03464] sm:w-32 flex-shrink-0" x-text="entry[0].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + ':'"></span>
                                            <span class="text-sm text-[#4b2036] flex-1" x-text="entry[1]"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Timestamps --}}
                        <div class="border-t border-[#f3cbe0] pt-4 mt-6">
                            <div class="space-y-2 text-xs text-[#7c4c63]">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Created:</span>
                                    <span x-text="formatDate(selectedNotification.created_at)"></span>
                                </div>
                                <div x-show="selectedNotification.read_at" class="flex items-center justify-between">
                                    <span class="font-semibold">Read:</span>
                                    <span x-text="formatDate(selectedNotification.read_at)"></span>
                                </div>
                                <div x-show="!selectedNotification.read_at" class="flex items-center justify-between">
                                    <span class="font-semibold">Status:</span>
                                    <span class="text-[#ff7ab8] font-semibold">Unread</span>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-[#f3cbe0]">
                            <button
                                @click="closeModal()"
                                class="px-4 py-2 rounded-[10px] border border-[#f3cbe0] text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] transition"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endsection

