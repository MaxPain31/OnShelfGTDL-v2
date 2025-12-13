<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPanelController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();

        if (!$user || !$user->role || $user->role->name !== 'Administrator') {
            abort(403, 'Unauthorized access. Only administrators can access this page.');
        }
    }

    public function dashboard()
    {
        $this->ensureAdmin();

        // Get all users
        $totalMembers = User::count();
        $totalStudents = User::whereHas('role', fn ($query) => $query->where('name', 'Student'))->count();
        $totalTeachers = User::whereHas('role', fn ($query) => $query->where('name', 'Teacher'))->count();

        // Get all borrows
        $totalBorrows = BookBorrow::whereIn('status', ['borrowed', 'overdue'])->count();
        $overdueBooks = BookBorrow::where('status', 'overdue')->count();
        $activeReservations = BookReservation::whereIn('status', ['pending', 'claimed'])
            ->where('claim_deadline', '>=', now()->startOfDay())
            ->count();

        $stats = [
            ['label' => 'Total Members', 'value' => (string)$totalMembers, 'trend' => 'All users', 'icon' => 'users', 'color' => '#6ddccf'],
            ['label' => 'Total Students', 'value' => (string)$totalStudents, 'trend' => 'Registered', 'icon' => 'user', 'color' => '#3b82f6'],
            ['label' => 'Total Teachers', 'value' => (string)$totalTeachers, 'trend' => 'Registered', 'icon' => 'user-check', 'color' => '#8b5cf6'],
            ['label' => 'Books Borrowed', 'value' => (string)$totalBorrows, 'trend' => 'Active borrows', 'icon' => 'book-open', 'color' => '#10b981'],
            ['label' => 'Overdue Books', 'value' => (string)$overdueBooks, 'trend' => 'Needs attention', 'icon' => 'alert-circle', 'color' => '#ef4444'],
            ['label' => 'Active Reservations', 'value' => (string)$activeReservations, 'trend' => 'Pending/Claimed', 'icon' => 'bookmark', 'color' => '#f9c74f'],
        ];

        // Student Statistics
        $studentRole = Role::where('name', 'Student')->first();
        $studentIds = $studentRole ? User::where('role_id', $studentRole->id)->pluck('id') : collect();

        // Student statistics
        $studentActiveBorrows = BookBorrow::whereIn('user_id', $studentIds)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->count();

        $studentActiveReservations = BookReservation::whereIn('user_id', $studentIds)
            ->whereIn('status', ['pending', 'claimed'])
            ->where('claim_deadline', '>=', now()->startOfDay())
            ->count();

        $studentOverdueBooks = BookBorrow::whereIn('user_id', $studentIds)
            ->where('status', 'overdue')
            ->count();

        $studentReturnedBooks = BookBorrow::whereIn('user_id', $studentIds)
            ->where('status', 'returned')
            ->count();

        $studentThisMonthBorrows = BookBorrow::whereIn('user_id', $studentIds)
            ->whereMonth('borrow_date', Carbon::now()->month)
            ->whereYear('borrow_date', Carbon::now()->year)
            ->count();

        $studentThisMonthReturns = BookBorrow::whereIn('user_id', $studentIds)
            ->where('status', 'returned')
            ->whereMonth('return_date', Carbon::now()->month)
            ->whereYear('return_date', Carbon::now()->year)
            ->count();

        $studentStats = [
            ['label' => 'Books Borrowed', 'value' => (string)$studentActiveBorrows, 'trend' => 'Currently reading', 'icon' => 'book-open', 'color' => '#6ddccf'],
            ['label' => 'Reserved Books', 'value' => (string)$studentActiveReservations, 'trend' => 'Pending pickup', 'icon' => 'bookmark', 'color' => '#f9c74f'],
            ['label' => 'Overdue Books', 'value' => (string)$studentOverdueBooks, 'trend' => 'Needs attention', 'icon' => 'alert-circle', 'color' => '#ef4444'],
            ['label' => 'Reading History', 'value' => (string)$studentReturnedBooks, 'trend' => 'Total books read', 'icon' => 'history', 'color' => '#10b981'],
            ['label' => 'Successful Borrows', 'value' => (string)$studentThisMonthBorrows, 'trend' => 'This month', 'icon' => 'check-circle', 'color' => '#10b981'],
            ['label' => 'Returned Books', 'value' => (string)$studentThisMonthReturns, 'trend' => 'This month', 'icon' => 'rotate-ccw', 'color' => '#6ddccf'],
        ];

        // Get student reading history for chart (last 6 months)
        $studentReadingHistory = BookBorrow::whereIn('user_id', $studentIds)
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
        $studentChartData = [];
        $studentChartLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthLabel = Carbon::now()->subMonths($i)->format('M Y');
            $studentChartLabels[] = $monthLabel;
            $studentChartData[] = $studentReadingHistory[$month] ?? 0;
        }

        // Student Registration Graph Data
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

        // Recent borrows for activity feed
        $recentBorrows = BookBorrow::with(['book', 'user.userInfo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recently added books
        $recentlyAddedBooks = Book::orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'studentStats' => $studentStats,
            'studentChartLabels' => $studentChartLabels,
            'studentChartData' => $studentChartData,
            'studentRegistrationChartLabels' => $studentRegistrationChartLabels,
            'studentRegistrationChartData' => $studentRegistrationChartData,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
            'recentBorrows' => $recentBorrows,
            'recentlyAddedBooks' => $recentlyAddedBooks,
        ]);
    }

    public function manageStudents()
    {
        $this->ensureAdmin();

        $studentsQuery = \App\Models\User::query()
            ->with(['userInfo', 'role'])
            ->whereHas('role', fn ($query) => $query->where('name', 'Student'));

        if ($search = request()->input('search')) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhereHas('userInfo', function ($relation) use ($search) {
                        $relation
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('lrn', 'like', "%{$search}%");
                    });
            });
        }

        $students = $studentsQuery
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.manage-users', [
            'userType' => 'students',
            'students' => $students,
        ]);
    }

    public function manageTeachers()
    {
        $this->ensureAdmin();

        return view('admin.manage-users', ['userType' => 'teachers']);
    }
}

