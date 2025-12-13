<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Mail\BookReturnedMail;
use App\Models\BookBorrow;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\View\View;

class BorrowManagementController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();

        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        // Update overdue status
        BookBorrow::where('status', 'borrowed')
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);

        $borrowsQuery = BookBorrow::with(['book', 'user.userInfo', 'user.role']);

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'overdue') {
                $borrowsQuery->where('status', 'overdue');
            } elseif ($status === 'borrowed') {
                $borrowsQuery->where('status', 'borrowed');
            } elseif ($status === 'returned') {
                $borrowsQuery->where('status', 'returned');
            }
        } else {
            // Default: show active borrows (borrowed and overdue)
            $borrowsQuery->whereIn('status', ['borrowed', 'overdue']);
        }

        // Search functionality
        if ($search = $request->input('search')) {
            $borrowsQuery->where(function ($query) use ($search) {
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
                $borrowsQuery->whereHas('user.role', function ($query) {
                    $query->where('name', 'Student');
                });
            } elseif ($userType === 'teacher') {
                $borrowsQuery->whereHas('user.role', function ($query) {
                    $query->where('name', 'Teacher');
                });
            }
        }

        $borrows = $borrowsQuery->orderBy('borrow_date', 'desc')->paginate(15)->withQueryString();

        return view('admin.manage-borrows', [
            'borrows' => $borrows,
            'selectedStatus' => $request->input('status', 'active'),
            'selectedUserType' => $request->input('user_type', ''),
            'searchQuery' => $request->input('search', ''),
        ]);
    }

    public function return(BookBorrow $borrow): JsonResponse
    {
        $this->ensureAdminAccess();
        
        // Check if already returned
        if ($borrow->status === 'returned') {
            return response()->json([
                'success' => false,
                'message' => 'This book has already been marked as returned.',
            ], 400);
        }
        
        try {
            DB::transaction(function () use ($borrow) {
                $returnDate = Carbon::today();
                
                $borrow->update([
                    'status' => 'returned',
                    'return_date' => $returnDate,
                ]);
                
                // Increase stock quantity when book is returned
                $borrow->book->increment('stock_quantity');
                
                // Create notification for the borrower
                $user = $borrow->user;
                $userType = $user->isStudent() ? 'Student' : ($user->isTeacher() ? 'Teacher' : 'User');
                $userName = $user->userInfo->full_name ?? $user->name ?? $user->email;
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'book_returned',
                    'title' => 'Book Returned Successfully',
                    'message' => "Your borrowed book \"{$borrow->book->book_name}\" has been successfully returned on {$returnDate->format('M d, Y')}. Thank you!",
                    'related_id' => $borrow->id,
                    'related_type' => BookBorrow::class,
                    'data' => [
                        'book_name' => $borrow->book->book_name,
                        'book_id' => $borrow->book->id,
                        'return_date' => $returnDate->format('Y-m-d'),
                        'return_date_formatted' => $returnDate->format('M d, Y'),
                    ],
                ]);

                // Send email notification
                $bookImage = $borrow->book->image_path ? config('app.url') . '/storage/' . $borrow->book->image_path : null;
                Mail::to($user->email)->send(new BookReturnedMail(
                    $userName,
                    $borrow->book->book_name,
                    $returnDate->format('M d, Y'),
                    $bookImage
                ));
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Book has been marked as returned successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark book as returned. Please try again.',
            ], 500);
        }
    }
}
