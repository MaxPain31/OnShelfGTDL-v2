@extends('layout.student.app')

@section('title', 'Borrowed Books | OnShelf GTDL')
@section('page_title', 'Borrowed Books')

@section('content')
    <div
        x-data="{}"
        x-init="if (window.lucide) { lucide.createIcons(); }"
    >
        <div class="space-y-6">
            {{-- Active Borrows --}}
            @if($activeBorrows->count() > 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                    <div class="border-b border-[#f3cbe0] px-6 py-4">
                        <h2 class="text-lg font-semibold text-[#4b2036]">Active Borrows</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-[#4b2036]">
                            <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                                <tr>
                                    <th class="px-6 py-3 whitespace-nowrap">Book</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Borrow Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Due Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f7d6e6]">
                                @foreach($activeBorrows as $borrow)
                                    @php
                                        $isOverdue = $borrow->due_date < now()->startOfDay();
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
                                                    <div class="text-xs text-[#7c4c63]">{{ $borrow->book->authors_name }}</div>
                                                </div>
                                            </div>
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
                                            @if($isOverdue || $borrow->status === 'overdue')
                                                <span class="bg-rose-50 text-rose-700 px-2 py-1 text-xs font-semibold rounded-full">Overdue</span>
                                            @else
                                                <span class="bg-blue-50 text-blue-700 px-2 py-1 text-xs font-semibold rounded-full">Borrowed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-12 text-center">
                    <i data-lucide="book-copy" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                    <h2 class="text-xl font-semibold text-[#4b2036] mb-2">No Active Borrows</h2>
                    <p class="text-sm text-[#7c4c63]">You don't have any active book borrows at the moment.</p>
                </div>
            @endif

            {{-- Returned Borrows --}}
            @if($returnedBorrows->count() > 0)
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                    <div class="border-b border-[#f3cbe0] px-6 py-4">
                        <h2 class="text-lg font-semibold text-[#4b2036]">Returned Books</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-[#4b2036]">
                            <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                                <tr>
                                    <th class="px-6 py-3 whitespace-nowrap">Book</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Borrow Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Due Date</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Return Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f7d6e6]">
                                @foreach($returnedBorrows as $borrow)
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
                                                    <div class="text-xs text-[#7c4c63]">{{ $borrow->book->authors_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $borrow->due_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-green-50 text-green-700 px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ $borrow->return_date->format('M d, Y') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
