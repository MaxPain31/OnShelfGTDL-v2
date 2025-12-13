@extends('layout.admin.app')

@section('title', 'Admin Dashboard | OnShelf GTDL')
@section('page_title', 'Dashboard')

@section('content')
    <div
        x-data="{}"
        x-init="
            if (window.lucide) { lucide.createIcons(); }
            // Initialize Chart.js
            if (typeof Chart !== 'undefined') {
                // Student Reading History Chart
                const studentCtx = document.getElementById('studentReadingHistoryChart');
                if (studentCtx) {
                    new Chart(studentCtx, {
                        type: 'line',
                        data: {
                            labels: @js($studentChartLabels),
                            datasets: [{
                                label: 'Books Returned',
                                data: @js($studentChartData),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#3b82f6',
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

                // Student Registration Chart
                const studentRegistrationCtx = document.getElementById('studentRegistrationChart');
                if (studentRegistrationCtx) {
                    new Chart(studentRegistrationCtx, {
                        type: 'bar',
                        data: {
                            labels: @js($studentRegistrationChartLabels),
                            datasets: [{
                                label: 'New Students',
                                data: @js($studentRegistrationChartData),
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: '#10b981',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
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
        {{-- General Statistics Section --}}
        <section class="space-y-6">
            <div class="flex items-center gap-3 pb-2 border-b-2 border-[#f3cbe0]">
                <i data-lucide="bar-chart-3" class="w-6 h-6 text-[#a03464]"></i>
                <h2 class="text-2xl font-bold text-[#4b2036]">General Statistics</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
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
            </div>
        </section>

        {{-- Student Statistics Section --}}
        <section class="space-y-6">
            <div class="flex items-center gap-3 pb-2 border-b-2 border-[#f3cbe0]">
                <i data-lucide="users" class="w-6 h-6 text-[#a03464]"></i>
                <h2 class="text-2xl font-bold text-[#4b2036]">Student Statistics</h2>
            </div>

            {{-- Student Statistics Cards --}}
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                @foreach ($studentStats as $card)
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
            </div>

            {{-- Student Charts --}}
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- Student Reading History Chart --}}
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Student Activity</p>
                            <h2 class="text-xl font-semibold text-[#4b2036]">Student Reading History</h2>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="studentReadingHistoryChart"></canvas>
                    </div>
                </div>

                {{-- Student Registration Chart --}}
                <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Student Management</p>
                            <h2 class="text-xl font-semibold text-[#4b2036]">New Student Registrations</h2>
                        </div>
                        <a href="{{ route('admin.manage-students') }}" class="rounded-full border border-[#f3cbe0] px-4 py-2 text-sm text-[#a03464] hover:bg-[#fff2f8] transition">
                            Manage
                        </a>
                    </div>
                    <div class="h-64">
                        <canvas id="studentRegistrationChart"></canvas>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[#f3cbe0] grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-xs text-[#7c4c63] mb-1">Active Students</p>
                            <p class="text-2xl font-bold text-green-600">{{ $activeStudents }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-[#7c4c63] mb-1">Inactive Students</p>
                            <p class="text-2xl font-bold text-rose-600">{{ $inactiveStudents }}</p>
                        </div>
                    </div>
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
                    <a href="{{ route('admin.manage-books') }}" class="text-sm font-semibold text-[#a03464] hover:underline">View all</a>
                </div>
                <div class="grid gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach ($recentlyAddedBooks as $book)
                        <article class="group relative flex flex-col gap-2 items-center text-center">
                            <a
                                href="{{ route('admin.manage-books') }}?book={{ $book->id }}"
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
                                    <a href="{{ route('admin.manage-books') }}?book={{ $book->id }}" class="hover:text-[#a03464] transition">
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

        {{-- Latest Borrow Transactions --}}
        <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Recently Active</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Latest Borrow Transactions</h2>
                </div>
                <a href="{{ route('admin.manage-borrows') }}" class="text-sm font-semibold text-[#a03464] hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Borrower</th>
                            <th class="px-4 py-3 whitespace-nowrap">Book</th>
                            <th class="px-4 py-3 whitespace-nowrap">Borrowed</th>
                            <th class="px-4 py-3 whitespace-nowrap">Due Date</th>
                            <th class="px-4 py-3 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse($recentBorrows as $borrow)
                            @php
                                $borrowerName = $borrow->user->userInfo->full_name ?? $borrow->user->email;
                                $userType = $borrow->user->isStudent() ? 'Student' : ($borrow->user->isTeacher() ? 'Teacher' : 'User');
                            @endphp
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold">{{ $borrowerName }}</p>
                                        <p class="text-xs text-[#7c4c63]">{{ $userType }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ html_entity_decode($borrow->book->book_name) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $borrow->borrow_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $borrow->due_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($borrow->status === 'returned')
                                        <span class="bg-green-50 text-green-700 px-3 py-1 text-xs font-semibold rounded-full">Returned</span>
                                    @elseif($borrow->status === 'overdue')
                                        <span class="bg-rose-50 text-rose-700 px-3 py-1 text-xs font-semibold rounded-full">Overdue</span>
                                    @else
                                        <span class="bg-amber-50 text-amber-700 px-3 py-1 text-xs font-semibold rounded-full">Borrowed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-[#7c4c63]">
                                    No borrow transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection
