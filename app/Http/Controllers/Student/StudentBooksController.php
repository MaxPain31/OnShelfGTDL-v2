<?php

namespace App\Http\Controllers\Student;

use App\Mail\BookBorrowedMail;
use App\Mail\BookReservedMail;
use App\Models\Book;
use App\Models\BookBorrow;
use App\Models\BookFavorite;
use App\Models\BookReservation;
use App\Models\BookView;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\View\View;

class StudentBooksController extends BaseStudentController
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $booksQuery = Book::query();

        // Search functionality
        if ($search = $request->input('search')) {
            $booksQuery->where(function ($query) use ($search) {
                $query->where('isbn', 'like', "%{$search}%")
                    ->orWhere('book_name', 'like', "%{$search}%")
                    ->orWhere('authors_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $booksQuery->where('category', $request->input('category'));
        }

        $books = $booksQuery->orderBy('category')->orderBy('book_name')->get();

        // Group books by category
        $booksByCategory = $books->groupBy('category');

        // Get distinct categories for filter dropdown
        $categories = Book::distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('student.books', [
            'booksByCategory' => $booksByCategory,
            'categories' => $categories,
            'selectedCategory' => $request->input('category'),
            'searchQuery' => $request->input('search'),
        ]);
    }

    public function show(Book $book): JsonResponse
    {
        $this->ensureStudent();

        $user = Auth::user();

        // Check if user has already viewed this book
        $hasViewed = BookView::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->exists();

        // Only increment view count if user hasn't viewed this book before
        if (!$hasViewed) {
            // Create a view record
            BookView::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
            ]);

            // Increment view count
            $book->increment('view_count');
        }

        // Reload to get updated view count
        $book->refresh();

        // Check if user has favorited this book
        $isFavorited = BookFavorite::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->exists();

        // Count returned books for reservation eligibility
        $returnedBooksCount = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->count();

        $canReserve = $returnedBooksCount >= 5;

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'isbn' => $book->isbn,
                'book_name' => $book->book_name,
                'category' => $book->category,
                'authors_name' => $book->authors_name,
                'book_shelf' => $book->book_shelf,
                'copyright' => $book->copyright,
                'stock_quantity' => $book->stock_quantity,
                'publication_name' => $book->publication_name,
                'image_path' => $book->image_path ? asset('storage/' . $book->image_path) : null,
                'description' => $book->description,
                'view_count' => $book->view_count,
                'favorite_count' => $book->favorite_count ?? 0,
                'is_favorited' => $isFavorited,
                'can_reserve' => $canReserve,
                'returned_books_count' => $returnedBooksCount,
            ],
        ]);
    }

    public function toggleFavorite(Book $book): JsonResponse
    {
        $this->ensureStudent();

        $user = Auth::user();

        // Check if user has already favorited this book
        $favorite = BookFavorite::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->first();

        if ($favorite) {
            // Unfavorite: Remove favorite and decrement count
            $favorite->delete();
            $book->decrement('favorite_count');
            $isFavorited = false;
        } else {
            // Favorite: Add favorite and increment count
            BookFavorite::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
            ]);
            $book->increment('favorite_count');
            $isFavorited = true;
        }

        $book->refresh();

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'favorite_count' => $book->favorite_count,
        ]);
    }

    public function borrow(Book $book): JsonResponse
    {
        $this->ensureStudent();

        $user = Auth::user();

        // Check if user has overdue books
        $overdueCount = $user->overdueBorrows()->count();
        if ($overdueCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "You cannot borrow books because you have {$overdueCount} overdue book(s). Please return them first.",
            ], 400);
        }

        // Check if user already has 3 active borrows
        $activeBorrows = $user->activeBorrows()->count();
        if ($activeBorrows >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum limit of 3 active borrows. Please return a book first.',
            ], 400);
        }

        // Check if book is already borrowed by this user (not returned)
        $existingBorrow = BookBorrow::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->first();

        if ($existingBorrow) {
            return response()->json([
                'success' => false,
                'message' => 'You have already borrowed this book. Please return it first before borrowing again.',
            ], 400);
        }

        // Check if book has available stock
        if ($book->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This book is currently not available. All copies are borrowed.',
            ], 400);
        }

        try {
            $borrow = null;
            DB::transaction(function () use ($book, $user, &$borrow) {
                // Calculate due date (3 business days, excluding weekends)
                $dueDate = Carbon::today();
                $businessDays = 0;
                while ($businessDays < 3) {
                    $dueDate->addDay();
                    // Skip Saturday (6) and Sunday (0)
                    if ($dueDate->dayOfWeek !== Carbon::SATURDAY && $dueDate->dayOfWeek !== Carbon::SUNDAY) {
                        $businessDays++;
                    }
                }

                // Create borrow record
                $borrow = BookBorrow::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'borrow_date' => Carbon::today(),
                    'due_date' => $dueDate,
                    'status' => 'borrowed',
                ]);

                // Decrease stock quantity
                $book->decrement('stock_quantity');

                // Get user name
                $userName = $user->userInfo->full_name ?? $user->name ?? $user->email;

                // Create notification for user
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'book_borrowed',
                    'title' => 'Book Borrowed Successfully',
                    'message' => "You have successfully borrowed \"{$book->book_name}\". Please return it by {$dueDate->format('M d, Y')}.",
                    'related_id' => $borrow->id,
                    'related_type' => BookBorrow::class,
                    'data' => [
                        'book_name' => $book->book_name,
                        'book_id' => $book->id,
                        'due_date' => $dueDate->format('Y-m-d'),
                        'due_date_formatted' => $dueDate->format('M d, Y'),
                    ],
                ]);

                // Send email notification
                $bookImage = $book->image_path ? config('app.url') . '/storage/' . $book->image_path : null;
                Mail::to($user->email)->send(new BookBorrowedMail(
                    $userName,
                    $book->book_name,
                    Carbon::today()->format('M d, Y'),
                    $dueDate->format('M d, Y'),
                    $bookImage
                ));

                // Notify all admins
                $adminRole = Role::where('name', 'Administrator')->first();
                if ($adminRole) {
                    $admins = User::where('role_id', $adminRole->id)->get();
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'admin_book_borrowed',
                            'title' => 'New Book Borrowed',
                            'message' => "{$userName} (Student) has borrowed \"{$book->book_name}\". Due date: {$dueDate->format('M d, Y')}.",
                            'related_id' => $borrow->id,
                            'related_type' => BookBorrow::class,
                            'data' => [
                                'book_name' => $book->book_name,
                                'book_id' => $book->id,
                                'borrower_name' => $userName,
                                'borrower_type' => 'Student',
                                'due_date' => $dueDate->format('Y-m-d'),
                                'due_date_formatted' => $dueDate->format('M d, Y'),
                            ],
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully! Please return it within 3 days.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to borrow book. Please try again.',
            ], 500);
        }
    }

    public function reserve(Request $request, Book $book): JsonResponse
    {
        $this->ensureStudent();

        $user = Auth::user();

        // Validate request
        $validated = $request->validate([
            'reserve_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after:reserve_date',
        ], [
            'reserve_date.required' => 'Please select a reserve date.',
            'reserve_date.date' => 'Invalid reserve date format.',
            'reserve_date.after_or_equal' => 'Reserve date must be today or a future date.',
            'due_date.required' => 'Please select a due date.',
            'due_date.date' => 'Invalid due date format.',
            'due_date.after' => 'Due date must be after the reserve date.',
        ]);

        $reserveDate = Carbon::parse($validated['reserve_date']);
        $dueDate = Carbon::parse($validated['due_date']);

        // Check if user has at least 5 successful borrows and returns
        $returnedBooksCount = BookBorrow::where('user_id', $user->id)
            ->where('status', 'returned')
            ->count();

        if ($returnedBooksCount < 5) {
            return response()->json([
                'success' => false,
                'message' => "You need to successfully borrow and return at least 5 books before you can reserve books. You currently have {$returnedBooksCount} returned book(s).",
            ], 400);
        }

        // Check if user already has 3 active reservations
        $activeReservations = $user->activeReservations()->count();
        if ($activeReservations >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum limit of 3 active reservations. Please claim or cancel a reservation first.',
            ], 400);
        }

        // Check if book is already reserved by this user (pending and not expired)
        $existingReservation = BookReservation::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('claim_deadline', '>=', now()->startOfDay())
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reserved this book. Please claim it first before reserving again.',
            ], 400);
        }

        // Check if book is already borrowed by this user (not returned)
        $existingBorrow = BookBorrow::where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->first();

        if ($existingBorrow) {
            return response()->json([
                'success' => false,
                'message' => 'You have already borrowed this book. Please return it first before reserving.',
            ], 400);
        }

        // Check if book has available stock
        if ($book->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This book is currently not available. All copies are borrowed.',
            ], 400);
        }

        try {
            $reservation = null;
            DB::transaction(function () use ($book, $user, $reserveDate, $dueDate, &$reservation) {
                // Calculate claim deadline (3 days from now)
                $claimDeadline = Carbon::today()->addDays(3);

                // Create reservation record
                $reservation = BookReservation::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'reserve_date' => $reserveDate,
                    'due_date' => $dueDate,
                    'claim_deadline' => $claimDeadline,
                    'status' => 'pending',
                ]);

                // Get user name
                $userName = $user->userInfo->full_name ?? $user->name ?? $user->email;

                // Create notification for user
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'book_reserved',
                    'title' => 'Book Reserved Successfully',
                    'message' => "You have successfully reserved \"{$book->book_name}\". Please claim it by {$claimDeadline->format('M d, Y')}.",
                    'related_id' => $reservation->id,
                    'related_type' => BookReservation::class,
                    'data' => [
                        'book_name' => $book->book_name,
                        'book_id' => $book->id,
                        'claim_deadline' => $claimDeadline->format('Y-m-d'),
                        'claim_deadline_formatted' => $claimDeadline->format('M d, Y'),
                        'reserve_date' => $reserveDate->format('Y-m-d'),
                        'reserve_date_formatted' => $reserveDate->format('M d, Y'),
                    ],
                ]);

                // Send email notification
                $bookImage = $book->image_path ? config('app.url') . '/storage/' . $book->image_path : null;
                Mail::to($user->email)->send(new BookReservedMail(
                    $userName,
                    $book->book_name,
                    $reserveDate->format('M d, Y'),
                    $dueDate->format('M d, Y'),
                    $claimDeadline->format('M d, Y'),
                    $bookImage
                ));

                // Notify all admins
                $adminRole = Role::where('name', 'Administrator')->first();
                if ($adminRole) {
                    $admins = User::where('role_id', $adminRole->id)->get();
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'type' => 'admin_book_reserved',
                            'title' => 'New Book Reservation',
                            'message' => "{$userName} (Student) has reserved \"{$book->book_name}\". Claim deadline: {$claimDeadline->format('M d, Y')}.",
                            'related_id' => $reservation->id,
                            'related_type' => BookReservation::class,
                            'data' => [
                                'book_name' => $book->book_name,
                                'book_id' => $book->id,
                                'borrower_name' => $userName,
                                'borrower_type' => 'Student',
                                'claim_deadline' => $claimDeadline->format('Y-m-d'),
                                'claim_deadline_formatted' => $claimDeadline->format('M d, Y'),
                                'reserve_date' => $reserveDate->format('Y-m-d'),
                                'reserve_date_formatted' => $reserveDate->format('M d, Y'),
                            ],
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Book reserved successfully! You have 3 days to claim it.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve book. Please try again.',
            ], 500);
        }
    }
}

