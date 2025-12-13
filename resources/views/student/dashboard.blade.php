@extends('layout.student.app')

@section('title', 'Student Dashboard | OnShelf GTDL')
@section('page_title', 'Dashboard')

@section('content')
    <div
        x-data="{}"
        x-init="
            if (window.lucide) { lucide.createIcons(); }
            // Initialize Chart.js
            if (typeof Chart !== 'undefined') {
                const ctx = document.getElementById('readingHistoryChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @js($chartLabels),
                            datasets: [{
                                label: 'Books Returned',
                                data: @js($chartData),
                                borderColor: '#a03464',
                                backgroundColor: 'rgba(160, 52, 100, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#a03464',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#7c4c63',
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(243, 203, 224, 0.3)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#7c4c63',
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            }
        "
        class="space-y-8"
    >
        {{-- Statistics Cards --}}
        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($stats as $card)
                <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-[#7c4c63]">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $card['value'] }}</p>
                            <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">{{ $card['trend'] }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background-color: {{ $card['color'] }}20;">
                            <i data-lucide="{{ $card['icon'] }}" class="w-6 h-6" style="color: {{ $card['color'] }};"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Charts and Quick Actions --}}
        <section class="grid gap-6 lg:grid-cols-3">
            {{-- Reading History Chart --}}
            <div class="lg:col-span-2 rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Reading Activity</p>
                        <h2 class="text-xl font-semibold text-[#4b2036]">My Reading History</h2>
                    </div>
                    <a href="{{ route('student.borrowed-books') }}" class="rounded-full border border-[#f3cbe0] px-4 py-2 text-sm text-[#a03464] hover:bg-[#fff2f8] transition">
                        View All
                    </a>
                </div>
                <div class="h-64">
                    <canvas id="readingHistoryChart"></canvas>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Quick Actions</p>
                <h2 class="mt-2 text-xl font-semibold text-[#4b2036]">Shortcuts</h2>
                <div class="mt-6 space-y-3">
                    <a
                        href="{{ route('student.books') }}"
                        class="flex items-center gap-3 w-full rounded-[14px] border border-[#f3cbe0] px-4 py-3 text-left text-sm text-[#4b2036] hover:bg-[#fff2f8] transition"
                    >
                        <i data-lucide="search" class="w-4 h-4 text-[#a03464]"></i>
                        <span>Browse Books</span>
                    </a>
                    <a
                        href="{{ route('student.my-shelf') }}"
                        class="flex items-center gap-3 w-full rounded-[14px] border border-[#f3cbe0] px-4 py-3 text-left text-sm text-[#4b2036] hover:bg-[#fff2f8] transition"
                    >
                        <i data-lucide="book-open" class="w-4 h-4 text-[#a03464]"></i>
                        <span>View My Shelf</span>
                    </a>
                    <a
                        href="{{ route('student.borrowed-books') }}"
                        class="flex items-center gap-3 w-full rounded-[14px] border border-[#f3cbe0] px-4 py-3 text-left text-sm text-[#4b2036] hover:bg-[#fff2f8] transition"
                    >
                        <i data-lucide="book-check" class="w-4 h-4 text-[#a03464]"></i>
                        <span>Check Borrowed Books</span>
                    </a>
                    <a
                        href="{{ route('student.reserved-books') }}"
                        class="flex items-center gap-3 w-full rounded-[14px] border border-[#f3cbe0] px-4 py-3 text-left text-sm text-[#4b2036] hover:bg-[#fff2f8] transition"
                    >
                        <i data-lucide="bookmark" class="w-4 h-4 text-[#a03464]"></i>
                        <span>Reserved Books</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Recently Added Books --}}
        @if($recentlyAddedBooks->count() > 0)
            <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Recently Added</p>
                        <h2 class="text-xl font-semibold text-[#4b2036]">New Books Available</h2>
                    </div>
                    <a href="{{ route('student.books') }}" class="text-sm font-semibold text-[#a03464] hover:underline">View all</a>
                </div>
                <div class="grid gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach ($recentlyAddedBooks as $book)
                        <article class="group relative flex flex-col gap-2 items-center text-center">
                            <a
                                href="{{ route('student.books') }}?book={{ $book->id }}"
                                class="relative rounded-[10px] bg-[#fde7f0] border border-[#f3cbe0] overflow-hidden w-full max-w-[180px] mx-auto group-hover:scale-105 transition-transform duration-200 ease-out cursor-pointer"
                                style="aspect-ratio: 2 / 3;"
                            >
                                @if($book->image_path)
                                    <img
                                        src="{{ asset('storage/' . $book->image_path) }}"
                                        alt="{{ html_entity_decode($book->book_name) }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i data-lucide="book-open" class="w-12 h-12 text-[#a03464]/60"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200"></div>
                            </a>
                            <div class="w-full max-w-[180px]">
                                <h3 class="font-semibold text-[#4b2036] text-sm line-clamp-2 min-h-[2.5rem]">
                                    <a href="{{ route('student.books') }}?book={{ $book->id }}" class="hover:text-[#a03464] transition">
                                        {{ html_entity_decode($book->book_name) }}
                                    </a>
                                </h3>
                                <p class="text-xs text-[#7c4c63] mt-1 line-clamp-1">
                                    {{ html_entity_decode($book->authors_name ?? 'Unknown Author') }}
                                </p>
                                @if($book->category)
                                    <p class="text-xs text-[#a03464] mt-1">{{ $book->category }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Recent Activity --}}
        @if($recentBorrows->count() > 0)
            <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Recent Activity</p>
                        <h2 class="text-xl font-semibold text-[#4b2036]">My Recent Borrows</h2>
                    </div>
                    <a href="{{ route('student.borrowed-books') }}" class="text-sm font-semibold text-[#a03464] hover:underline">View all</a>
                </div>
                <div class="space-y-3">
                    @foreach ($recentBorrows as $borrow)
                        <div class="flex items-center gap-4 p-4 rounded-[14px] border border-[#f3cbe0] hover:bg-[#fff7fb] transition">
                            <div class="w-12 h-16 bg-[#fde7f0] rounded flex items-center justify-center flex-shrink-0">
                                @if($borrow->book->image_path)
                                    <img src="{{ asset('storage/' . $borrow->book->image_path) }}" alt="{{ $borrow->book->book_name }}" class="w-full h-full object-cover rounded">
                                @else
                                    <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-[#4b2036]">{{ html_entity_decode($borrow->book->book_name) }}</h3>
                                <p class="text-xs text-[#7c4c63] mt-1">
                                    Borrowed: {{ $borrow->borrow_date->format('M d, Y') }} •
                                    Due: {{ $borrow->due_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                @if($borrow->status === 'returned')
                                    <span class="bg-green-50 text-green-700 px-3 py-1 text-xs font-semibold rounded-full">Returned</span>
                                @elseif($borrow->status === 'overdue')
                                    <span class="bg-rose-50 text-rose-700 px-3 py-1 text-xs font-semibold rounded-full">Overdue</span>
                                @else
                                    <span class="bg-amber-50 text-amber-700 px-3 py-1 text-xs font-semibold rounded-full">Borrowed</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection
