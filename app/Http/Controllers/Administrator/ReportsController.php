<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
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
        ]);
    }
}

