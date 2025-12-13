@extends('layout.admin.app')

@section('title', 'Manage Borrows | OnShelf GTDL')
@section('page_title', 'Manage Borrows')

@section('content')
    <div
        x-data="{
            searchQuery: '{{ request('search', '') }}',
            selectedStatus: '{{ $selectedStatus }}',
            selectedUserType: '{{ $selectedUserType }}',
            showReturnConfirmModal: false,
            returnBorrowId: null,
            isReturning: false,
            returnMessage: '',
            returnMessageType: 'success',
            openReturnConfirm(borrowId) {
                this.returnBorrowId = borrowId;
                this.showReturnConfirmModal = true;
                this.returnMessage = '';
                this.isReturning = false;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeReturnConfirm() {
                this.showReturnConfirmModal = false;
                this.returnBorrowId = null;
                this.returnMessage = '';
            },
            async confirmReturn() {
                if (!this.returnBorrowId) return;

                this.isReturning = true;
                this.returnMessage = '';

                try {
                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                    const response = await fetch(`{{ route('admin.manage-borrows.return', ':id') }}`.replace(':id', this.returnBorrowId), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.returnMessage = data.message || 'Book marked as returned successfully!';
                        this.returnMessageType = 'success';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.returnMessage = data.message || 'Failed to mark book as returned. Please try again.';
                        this.returnMessageType = 'error';
                    }
                } catch (error) {
                    console.error('Error returning book:', error);
                    this.returnMessage = 'An error occurred while marking the book as returned. Please try again.';
                    this.returnMessageType = 'error';
                } finally {
                    this.isReturning = false;
                }
            }
        }"
        x-init="if (window.lucide) { lucide.createIcons(); }"
        x-effect="if (showReturnConfirmModal && window.lucide) { setTimeout(() => lucide.createIcons(), 100); }"
    >
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
            <div class="flex flex-wrap items-center gap-3 border-b border-[#f3cbe0] px-6 py-4">
                <h2 class="text-lg font-semibold text-[#4b2036]">All Borrows</h2>
                <div class="ml-auto flex items-center gap-3 flex-wrap">
                    {{-- Search --}}
                    <form method="GET" action="{{ route('admin.manage-borrows') }}" class="relative">
                        <input
                            type="search"
                            name="search"
                            x-model="searchQuery"
                            placeholder="Search by book, user, email..."
                            class="custom-search rounded-full border border-[#f3cbe0] bg-[#fff7fb] pr-12 pl-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                        />
                        <button
                            type="submit"
                            class="absolute inset-y-0 right-3 flex items-center text-[#a03464]/60 hover:text-[#a03464]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                            </svg>
                        </button>
                    </form>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('admin.manage-borrows') }}" class="flex items-center gap-2">
                        <input type="hidden" name="search" :value="searchQuery">
                        <select name="status" x-model="selectedStatus" @change="$el.closest('form').submit()" class="rounded-full border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f] min-w-[230px]">
                            <option value="active">Active (Borrowed & Overdue)</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="overdue">Overdue</option>
                            <option value="returned">Returned</option>
                            <option value="">All</option>
                        </select>
                        <select name="user_type" x-model="selectedUserType" @change="$el.closest('form').submit()" class="rounded-full border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f] min-w-[100px]">
                            <option value="">All Users</option>
                            <option value="student">Students</option>
                            <option value="teacher">Teachers</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[570px]">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap">Book</th>
                            <th class="px-6 py-3 whitespace-nowrap">Borrower</th>
                            <th class="px-6 py-3 whitespace-nowrap">User Type</th>
                            <th class="px-6 py-3 whitespace-nowrap">Borrow Date</th>
                            <th class="px-6 py-3 whitespace-nowrap">Due Date</th>
                            <th class="px-6 py-3 whitespace-nowrap">Return Date</th>
                            <th class="px-6 py-3 whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse($borrows as $borrow)
                            @php
                                $user = $borrow->user;
                                $userInfo = $user->userInfo;
                                $isOverdue = $borrow->status === 'overdue' || ($borrow->status === 'borrowed' && $borrow->due_date < now()->startOfDay());
                                $userType = $user->role->name ?? 'Unknown';
                            @endphp
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($borrow->book->image_path)
                                            <img src="{{ asset('storage/' . $borrow->book->image_path) }}" alt="{{ $borrow->book->book_name }}" class="w-12 h-16 object-cover rounded">
                                        @else
                                            <div class="w-12 h-16 bg-[#f3cbe0] rounded flex items-center justify-center">
                                                <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold">{{ $borrow->book->book_name }}</div>
                                            <div class="text-xs text-[#7c4c63]">{{ $borrow->book->isbn }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-semibold">{{ $userInfo->full_name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-[#7c4c63]">{{ $user->email }}</div>
                                        @if($userInfo)
                                            <div class="text-xs text-[#7c4c63]">
                                                @if($userType === 'Student')
                                                    LRN: {{ $userInfo->lrn ?? '—' }}
                                                @elseif($userType === 'Teacher')
                                                    Emp. No.: {{ $userInfo->employee_number ?? '—' }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 py-1 text-xs font-semibold rounded-full',
                                        'bg-blue-50 text-blue-700' => $userType === 'Student',
                                        'bg-purple-50 text-purple-700' => $userType === 'Teacher',
                                    ])>
                                        {{ $userType }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'font-semibold',
                                        'text-rose-700' => $isOverdue,
                                        'text-[#4b2036]' => !$isOverdue,
                                    ])>
                                        {{ $borrow->due_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($borrow->return_date)
                                        <span class="text-green-700 font-semibold">{{ $borrow->return_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-[#7c4c63]">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($borrow->status === 'returned')
                                        <span class="bg-green-50 text-green-700 px-2 py-1 text-xs font-semibold rounded-full">Returned</span>
                                    @elseif($isOverdue)
                                        <span class="bg-rose-50 text-rose-700 px-2 py-1 text-xs font-semibold rounded-full">Overdue</span>
                                    @else
                                        <span class="bg-blue-50 text-blue-700 px-2 py-1 text-xs font-semibold rounded-full">Borrowed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($borrow->status !== 'returned')
                                        <button
                                            type="button"
                                            @click.stop="openReturnConfirm({{ $borrow->id }})"
                                            class="inline-flex items-center gap-1.5 rounded-[10px] bg-[#a03464] px-4 py-2 text-xs font-semibold text-white hover:bg-[#821a4f] transition cursor-pointer"
                                            title="Mark as returned"
                                        >
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            <span>Return</span>
                                        </button>
                                    @else
                                        <span class="text-xs text-[#7c4c63]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-6 text-center text-sm text-[#7c4c63]">
                                    No borrows found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#f3cbe0] px-6 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-xs text-[#7c4c63]">
                    <p class="leading-tight">
                        @if ($borrows->total())
                            Showing {{ $borrows->firstItem() }} to {{ $borrows->lastItem() }} of {{ $borrows->total() }} borrows
                        @else
                            Showing 0 to 0 of 0 borrows
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ $borrows->previousPageUrl() ?: '#' }}"
                            class="rounded-full border border-[#f3cbe0] px-3 py-1 text-xs font-semibold text-[#a03464] {{ $borrows->previousPageUrl() ? 'hover:bg-[#fff2f8]' : 'opacity-60 cursor-not-allowed' }}"
                            @if(!$borrows->previousPageUrl()) aria-disabled="true" @endif
                        >
                            Previous
                        </a>
                        <span class="text-[#a03464] font-semibold">{{ $borrows->currentPage() }}</span>
                        <a
                            href="{{ $borrows->nextPageUrl() ?: '#' }}"
                            class="rounded-full border border-[#f3cbe0] px-3 py-1 text-xs font-semibold text-[#a03464] {{ $borrows->nextPageUrl() ? 'hover:bg-[#fff2f8]' : 'opacity-60 cursor-not-allowed' }}"
                            @if(!$borrows->nextPageUrl()) aria-disabled="true" @endif
                        >
                            Next
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Confirmation Modal --}}
        <div
            x-cloak
            x-show="showReturnConfirmModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[1400] flex items-center justify-center bg-black/40 px-4 py-8"
            @keydown.escape.window="closeReturnConfirm()"
        >
            <div
                x-on:click.away="closeReturnConfirm()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                class="w-full max-w-md rounded-[24px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0]"
            >
                <div class="px-6 py-5 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Verify Book Return</h2>
                        <button
                            type="button"
                            @click="closeReturnConfirm()"
                            :disabled="isReturning"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">
                    {{-- Message --}}
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-[#4b2036] leading-relaxed">
                                Are you sure you want to mark this book as returned? This action will verify the book return and update the status.
                            </p>
                        </div>
                    </div>

                    {{-- Success/Error Message --}}
                    <div
                        x-show="returnMessage"
                        x-cloak
                        x-transition
                        :class="returnMessageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
                        class="p-3 rounded-[10px] border text-sm"
                    >
                        <p x-text="returnMessage"></p>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-[#f3cbe0] flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeReturnConfirm()"
                        :disabled="isReturning"
                        class="rounded-[12px] border border-[#f3cbe0] px-6 py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="confirmReturn()"
                        :disabled="isReturning"
                        class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#a03464] px-6 py-2 text-sm font-semibold text-white hover:bg-[#821a4f] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            x-show="isReturning"
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <span x-text="isReturning ? 'Verifying...' : 'Return'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
@endsection

