<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Book;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class TeacherDashboardController extends BaseTeacherController
{
    public function dashboard(): View|RedirectResponse
    {
        $this->ensureTeacher();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $user = Auth::user();

        // Calculate statistics
        $activeBorrows = $user->activeBorrows()->count();
        $activeReservations = $user->activeReservations()->count();
        $overdueBooks = $user->overdueBorrows()->count();
        $returnedBooks = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->count();

        // This month's successful borrows
        $thisMonthBorrows = BookBorrow::where('user_id', $user->id)
            ->whereMonth('borrow_date', Carbon::now()->month)
            ->whereYear('borrow_date', Carbon::now()->year)
            ->count();

        // This month's returns
        $thisMonthReturns = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->whereMonth('return_date', Carbon::now()->month)
            ->whereYear('return_date', Carbon::now()->year)
            ->count();

        $stats = [
            ['label' => 'Books Borrowed', 'value' => (string)$activeBorrows, 'trend' => 'Currently reading', 'icon' => 'book-open', 'color' => '#6ddccf'],
            ['label' => 'Reserved Books', 'value' => (string)$activeReservations, 'trend' => 'Pending pickup', 'icon' => 'bookmark', 'color' => '#f9c74f'],
            ['label' => 'Overdue Books', 'value' => (string)$overdueBooks, 'trend' => 'Needs attention', 'icon' => 'alert-circle', 'color' => '#ef4444'],
            ['label' => 'Reading History', 'value' => (string)$returnedBooks, 'trend' => 'Total books read', 'icon' => 'history', 'color' => '#10b981'],
            ['label' => 'Successful Borrows', 'value' => (string)$thisMonthBorrows, 'trend' => 'This month', 'icon' => 'check-circle', 'color' => '#10b981'],
            ['label' => 'Returned Books', 'value' => (string)$thisMonthReturns, 'trend' => 'This month', 'icon' => 'rotate-ccw', 'color' => '#6ddccf'],
        ];

        // Get reading history for chart (last 6 months)
        $readingHistory = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->where('return_date', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(return_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months with 0
        $chartData = [];
        $chartLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $chartLabels[] = $monthLabel;
            $chartData[] = $readingHistory[$month] ?? 0;
        }

        // Get recently added books (last 8 books)
        $recentlyAddedBooks = Book::orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Get recent borrows for activity feed
        $recentBorrows = BookBorrow::where('user_id', $user->id)
            ->with('book')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get successful borrows (recently borrowed books)
        $recentSuccessfulBorrows = BookBorrow::where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'overdue', 'returned'])
            ->with('book')
            ->orderBy('borrow_date', 'desc')
            ->limit(6)
            ->get();

        // Get recently returned books
        $recentlyReturnedBooks = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->with('book')
            ->orderBy('return_date', 'desc')
            ->limit(6)
            ->get();

        // Student Registration Graph Data (from manage students page)
        $studentRole = Role::where('name', 'Student')->first();
        $studentIds = User::where('role_id', $studentRole->id)->pluck('id');
        $studentRegistrations = User::whereIn('id', $studentIds)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill in missing months with 0
        $studentRegistrationChartData = [];
        $studentRegistrationChartLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $studentRegistrationChartLabels[] = $monthLabel;
            $studentRegistrationChartData[] = $studentRegistrations[$month] ?? 0;
        }

        // Student Status Statistics
        $activeStudents = User::whereIn('id', $studentIds)
            ->where('deactivated', false)
            ->count();

        $inactiveStudents = User::whereIn('id', $studentIds)
            ->where('deactivated', true)
            ->count();

        return view('teacher.dashboard', [
            'stats' => $stats,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentlyAddedBooks' => $recentlyAddedBooks,
            'recentBorrows' => $recentBorrows,
            'recentSuccessfulBorrows' => $recentSuccessfulBorrows,
            'recentlyReturnedBooks' => $recentlyReturnedBooks,
            'studentRegistrationChartLabels' => $studentRegistrationChartLabels,
            'studentRegistrationChartData' => $studentRegistrationChartData,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
        ]);
    }
}

