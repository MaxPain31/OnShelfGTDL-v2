@extends('layout.admin.app')

@section('title', 'Reports | OnShelf GTDL')
@section('page_title', 'Reports')

@section('content')
    <div
        x-data="{}"
        x-init="
            if (window.lucide) { lucide.createIcons(); }
            // Initialize Chart.js
            if (typeof Chart !== 'undefined') {
                // Monthly Borrows and Returns Chart
                const monthlyCtx = document.getElementById('monthlyBorrowsReturnsChart');
                if (monthlyCtx) {
                    new Chart(monthlyCtx, {
                        type: 'line',
                        data: {
                            labels: @js($monthlyBorrowsLabels),
                            datasets: [{
                                label: 'Borrows',
                                data: @js($monthlyBorrowsData),
                                borderColor: '#a03464',
                                backgroundColor: 'rgba(160, 52, 100, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#a03464',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }, {
                                label: 'Returns',
                                data: @js($monthlyReturnsData),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { color: 'rgba(243, 203, 224, 0.3)' }
                                },
                                x: {
                                    ticks: {
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }

                // Reservations Chart
                const reservationsCtx = document.getElementById('reservationsChart');
                if (reservationsCtx) {
                    new Chart(reservationsCtx, {
                        type: 'bar',
                        data: {
                            labels: @js($monthlyBorrowsLabels),
                            datasets: [{
                                label: 'Reservations',
                                data: @js($monthlyReservationsData),
                                backgroundColor: 'rgba(249, 199, 79, 0.8)',
                                borderColor: '#f9c74f',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { color: 'rgba(243, 203, 224, 0.3)' }
                                },
                                x: {
                                    ticks: {
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }

                // Daily Activity Chart
                const dailyCtx = document.getElementById('dailyActivityChart');
                if (dailyCtx) {
                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: @js($dailyActivityLabels),
                            datasets: [{
                                label: 'Daily Borrows',
                                data: @js($dailyActivityData),
                                borderColor: '#6ddccf',
                                backgroundColor: 'rgba(109, 220, 207, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 2,
                                pointHoverRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#7c4c63',
                                        font: { size: 10 }
                                    },
                                    grid: { color: 'rgba(243, 203, 224, 0.3)' }
                                },
                                x: {
                                    ticks: {
                                        color: '#7c4c63',
                                        font: { size: 10 },
                                        maxRotation: 45,
                                        minRotation: 45
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }

                // User Registrations Chart
                const registrationsCtx = document.getElementById('registrationsChart');
                if (registrationsCtx) {
                    new Chart(registrationsCtx, {
                        type: 'bar',
                        data: {
                            labels: @js($monthlyBorrowsLabels),
                            datasets: [{
                                label: 'New Users',
                                data: @js($monthlyRegistrationsData),
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: '#3b82f6',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { color: 'rgba(243, 203, 224, 0.3)' }
                                },
                                x: {
                                    ticks: {
                                        color: '#7c4c63',
                                        font: { size: 11 }
                                    },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }

                // Borrow Status Distribution
                const borrowStatusCtx = document.getElementById('borrowStatusChart');
                if (borrowStatusCtx) {
                    const statusData = @js($borrowStatusDistribution);
                    new Chart(borrowStatusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                            datasets: [{
                                data: Object.values(statusData),
                                backgroundColor: [
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(249, 199, 79, 0.8)',
                                    'rgba(59, 130, 246, 0.8)'
                                ],
                                borderColor: [
                                    '#10b981',
                                    '#ef4444',
                                    '#f9c74f',
                                    '#3b82f6'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: { size: 12 }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                }

                // Reservation Status Distribution
                const reservationStatusCtx = document.getElementById('reservationStatusChart');
                if (reservationStatusCtx) {
                    const resStatusData = @js($reservationStatusDistribution);
                    new Chart(reservationStatusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(resStatusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                            datasets: [{
                                data: Object.values(resStatusData),
                                backgroundColor: [
                                    'rgba(249, 199, 79, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(107, 114, 128, 0.8)'
                                ],
                                borderColor: [
                                    '#f9c74f',
                                    '#10b981',
                                    '#6b7280'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: { size: 12 }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(75, 32, 54, 0.9)',
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                }
            }
        "
        class="space-y-8"
    >
        {{-- Overall Statistics --}}
        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-[#7c4c63]">Total Books</p>
                        <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $totalBooks }}</p>
                        <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">In Library</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#6ddccf]/20">
                        <i data-lucide="book-open" class="w-6 h-6 text-[#6ddccf]"></i>
                    </div>
                </div>
            </article>
            <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-[#7c4c63]">Total E-Books</p>
                        <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $totalEbooks }}</p>
                        <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">Digital</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#3b82f6]/20">
                        <i data-lucide="file-text" class="w-6 h-6 text-[#3b82f6]"></i>
                    </div>
                </div>
            </article>
            <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-[#7c4c63]">Total Members</p>
                        <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $totalMembers }}</p>
                        <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">All Users</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#8b5cf6]/20">
                        <i data-lucide="users" class="w-6 h-6 text-[#8b5cf6]"></i>
                    </div>
                </div>
            </article>
            <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-[#7c4c63]">Total Borrows</p>
                        <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $totalBorrows }}</p>
                        <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">All Time</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#10b981]/20">
                        <i data-lucide="book-copy" class="w-6 h-6 text-[#10b981]"></i>
                    </div>
                </div>
            </article>
            <article class="rounded-[20px] border border-[#f3cbe0] bg-white shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-[#7c4c63]">Books Returned</p>
                        <p class="mt-3 text-3xl font-semibold text-[#4b2036]">{{ $totalReturned }}</p>
                        <p class="mt-2 text-xs font-medium text-[#a03464] uppercase tracking-wide">Completed</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-[#6ddccf]/20">
                        <i data-lucide="check-circle" class="w-6 h-6 text-[#6ddccf]"></i>
                    </div>
                </div>
            </article>
        </section>

        {{-- Year Comparison --}}
        <section class="grid gap-6 md:grid-cols-2">
            <article class="rounded-[24px] border border-[#f3cbe0] bg-white shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Year Comparison</p>
                        <h2 class="text-xl font-semibold text-[#4b2036]">Borrows Growth</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-[#7c4c63]">This Year</p>
                        <p class="text-2xl font-bold text-[#4b2036]">{{ $thisYearBorrows }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-[#7c4c63]">Last Year</p>
                        <p class="text-2xl font-bold text-[#4b2036]">{{ $lastYearBorrows }}</p>
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold {{ $borrowGrowth >= 0 ? 'text-green-600' : 'text-rose-600' }}">
                                {{ $borrowGrowth >= 0 ? '+' : '' }}{{ $borrowGrowth }}%
                            </span>
                            <i data-lucide="{{ $borrowGrowth >= 0 ? 'trending-up' : 'trending-down' }}" class="w-4 h-4 {{ $borrowGrowth >= 0 ? 'text-green-600' : 'text-rose-600' }}"></i>
                        </div>
                    </div>
                </div>
            </article>
            <article class="rounded-[24px] border border-[#f3cbe0] bg-white shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Year Comparison</p>
                        <h2 class="text-xl font-semibold text-[#4b2036]">Returns Growth</h2>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-[#7c4c63]">This Year</p>
                        <p class="text-2xl font-bold text-[#4b2036]">{{ $thisYearReturns }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-[#7c4c63]">Last Year</p>
                        <p class="text-2xl font-bold text-[#4b2036]">{{ $lastYearReturns }}</p>
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold {{ $returnGrowth >= 0 ? 'text-green-600' : 'text-rose-600' }}">
                                {{ $returnGrowth >= 0 ? '+' : '' }}{{ $returnGrowth }}%
                            </span>
                            <i data-lucide="{{ $returnGrowth >= 0 ? 'trending-up' : 'trending-down' }}" class="w-4 h-4 {{ $returnGrowth >= 0 ? 'text-green-600' : 'text-rose-600' }}"></i>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        {{-- Charts Section --}}
        <section class="grid gap-6 lg:grid-cols-2">
            {{-- Monthly Borrows and Returns --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Trends</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Monthly Borrows & Returns</h2>
                </div>
                <div class="h-64">
                    <canvas id="monthlyBorrowsReturnsChart"></canvas>
                </div>
            </div>

            {{-- Reservations Trend --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Trends</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Monthly Reservations</h2>
                </div>
                <div class="h-64">
                    <canvas id="reservationsChart"></canvas>
                </div>
            </div>

            {{-- Daily Activity --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Activity</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Daily Borrows (Last 30 Days)</h2>
                </div>
                <div class="h-64">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>

            {{-- User Registrations --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Users</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">New User Registrations</h2>
                </div>
                <div class="h-64">
                    <canvas id="registrationsChart"></canvas>
                </div>
            </div>
        </section>

        {{-- Status Distributions --}}
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Distribution</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Borrow Status</h2>
                </div>
                <div class="h-64">
                    <canvas id="borrowStatusChart"></canvas>
                </div>
            </div>
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Distribution</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Reservation Status</h2>
                </div>
                <div class="h-64">
                    <canvas id="reservationStatusChart"></canvas>
                </div>
            </div>
        </section>

        {{-- Student Leaderboard --}}
        <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Leaderboard</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Top 10 Student Readers</h2>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Rank</th>
                            <th class="px-4 py-3 whitespace-nowrap">Student Name</th>
                            <th class="px-4 py-3 whitespace-nowrap">LRN</th>
                            <th class="px-4 py-3 whitespace-nowrap">Books Read</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse($topStudents as $index => $student)
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($index < 3)
                                            <span class="text-lg font-bold {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-gray-400' : 'text-amber-600') }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <i data-lucide="award" class="w-4 h-4 {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-gray-400' : 'text-amber-600') }}"></i>
                                        @else
                                            <span class="font-semibold">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-semibold">
                                    {{ html_entity_decode($student->userInfo->full_name ?? $student->email) }}
                                </td>
                                <td class="px-4 py-3">{{ $student->userInfo->lrn ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#a03464]">{{ $student->borrows_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-[#7c4c63]">
                                    No student data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Teacher Statistics --}}
        <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Teacher Activity</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Top 10 Teacher Readers</h2>
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div class="bg-[#fff7fb] rounded-[14px] p-4 border border-[#f3cbe0]">
                    <p class="text-sm text-[#7c4c63]">Total Borrows</p>
                    <p class="text-2xl font-bold text-[#4b2036]">{{ $teacherBorrows }}</p>
                </div>
                <div class="bg-[#fff7fb] rounded-[14px] p-4 border border-[#f3cbe0]">
                    <p class="text-sm text-[#7c4c63]">Books Returned</p>
                    <p class="text-2xl font-bold text-[#4b2036]">{{ $teacherReturns }}</p>
                </div>
                <div class="bg-[#fff7fb] rounded-[14px] p-4 border border-[#f3cbe0]">
                    <p class="text-sm text-[#7c4c63]">Reservations</p>
                    <p class="text-2xl font-bold text-[#4b2036]">{{ $teacherReservations }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Rank</th>
                            <th class="px-4 py-3 whitespace-nowrap">Teacher Name</th>
                            <th class="px-4 py-3 whitespace-nowrap">Employee #</th>
                            <th class="px-4 py-3 whitespace-nowrap">Books Read</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse($topTeachers as $index => $teacher)
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-4 py-3">
                                    <span class="font-semibold">{{ $index + 1 }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold">
                                    {{ html_entity_decode($teacher->userInfo->full_name ?? $teacher->email) }}
                                </td>
                                <td class="px-4 py-3">{{ $teacher->userInfo->employee_number ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#a03464]">{{ $teacher->borrows_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-[#7c4c63]">
                                    No teacher data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Popular Books --}}
        <section class="grid gap-6 lg:grid-cols-3">
            {{-- Most Borrowed --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Popular Books</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Most Borrowed</h2>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($topBorrowedBooks as $index => $book)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#fff7fb] border border-[#f3cbe0]">
                            <div class="w-8 h-8 rounded-full bg-[#a03464]/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-[#a03464]">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-[#4b2036] truncate">{{ html_entity_decode($book->book_name) }}</p>
                                <p class="text-xs text-[#7c4c63]">{{ $book->borrows_count }} borrows</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#7c4c63] text-center py-4">No data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Most Viewed --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Popular Books</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Most Viewed</h2>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($topViewedBooks as $index => $book)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#fff7fb] border border-[#f3cbe0]">
                            <div class="w-8 h-8 rounded-full bg-[#3b82f6]/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-[#3b82f6]">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-[#4b2036] truncate">{{ html_entity_decode($book->book_name) }}</p>
                                <p class="text-xs text-[#7c4c63]">{{ $book->view_count }} views</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#7c4c63] text-center py-4">No data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Most Favorited --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Popular Books</p>
                    <h2 class="text-xl font-semibold text-[#4b2036]">Most Favorited</h2>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($topFavoritedBooks as $index => $book)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-[#fff7fb] border border-[#f3cbe0]">
                            <div class="w-8 h-8 rounded-full bg-[#f9c74f]/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-[#f9c74f]">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-[#4b2036] truncate">{{ html_entity_decode($book->book_name) }}</p>
                                <p class="text-xs text-[#7c4c63]">{{ $book->favorite_count }} favorites</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#7c4c63] text-center py-4">No data available.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Category Statistics --}}
        <section class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.3em] text-[#a03464]/60">Library Analysis</p>
                <h2 class="text-xl font-semibold text-[#4b2036]">Books by Category</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Category</th>
                            <th class="px-4 py-3 whitespace-nowrap">Total Books</th>
                            <th class="px-4 py-3 whitespace-nowrap">Total Stock</th>
                            <th class="px-4 py-3 whitespace-nowrap">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @php
                            $totalBooksInCategories = $categoryStats->sum('total');
                        @endphp
                        @forelse($categoryStats as $category)
                            @php
                                $percentage = $totalBooksInCategories > 0 ? round(($category->total / $totalBooksInCategories) * 100, 1) : 0;
                            @endphp
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-4 py-3 font-semibold">{{ $category->category ?? 'Uncategorized' }}</td>
                                <td class="px-4 py-3">{{ $category->total }}</td>
                                <td class="px-4 py-3">{{ $category->total_stock }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-[#f3cbe0] rounded-full h-2 max-w-[100px]">
                                            <div class="bg-[#a03464] h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold">{{ $percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-[#7c4c63]">
                                    No category data available.
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

