<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use App\Models\Ebook;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class ReportsController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(): View
    {
        $this->ensureAdminAccess();

        // Overall Statistics
        $totalBooks = Book::count();
        $totalEbooks = Ebook::count();
        $totalMembers = User::count();
        $totalStudents = User::whereHas('role', fn ($query) => $query->where('name', 'Student'))->count();
        $totalTeachers = User::whereHas('role', fn ($query) => $query->where('name', 'Teacher'))->count();
        $totalBorrows = BookBorrow::count();
        $totalReturned = BookBorrow::where('status', 'returned')->count();
        $totalReservations = BookReservation::count();
        $activeBorrows = BookBorrow::whereIn('status', ['borrowed', 'overdue'])->count();
        $overdueBooks = BookBorrow::where('status', 'overdue')
            ->orWhere(function ($query) {
                $query->where('status', 'borrowed')
                    ->where('due_date', '<', now()->startOfDay());
            })
            ->count();

        // Monthly Borrowing Trends (Last 12 months)
        $monthlyBorrows = BookBorrow::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyBorrowsLabels = [];
        $monthlyBorrowsData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $monthlyBorrowsLabels[] = $monthLabel;
            $monthlyBorrowsData[] = $monthlyBorrows[$month] ?? 0;
        }

        // Monthly Returns Trends
        $monthlyReturns = BookBorrow::where('status', 'returned')
            ->where('return_date', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(return_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyReturnsData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthlyReturnsData[] = $monthlyReturns[$month] ?? 0;
        }

        // Reservation Trends
        $monthlyReservations = BookReservation::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyReservationsData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthlyReservationsData[] = $monthlyReservations[$month] ?? 0;
        }

        // Category-wise Statistics
        $categoryStats = Book::select('category', DB::raw('COUNT(*) as total'), DB::raw('SUM(stock_quantity) as total_stock'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Top 10 Most Borrowed Books
        $topBorrowedBooks = Book::withCount('borrows')
            ->orderByDesc('borrows_count')
            ->limit(10)
            ->get();

        // Top 10 Most Popular Books (by views)
        $topViewedBooks = Book::orderByDesc('view_count')
            ->limit(10)
            ->get();

        // Top 10 Most Favorited Books
        $topFavoritedBooks = Book::orderByDesc('favorite_count')
            ->limit(10)
            ->get();

        // Student Leaderboard (Top Readers)
        $studentRole = Role::where('name', 'Student')->first();
        $studentIds = $studentRole ? User::where('role_id', $studentRole->id)->pluck('id') : collect();

        $topStudents = User::whereIn('id', $studentIds)
            ->withCount(['borrows' => function ($query) {
                $query->where('status', 'returned');
            }])
            ->with('userInfo')
            ->orderByDesc('borrows_count')
            ->limit(10)
            ->get();

        // Teacher Statistics
        $teacherRole = Role::where('name', 'Teacher')->first();
        $teacherIds = $teacherRole ? User::where('role_id', $teacherRole->id)->pluck('id') : collect();

        $teacherBorrows = BookBorrow::whereIn('user_id', $teacherIds)->count();
        $teacherReturns = BookBorrow::whereIn('user_id', $teacherIds)
            ->where('status', 'returned')
            ->count();
        $teacherReservations = BookReservation::whereIn('user_id', $teacherIds)->count();

        // Top Teachers (by borrows)
        $topTeachers = User::whereIn('id', $teacherIds)
            ->withCount(['borrows' => function ($query) {
                $query->where('status', 'returned');
            }])
            ->with('userInfo')
            ->orderByDesc('borrows_count')
            ->limit(10)
            ->get();

        // Daily Activity (Last 30 days)
        $dailyActivity = BookBorrow::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $dailyActivityLabels = [];
        $dailyActivityData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateLabel = Carbon::now()->subDays($i)->format('M d');
            $dailyActivityLabels[] = $dateLabel;
            $dailyActivityData[] = $dailyActivity[$date] ?? 0;
        }

        // Status Distribution
        $borrowStatusDistribution = BookBorrow::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $reservationStatusDistribution = BookReservation::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // This Year vs Last Year Comparison
        $thisYearBorrows = BookBorrow::whereYear('created_at', Carbon::now()->year)->count();
        $lastYearBorrows = BookBorrow::whereYear('created_at', Carbon::now()->subYear()->year)->count();
        $borrowGrowth = $lastYearBorrows > 0
            ? round((($thisYearBorrows - $lastYearBorrows) / $lastYearBorrows) * 100, 2)
            : 0;

        $thisYearReturns = BookBorrow::where('status', 'returned')
            ->whereYear('return_date', Carbon::now()->year)
            ->count();
        $lastYearReturns = BookBorrow::where('status', 'returned')
            ->whereYear('return_date', Carbon::now()->subYear()->year)
            ->count();
        $returnGrowth = $lastYearReturns > 0
            ? round((($thisYearReturns - $lastYearReturns) / $lastYearReturns) * 100, 2)
            : 0;

        // User Registration Trends
        $monthlyRegistrations = User::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyRegistrationsData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthlyRegistrationsData[] = $monthlyRegistrations[$month] ?? 0;
        }

        // Attendance Statistics
        $totalAttendance = Attendance::count();
        $now = Carbon::now('Asia/Manila');
        $todayAttendance = Attendance::whereDate('visit_date', $now->toDateString())->count();
        $thisMonthAttendance = Attendance::whereMonth('visit_date', $now->month)
            ->whereYear('visit_date', $now->year)
            ->count();
        $lastMonth = $now->copy()->subMonth();
        $lastMonthAttendance = Attendance::whereMonth('visit_date', $lastMonth->month)
            ->whereYear('visit_date', $lastMonth->year)
            ->count();

        // Monthly Attendance Trends
        $monthlyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(visit_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyAttendanceLabels = [];
        $monthlyAttendanceData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $monthlyAttendanceLabels[] = $monthLabel;
            $monthlyAttendanceData[] = $monthlyAttendance[$month] ?? 0;
        }

        // Daily Attendance (Last 30 days)
        $dailyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('DATE(visit_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $dailyAttendanceLabels = [];
        $dailyAttendanceData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateLabel = Carbon::now()->subDays($i)->format('M d');
            $dailyAttendanceLabels[] = $dateLabel;
            $dailyAttendanceData[] = $dailyAttendance[$date] ?? 0;
        }

        return view('admin.reports', [
            // Overall Stats
            'totalBooks' => $totalBooks,
            'totalEbooks' => $totalEbooks,
            'totalMembers' => $totalMembers,
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalBorrows' => $totalBorrows,
            'totalReturned' => $totalReturned,
            'totalReservations' => $totalReservations,
            'activeBorrows' => $activeBorrows,
            'overdueBooks' => $overdueBooks,

            // Charts Data
            'monthlyBorrowsLabels' => $monthlyBorrowsLabels,
            'monthlyBorrowsData' => $monthlyBorrowsData,
            'monthlyReturnsData' => $monthlyReturnsData,
            'monthlyReservationsData' => $monthlyReservationsData,
            'dailyActivityLabels' => $dailyActivityLabels,
            'dailyActivityData' => $dailyActivityData,
            'monthlyRegistrationsData' => $monthlyRegistrationsData,

            // Category Stats
            'categoryStats' => $categoryStats,

            // Top Lists
            'topBorrowedBooks' => $topBorrowedBooks,
            'topViewedBooks' => $topViewedBooks,
            'topFavoritedBooks' => $topFavoritedBooks,
            'topStudents' => $topStudents,
            'topTeachers' => $topTeachers,

            // Teacher Stats
            'teacherBorrows' => $teacherBorrows,
            'teacherReturns' => $teacherReturns,
            'teacherReservations' => $teacherReservations,

            // Status Distributions
            'borrowStatusDistribution' => $borrowStatusDistribution,
            'reservationStatusDistribution' => $reservationStatusDistribution,

            // Year Comparison
            'thisYearBorrows' => $thisYearBorrows,
            'lastYearBorrows' => $lastYearBorrows,
            'borrowGrowth' => $borrowGrowth,
            'thisYearReturns' => $thisYearReturns,
            'lastYearReturns' => $lastYearReturns,
            'returnGrowth' => $returnGrowth,

            // Attendance Stats
            'totalAttendance' => $totalAttendance,
            'todayAttendance' => $todayAttendance,
            'thisMonthAttendance' => $thisMonthAttendance,
            'lastMonthAttendance' => $lastMonthAttendance,
            'monthlyAttendanceLabels' => $monthlyAttendanceLabels,
            'monthlyAttendanceData' => $monthlyAttendanceData,
            'dailyAttendanceLabels' => $dailyAttendanceLabels,
            'dailyAttendanceData' => $dailyAttendanceData,
        ]);
    }

    public function export(string $type, string $format)
    {
        $this->ensureAdminAccess();

        $validTypes = [
            'overall-stats',
            'monthly-borrows',
            'monthly-reservations',
            'daily-activity',
            'user-registrations',
            'borrow-status',
            'reservation-status',
            'top-students',
            'top-teachers',
            'top-borrowed-books',
            'top-viewed-books',
            'top-favorited-books',
            'category-stats',
            'year-comparison',
            'monthly-attendance',
            'daily-attendance',
        ];

        $validFormats = ['pdf', 'excel'];

        abort_if(!in_array($type, $validTypes), 404, 'Invalid report type.');
        abort_if(!in_array($format, $validFormats), 404, 'Invalid export format.');

        $data = $this->getReportData($type);
        $title = $this->getReportTitle($type);

        // Transform overall-stats for Excel export
        if ($format === 'excel' && $type === 'overall-stats') {
            $data = array_map(function ($key, $value) {
                return [
                    'metric' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $value,
                ];
            }, array_keys($data), $data);
        }

        if ($format === 'pdf') {
            return $this->exportToPdf($type, $title, $data);
        } else {
            return $this->exportToExcel($type, $title, $data);
        }
    }

    private function getReportData(string $type): array
    {
        $data = [];

        switch ($type) {
            case 'overall-stats':
                $stats = [
                    'totalBooks' => Book::count(),
                    'totalEbooks' => Ebook::count(),
                    'totalMembers' => User::count(),
                    'totalStudents' => User::whereHas('role', fn ($q) => $q->where('name', 'Student'))->count(),
                    'totalTeachers' => User::whereHas('role', fn ($q) => $q->where('name', 'Teacher'))->count(),
                    'totalBorrows' => BookBorrow::count(),
                    'totalReturned' => BookBorrow::where('status', 'returned')->count(),
                    'totalReservations' => BookReservation::count(),
                ];
                // Convert to array format for Excel export
                $data = [];
                foreach ($stats as $key => $value) {
                    $data[] = [
                        'metric' => ucwords(str_replace('_', ' ', $key)),
                        'value' => $value,
                    ];
                }
                break;

            case 'monthly-borrows':
                $monthlyBorrows = BookBorrow::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                $data = $monthlyBorrows->map(function ($item) {
                    return [
                        'month' => Carbon::createFromFormat('Y-m', $item->month)->format('F Y'),
                        'borrows' => $item->count,
                    ];
                })->toArray();
                break;

            case 'monthly-reservations':
                $monthlyReservations = BookReservation::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                $data = $monthlyReservations->map(function ($item) {
                    return [
                        'month' => Carbon::createFromFormat('Y-m', $item->month)->format('F Y'),
                        'reservations' => $item->count,
                    ];
                })->toArray();
                break;

            case 'daily-activity':
                $dailyActivity = BookBorrow::where('created_at', '>=', Carbon::now()->subDays(30))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                $data = $dailyActivity->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d, Y'),
                        'borrows' => $item->count,
                    ];
                })->toArray();
                break;

            case 'user-registrations':
                $monthlyRegistrations = User::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                $data = $monthlyRegistrations->map(function ($item) {
                    return [
                        'month' => Carbon::createFromFormat('Y-m', $item->month)->format('F Y'),
                        'registrations' => $item->count,
                    ];
                })->toArray();
                break;

            case 'borrow-status':
                $statusDistribution = BookBorrow::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                $data = $statusDistribution->map(function ($item) {
                    return [
                        'status' => ucfirst($item->status),
                        'count' => $item->count,
                    ];
                })->toArray();
                break;

            case 'reservation-status':
                $statusDistribution = BookReservation::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();
                $data = $statusDistribution->map(function ($item) {
                    return [
                        'status' => ucfirst($item->status),
                        'count' => $item->count,
                    ];
                })->toArray();
                break;

            case 'top-students':
                $studentRole = Role::where('name', 'Student')->first();
                $studentIds = $studentRole ? User::where('role_id', $studentRole->id)->pluck('id') : collect();
                $topStudents = User::whereIn('id', $studentIds)
                    ->withCount(['borrows' => fn ($q) => $q->where('status', 'returned')])
                    ->with('userInfo')
                    ->orderByDesc('borrows_count')
                    ->limit(10)
                    ->get();
                $data = $topStudents->map(function ($student, $index) {
                    return [
                        'rank' => $index + 1,
                        'name' => html_entity_decode($student->userInfo->full_name ?? $student->email, ENT_QUOTES, 'UTF-8'),
                        'lrn' => $student->userInfo->lrn ?? '—',
                        'books_read' => $student->borrows_count,
                    ];
                })->toArray();
                break;

            case 'top-teachers':
                $teacherRole = Role::where('name', 'Teacher')->first();
                $teacherIds = $teacherRole ? User::where('role_id', $teacherRole->id)->pluck('id') : collect();
                $topTeachers = User::whereIn('id', $teacherIds)
                    ->withCount(['borrows' => fn ($q) => $q->where('status', 'returned')])
                    ->with('userInfo')
                    ->orderByDesc('borrows_count')
                    ->limit(10)
                    ->get();
                $data = $topTeachers->map(function ($teacher, $index) {
                    return [
                        'rank' => $index + 1,
                        'name' => html_entity_decode($teacher->userInfo->full_name ?? $teacher->email, ENT_QUOTES, 'UTF-8'),
                        'employee_number' => $teacher->userInfo->employee_number ?? '—',
                        'books_read' => $teacher->borrows_count,
                    ];
                })->toArray();
                break;

            case 'top-borrowed-books':
                $topBorrowedBooks = Book::withCount('borrows')
                    ->orderByDesc('borrows_count')
                    ->limit(10)
                    ->get();
                $data = $topBorrowedBooks->map(function ($book, $index) {
                    return [
                        'rank' => $index + 1,
                        'book_name' => html_entity_decode($book->book_name, ENT_QUOTES, 'UTF-8'),
                        'author' => html_entity_decode($book->author ?? '—', ENT_QUOTES, 'UTF-8'),
                        'borrows_count' => $book->borrows_count,
                    ];
                })->toArray();
                break;

            case 'top-viewed-books':
                $topViewedBooks = Book::orderByDesc('view_count')
                    ->limit(10)
                    ->get();
                $data = $topViewedBooks->map(function ($book, $index) {
                    return [
                        'rank' => $index + 1,
                        'book_name' => html_entity_decode($book->book_name, ENT_QUOTES, 'UTF-8'),
                        'author' => html_entity_decode($book->author ?? '—', ENT_QUOTES, 'UTF-8'),
                        'view_count' => $book->view_count,
                    ];
                })->toArray();
                break;

            case 'top-favorited-books':
                $topFavoritedBooks = Book::orderByDesc('favorite_count')
                    ->limit(10)
                    ->get();
                $data = $topFavoritedBooks->map(function ($book, $index) {
                    return [
                        'rank' => $index + 1,
                        'book_name' => html_entity_decode($book->book_name, ENT_QUOTES, 'UTF-8'),
                        'author' => html_entity_decode($book->author ?? '—', ENT_QUOTES, 'UTF-8'),
                        'favorite_count' => $book->favorite_count,
                    ];
                })->toArray();
                break;

            case 'category-stats':
                $categoryStats = Book::select('category', DB::raw('COUNT(*) as total'), DB::raw('SUM(stock_quantity) as total_stock'))
                    ->groupBy('category')
                    ->orderByDesc('total')
                    ->get();
                $totalBooksInCategories = $categoryStats->sum('total');
                $data = $categoryStats->map(function ($category) use ($totalBooksInCategories) {
                    $percentage = $totalBooksInCategories > 0 ? round(($category->total / $totalBooksInCategories) * 100, 1) : 0;
                    return [
                        'category' => $category->category ?? 'Uncategorized',
                        'total_books' => $category->total,
                        'total_stock' => $category->total_stock,
                        'percentage' => $percentage . '%',
                    ];
                })->toArray();
                break;

            case 'year-comparison':
                $thisYearBorrows = BookBorrow::whereYear('created_at', Carbon::now()->year)->count();
                $lastYearBorrows = BookBorrow::whereYear('created_at', Carbon::now()->subYear()->year)->count();
                $thisYearReturns = BookBorrow::where('status', 'returned')
                    ->whereYear('return_date', Carbon::now()->year)
                    ->count();
                $lastYearReturns = BookBorrow::where('status', 'returned')
                    ->whereYear('return_date', Carbon::now()->subYear()->year)
                    ->count();
                $data = [
                    [
                        'metric' => 'Borrows',
                        'this_year' => $thisYearBorrows,
                        'last_year' => $lastYearBorrows,
                        'growth' => $lastYearBorrows > 0 ? round((($thisYearBorrows - $lastYearBorrows) / $lastYearBorrows) * 100, 2) : 0,
                    ],
                    [
                        'metric' => 'Returns',
                        'this_year' => $thisYearReturns,
                        'last_year' => $lastYearReturns,
                        'growth' => $lastYearReturns > 0 ? round((($thisYearReturns - $lastYearReturns) / $lastYearReturns) * 100, 2) : 0,
                    ],
                ];
                break;

            case 'monthly-attendance':
                $monthlyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(visit_date, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                $data = $monthlyAttendance->map(function ($item) {
                    return [
                        'month' => Carbon::createFromFormat('Y-m', $item->month)->format('F Y'),
                        'attendance' => $item->count,
                    ];
                })->toArray();
                break;

            case 'daily-attendance':
                $dailyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subDays(30))
                    ->select(DB::raw('DATE(visit_date) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                $data = $dailyAttendance->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d, Y'),
                        'attendance' => $item->count,
                    ];
                })->toArray();
                break;
        }

        return $data;
    }

    private function getReportTitle(string $type): string
    {
        $titles = [
            'overall-stats' => 'Overall Statistics',
            'monthly-borrows' => 'Monthly Borrows Report',
            'monthly-reservations' => 'Monthly Reservations Report',
            'daily-activity' => 'Daily Activity Report',
            'user-registrations' => 'User Registrations Report',
            'borrow-status' => 'Borrow Status Distribution',
            'reservation-status' => 'Reservation Status Distribution',
            'top-students' => 'Top 10 Student Readers',
            'top-teachers' => 'Top 10 Teacher Readers',
            'top-borrowed-books' => 'Top 10 Most Borrowed Books',
            'top-viewed-books' => 'Top 10 Most Viewed Books',
            'top-favorited-books' => 'Top 10 Most Favorited Books',
            'category-stats' => 'Books by Category',
            'year-comparison' => 'Year Comparison Report',
            'monthly-attendance' => 'Monthly Attendance Report',
            'daily-attendance' => 'Daily Attendance Report',
        ];

        return $titles[$type] ?? 'Report';
    }

    private function getChartDataForPdf(string $type): ?array
    {
        switch ($type) {
            case 'monthly-borrows':
                $monthlyBorrows = BookBorrow::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $monthlyReturns = BookBorrow::where('status', 'returned')
                    ->where('return_date', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(return_date, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $labels = [];
                $borrowsData = [];
                $returnsData = [];
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i)->format('Y-m');
                    $labels[] = Carbon::now()->subMonths($i)->format('M Y');
                    $borrowsData[] = $monthlyBorrows->where('month', $month)->first()->count ?? 0;
                    $returnsData[] = $monthlyReturns->where('month', $month)->first()->count ?? 0;
                }

                return [
                    'type' => 'line',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Borrows', 'data' => $borrowsData, 'color' => '#a03464'],
                        ['label' => 'Returns', 'data' => $returnsData, 'color' => '#10b981'],
                    ],
                ];

            case 'monthly-reservations':
                $monthlyReservations = BookReservation::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i)->format('Y-m');
                    $labels[] = Carbon::now()->subMonths($i)->format('M Y');
                    $data[] = $monthlyReservations->where('month', $month)->first()->count ?? 0;
                }

                return [
                    'type' => 'bar',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Reservations', 'data' => $data, 'color' => '#f9c74f'],
                    ],
                ];

            case 'daily-activity':
                $dailyActivity = BookBorrow::where('created_at', '>=', Carbon::now()->subDays(30))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $labels[] = Carbon::now()->subDays($i)->format('M d');
                    $data[] = $dailyActivity->where('date', $date)->first()->count ?? 0;
                }

                return [
                    'type' => 'line',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Daily Borrows', 'data' => $data, 'color' => '#6ddccf'],
                    ],
                ];

            case 'user-registrations':
                $monthlyRegistrations = User::where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i)->format('Y-m');
                    $labels[] = Carbon::now()->subMonths($i)->format('M Y');
                    $data[] = $monthlyRegistrations->where('month', $month)->first()->count ?? 0;
                }

                return [
                    'type' => 'bar',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'New Users', 'data' => $data, 'color' => '#3b82f6'],
                    ],
                ];

            case 'borrow-status':
                $statusDistribution = BookBorrow::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();

                $labels = [];
                $data = [];
                $colors = ['#10b981', '#ef4444', '#f9c74f', '#3b82f6', '#8b5cf6'];
                $colorIndex = 0;
                foreach ($statusDistribution as $item) {
                    $labels[] = ucfirst($item->status);
                    $data[] = $item->count;
                    $colorIndex++;
                }

                return [
                    'type' => 'pie',
                    'labels' => $labels,
                    'data' => $data,
                    'colors' => array_slice($colors, 0, count($labels)),
                ];

            case 'reservation-status':
                $statusDistribution = BookReservation::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->get();

                $labels = [];
                $data = [];
                $colors = ['#f9c74f', '#10b981', '#6b7280'];
                $colorIndex = 0;
                foreach ($statusDistribution as $item) {
                    $labels[] = ucfirst($item->status);
                    $data[] = $item->count;
                    $colorIndex++;
                }

                return [
                    'type' => 'pie',
                    'labels' => $labels,
                    'data' => $data,
                    'colors' => array_slice($colors, 0, count($labels)),
                ];

            case 'monthly-attendance':
                $monthlyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subMonths(12))
                    ->select(DB::raw('DATE_FORMAT(visit_date, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::now()->subMonths($i)->format('Y-m');
                    $labels[] = Carbon::now()->subMonths($i)->format('M Y');
                    $data[] = $monthlyAttendance->where('month', $month)->first()->count ?? 0;
                }

                return [
                    'type' => 'bar',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Visitors', 'data' => $data, 'color' => '#8b5cf6'],
                    ],
                ];

            case 'daily-attendance':
                $dailyAttendance = Attendance::where('visit_date', '>=', Carbon::now()->subDays(30))
                    ->select(DB::raw('DATE(visit_date) as date'), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $labels = [];
                $data = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $labels[] = Carbon::now()->subDays($i)->format('M d');
                    $data[] = $dailyAttendance->where('date', $date)->first()->count ?? 0;
                }

                return [
                    'type' => 'line',
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Daily Visitors', 'data' => $data, 'color' => '#8b5cf6'],
                    ],
                ];

            default:
                return null;
        }
    }

    private function exportToPdf(string $type, string $title, array $data): Response
    {
        // For overall-stats, convert back to associative array for PDF view
        $pdfData = $data;
        if ($type === 'overall-stats') {
            $pdfData = [];
            foreach ($data as $row) {
                $key = strtolower(str_replace(' ', '_', $row['metric']));
                $pdfData[$key] = $row['value'];
            }
        }

        // Get chart data for chart-based reports
        $chartData = $this->getChartDataForPdf($type);

        $html = view('admin.reports.export-pdf', [
            'title' => $title,
            'type' => $type,
            'data' => $pdfData,
            'chartData' => $chartData,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = str_replace(' ', '_', strtolower($title)) . '_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportToExcel(string $type, string $title, array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $export = new class($data, $type) implements FromCollection, WithHeadings, WithMapping {
            private $data;
            private $type;

            public function __construct(array $data, string $type)
            {
                $this->data = $data;
                $this->type = $type;
            }

            public function collection(): Collection
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                $headings = [
                    'overall-stats' => ['Metric', 'Value'],
                    'monthly-borrows' => ['Month', 'Borrows'],
                    'monthly-reservations' => ['Month', 'Reservations'],
                    'daily-activity' => ['Date', 'Borrows'],
                    'user-registrations' => ['Month', 'Registrations'],
                    'borrow-status' => ['Status', 'Count'],
                    'reservation-status' => ['Status', 'Count'],
                    'top-students' => ['Rank', 'Name', 'LRN', 'Books Read'],
                    'top-teachers' => ['Rank', 'Name', 'Employee Number', 'Books Read'],
                    'top-borrowed-books' => ['Rank', 'Book Name', 'Author', 'Borrows Count'],
                    'top-viewed-books' => ['Rank', 'Book Name', 'Author', 'View Count'],
                    'top-favorited-books' => ['Rank', 'Book Name', 'Author', 'Favorite Count'],
                    'category-stats' => ['Category', 'Total Books', 'Total Stock', 'Percentage'],
                    'year-comparison' => ['Metric', 'This Year', 'Last Year', 'Growth (%)'],
                    'monthly-attendance' => ['Month', 'Attendance'],
                    'daily-attendance' => ['Date', 'Attendance'],
                ];

                return $headings[$this->type] ?? ['Data'];
            }

            public function map($row): array
            {
                return array_values($row);
            }
        };

        $filename = str_replace(' ', '_', strtolower($title)) . '_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }
}

