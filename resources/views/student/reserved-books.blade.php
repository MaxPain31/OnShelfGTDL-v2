@extends('layout.student.app')

@section('title', 'Reserved Books | OnShelf GTDL')
@section('page_title', 'Reserved Books')

@section('content')
    <div
        x-data="{}"
        x-init="if (window.lucide) { lucide.createIcons(); }"
    >
        <div class="space-y-6">
            {{-- Active Reservations --}}
            @if($activeReservations->count() > 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                    <div class="border-b border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                        <h2 class="text-base sm:text-lg font-semibold text-[#4b2036]">Active Reservations</h2>
                    </div>
                    {{-- Mobile & Tablet Card Layout --}}
                    <div class="lg:hidden space-y-3 px-3 py-4">
                        @foreach($activeReservations as $reservation)
                            @php
                                $isExpired = $reservation->status === 'pending' && $reservation->claim_deadline < now()->startOfDay();
                            @endphp
                            <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    @if($reservation->book->image_path)
                                        <img src="{{ asset('storage/' . $reservation->book->image_path) }}" alt="{{ $reservation->book->book_name }}" class="w-16 h-20 object-cover rounded flex-shrink-0">
                                    @else
                                        <div class="w-16 h-20 bg-[#f3cbe0] rounded flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm text-[#4b2036] truncate">{{ $reservation->book->book_name }}</h3>
                                        <p class="text-xs text-[#7c4c63] mt-0.5">{{ $reservation->book->authors_name }}</p>
                                    </div>
                                    @if($reservation->status === 'claimed')
                                        <span class="bg-green-50 text-green-700 px-2.5 py-1 text-xs font-semibold rounded-full flex-shrink-0">Claimed</span>
                                    @elseif($isExpired)
                                        <span class="bg-rose-50 text-rose-700 px-2.5 py-1 text-xs font-semibold rounded-full flex-shrink-0">Expired</span>
                                    @else
                                        <span class="bg-amber-50 text-amber-700 px-2.5 py-1 text-xs font-semibold rounded-full flex-shrink-0">Pending</span>
                                    @endif
                                </div>
                                <div class="space-y-2 text-xs pt-2 border-t border-[#f7d6e6]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Reserve Date:</span>
                                        <span class="text-[#4b2036]">{{ $reservation->reserve_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Due Date:</span>
                                        <span class="text-[#4b2036]">{{ $reservation->due_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Claim Deadline:</span>
                                        <span @class([
                                            'font-semibold',
                                            'text-rose-700' => $isExpired,
                                            'text-amber-700' => !$isExpired && $reservation->status === 'pending',
                                            'text-[#4b2036]' => $reservation->status === 'claimed',
                                        ])>
                                            {{ $reservation->claim_deadline->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Desktop Table Layout --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-[#4b2036]">
                            <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                                <tr>
                                    <th class="px-6 py-3 whitespace-nowrap">Book</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Reserve Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Due Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Claim Deadline</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f7d6e6]">
                                @foreach($activeReservations as $reservation)
                                    @php
                                        $isExpired = $reservation->status === 'pending' && $reservation->claim_deadline < now()->startOfDay();
                                    @endphp
                                    <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($reservation->book->image_path)
                                                    <img src="{{ asset('storage/' . $reservation->book->image_path) }}" alt="{{ $reservation->book->book_name }}" class="w-12 h-16 object-cover rounded">
                                                @else
                                                    <div class="w-12 h-16 bg-[#f3cbe0] rounded flex items-center justify-center">
                                                        <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-sm">{{ $reservation->book->book_name }}</div>
                                                    <div class="text-xs text-[#7c4c63]">{{ $reservation->book->authors_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reservation->reserve_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reservation->due_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span @class([
                                                'font-semibold',
                                                'text-rose-700' => $isExpired,
                                                'text-amber-700' => !$isExpired && $reservation->status === 'pending',
                                                'text-[#4b2036]' => $reservation->status === 'claimed',
                                            ])>
                                                {{ $reservation->claim_deadline->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($reservation->status === 'claimed')
                                                <span class="bg-green-50 text-green-700 px-2 py-1 text-xs font-semibold rounded-full">Claimed</span>
                                            @elseif($isExpired)
                                                <span class="bg-rose-50 text-rose-700 px-2 py-1 text-xs font-semibold rounded-full">Expired</span>
                                            @else
                                                <span class="bg-amber-50 text-amber-700 px-2 py-1 text-xs font-semibold rounded-full">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-8 sm:p-12 text-center">
                    <i data-lucide="bookmark" class="w-12 h-12 sm:w-16 sm:h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                    <h2 class="text-lg sm:text-xl font-semibold text-[#4b2036] mb-2">No Active Reservations</h2>
                    <p class="text-xs sm:text-sm text-[#7c4c63]">You don't have any active book reservations at the moment.</p>
                </div>
            @endif

            {{-- Voided Reservations History --}}
            @if($voidedReservations->count() > 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                    <div class="border-b border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                        <h2 class="text-base sm:text-lg font-semibold text-[#4b2036]">Reservation History</h2>
                    </div>
                    {{-- Mobile & Tablet Card Layout --}}
                    <div class="lg:hidden space-y-3 px-3 py-4">
                        @foreach($voidedReservations as $reservation)
                            <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    @if($reservation->book->image_path)
                                        <img src="{{ asset('storage/' . $reservation->book->image_path) }}" alt="{{ $reservation->book->book_name }}" class="w-16 h-20 object-cover rounded flex-shrink-0">
                                    @else
                                        <div class="w-16 h-20 bg-[#f3cbe0] rounded flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm text-[#4b2036] truncate">{{ $reservation->book->book_name }}</h3>
                                        <p class="text-xs text-[#7c4c63] mt-0.5">{{ $reservation->book->authors_name }}</p>
                                    </div>
                                    <span class="bg-gray-50 text-gray-700 px-2.5 py-1 text-xs font-semibold rounded-full flex-shrink-0">Voided</span>
                                </div>
                                <div class="space-y-2 text-xs pt-2 border-t border-[#f7d6e6]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Reserve Date:</span>
                                        <span class="text-[#4b2036]">{{ $reservation->reserve_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Due Date:</span>
                                        <span class="text-[#4b2036]">{{ $reservation->due_date->format('M d, Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Claim Deadline:</span>
                                        <span class="text-[#4b2036]">{{ $reservation->claim_deadline->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Desktop Table Layout --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-[#4b2036]">
                            <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                                <tr>
                                    <th class="px-6 py-3 whitespace-nowrap">Book</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Reserve Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Due Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Claim Deadline</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f7d6e6]">
                                @foreach($voidedReservations as $reservation)
                                    <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($reservation->book->image_path)
                                                    <img src="{{ asset('storage/' . $reservation->book->image_path) }}" alt="{{ $reservation->book->book_name }}" class="w-12 h-16 object-cover rounded">
                                                @else
                                                    <div class="w-12 h-16 bg-[#f3cbe0] rounded flex items-center justify-center">
                                                        <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-sm">{{ $reservation->book->book_name }}</div>
                                                    <div class="text-xs text-[#7c4c63]">{{ $reservation->book->authors_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reservation->reserve_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reservation->due_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $reservation->claim_deadline->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-gray-50 text-gray-700 px-2 py-1 text-xs font-semibold rounded-full">Voided</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($activeReservations->count() === 0 && $voidedReservations->count() === 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-8 sm:p-12 text-center">
                    <i data-lucide="bookmark" class="w-12 h-12 sm:w-16 sm:h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                    <h2 class="text-lg sm:text-xl font-semibold text-[#4b2036] mb-2">No Reservations</h2>
                    <p class="text-xs sm:text-sm text-[#7c4c63] mb-4">You haven't reserved any books yet.</p>
                    <a
                        href="{{ route('student.books') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Browse Books</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
