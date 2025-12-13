<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\BookBorrow;
use App\Models\BookReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class ReservationManagementController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();

        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        // Auto-void expired reservations (past claim deadline)
        BookReservation::where('status', 'pending')
            ->where('claim_deadline', '<', now()->startOfDay())
            ->update(['status' => 'voided']);

        $reservationsQuery = BookReservation::with(['book', 'user.userInfo', 'user.role']);

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'pending') {
                $reservationsQuery->where('status', 'pending');
            } elseif ($status === 'claimed') {
                $reservationsQuery->where('status', 'claimed');
            } elseif ($status === 'voided') {
                $reservationsQuery->where('status', 'voided');
            }
        } else {
            // Default: show all reservations
            // No filter applied
        }

        // Search functionality
        if ($search = $request->input('search')) {
            $reservationsQuery->where(function ($query) use ($search) {
                $query->whereHas('book', function ($bookQuery) use ($search) {
                    $bookQuery->where('book_name', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhere('authors_name', 'like', "%{$search}%");
                })->orWhereHas('user.userInfo', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('lrn', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $userType = $request->input('user_type');
            if ($userType === 'student') {
                $reservationsQuery->whereHas('user.role', function ($query) {
                    $query->where('name', 'Student');
                });
            } elseif ($userType === 'teacher') {
                $reservationsQuery->whereHas('user.role', function ($query) {
                    $query->where('name', 'Teacher');
                });
            }
        }

        $reservations = $reservationsQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.manage-reservations', [
            'reservations' => $reservations,
            'selectedStatus' => $request->input('status', ''),
            'selectedUserType' => $request->input('user_type', ''),
            'searchQuery' => $request->input('search', ''),
        ]);
    }

    public function verify(BookReservation $reservation): JsonResponse
    {
        $this->ensureAdminAccess();
        
        // Check if already claimed
        if ($reservation->status === 'claimed') {
            return response()->json([
                'success' => false,
                'message' => 'This reservation has already been claimed.',
            ], 400);
        }
        
        // Check if voided
        if ($reservation->status === 'voided') {
            return response()->json([
                'success' => false,
                'message' => 'This reservation has been voided and cannot be claimed.',
            ], 400);
        }
        
        // Check if expired
        if ($reservation->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This reservation has expired and cannot be claimed.',
            ], 400);
        }
        
        // Check if book has available stock
        if ($reservation->book->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This book is currently not available. All copies are borrowed.',
            ], 400);
        }
        
        try {
            DB::transaction(function () use ($reservation) {
                // Mark reservation as claimed
                $reservation->update([
                    'status' => 'claimed',
                    'claimed_at' => now(),
                ]);
                
                // Create borrow record
                BookBorrow::create([
                    'book_id' => $reservation->book_id,
                    'user_id' => $reservation->user_id,
                    'borrow_date' => $reservation->reserve_date,
                    'due_date' => $reservation->due_date,
                    'status' => 'borrowed',
                ]);
                
                // Decrease stock quantity
                $reservation->book->decrement('stock_quantity');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation has been verified and claimed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify reservation. Please try again.',
            ], 500);
        }
    }

    public function void(BookReservation $reservation): JsonResponse
    {
        $this->ensureAdminAccess();
        
        // Check if already voided
        if ($reservation->status === 'voided') {
            return response()->json([
                'success' => false,
                'message' => 'This reservation has already been voided.',
            ], 400);
        }
        
        // Check if already claimed
        if ($reservation->status === 'claimed') {
            return response()->json([
                'success' => false,
                'message' => 'This reservation has already been claimed and cannot be voided.',
            ], 400);
        }
        
        try {
            $reservation->update([
                'status' => 'voided',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation has been voided successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to void reservation. Please try again.',
            ], 500);
        }
    }
}
