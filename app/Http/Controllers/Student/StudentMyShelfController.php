<?php

namespace App\Http\Controllers\Student;

use App\Models\BookFavorite;
use App\Models\EbookFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentMyShelfController extends BaseStudentController
{
    public function index(): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $user = Auth::user();
        
        // Get all favorited books for the current user
        $favoriteBooks = BookFavorite::where('user_id', $user->id)
            ->with('book')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('book')
            ->filter(); // Remove any null books
        
        // Get all favorited ebooks for the current user
        $favoriteEbooks = EbookFavorite::where('user_id', $user->id)
            ->with('ebook')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('ebook')
            ->filter(); // Remove any null ebooks
        
        // Group by category
        $booksByCategory = $favoriteBooks->groupBy('category');
        $ebooksByCategory = $favoriteEbooks->groupBy('category');
        
        return view('student.my-shelf', [
            'booksByCategory' => $booksByCategory,
            'ebooksByCategory' => $ebooksByCategory,
        ]);
    }
}

