<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentProfileController extends BaseStudentController
{
    public function show(): View|RedirectResponse
    {
        $this->ensureStudent();
        
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $user = Auth::user();
        $user->load(['userInfo', 'role']);

        // Get user statistics
        $activeBorrows = $user->activeBorrows()->count();
        $activeReservations = $user->activeReservations()->count();
        $totalBorrows = BookBorrow::where('user_id', $user->id)->count();
        $returnedBooks = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->count();
        $overdueBooks = $user->overdueBorrows()->count();

        return view('student.profile', [
            'user' => $user,
            'activeBorrows' => $activeBorrows,
            'activeReservations' => $activeReservations,
            'totalBorrows' => $totalBorrows,
            'returnedBooks' => $returnedBooks,
            'overdueBooks' => $overdueBooks,
        ]);
    }
}

