<?php

namespace App\Http\Controllers\Student;

use App\Models\BookReservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentReservedBooksController extends BaseStudentController
{
    public function index(): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }
        
        $user = Auth::user();
        
        // Auto-void expired reservations (past claim deadline)
        BookReservation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('claim_deadline', '<', now()->startOfDay())
            ->update(['status' => 'voided']);
        
        // Get all reservations for the user
        $reservations = BookReservation::with('book')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Separate into active (pending, claimed) and history (voided)
        $activeReservations = $reservations->whereIn('status', ['pending', 'claimed']);
        $voidedReservations = $reservations->where('status', 'voided');
        
        return view('student.reserved-books', [
            'activeReservations' => $activeReservations,
            'voidedReservations' => $voidedReservations,
        ]);
    }
}

