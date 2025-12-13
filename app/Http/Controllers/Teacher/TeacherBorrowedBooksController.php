<?php

namespace App\Http\Controllers\Teacher;

use App\Models\BookBorrow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class TeacherBorrowedBooksController extends BaseTeacherController
{
    public function index(): View|RedirectResponse
    {
        $this->ensureTeacher();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }
        
        $user = Auth::user();
        
        // Update overdue status
        BookBorrow::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);
        
        // Get all borrows for the user
        $borrows = BookBorrow::with('book')
            ->where('user_id', $user->id)
            ->orderBy('borrow_date', 'desc')
            ->get();
        
        // Separate into active and returned
        $activeBorrows = $borrows->where('status', '!=', 'returned');
        $returnedBorrows = $borrows->where('status', 'returned');
        
        return view('teacher.borrowed-books', [
            'activeBorrows' => $activeBorrows,
            'returnedBorrows' => $returnedBorrows,
        ]);
    }

    public function return(BookBorrow $borrow): JsonResponse
    {
        $this->ensureTeacher();
        
        $user = Auth::user();
        
        // Verify the borrow belongs to the user
        if ($borrow->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }
        
        // Check if already returned
        if ($borrow->status === 'returned') {
            return response()->json([
                'success' => false,
                'message' => 'This book has already been returned.',
            ], 400);
        }
        
        try {
            DB::transaction(function () use ($borrow) {
                $borrow->update([
                    'status' => 'returned',
                    'return_date' => Carbon::today(),
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to return book. Please try again.',
            ], 500);
        }
    }
}
