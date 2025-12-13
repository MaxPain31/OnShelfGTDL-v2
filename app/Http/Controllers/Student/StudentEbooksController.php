<?php

namespace App\Http\Controllers\Student;

use App\Models\Ebook;
use App\Models\EbookFavorite;
use App\Models\EbookView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentEbooksController extends BaseStudentController
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $ebooksQuery = Ebook::query();

        // Search functionality
        if ($search = $request->input('search')) {
            $ebooksQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('authors', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $ebooksQuery->where('category', $request->input('category'));
        }

        $ebooks = $ebooksQuery->orderBy('category')->orderBy('title')->get();

        // Group e-books by category
        $ebooksByCategory = $ebooks->groupBy('category');

        // Get distinct categories for filter dropdown
        $categories = Ebook::distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('student.ebooks', [
            'ebooksByCategory' => $ebooksByCategory,
            'categories' => $categories,
            'selectedCategory' => $request->input('category'),
            'searchQuery' => $request->input('search'),
        ]);
    }

    public function show(Ebook $ebook): JsonResponse
    {
        $this->ensureStudent();
        
        $user = Auth::user();
        
        // Check if user has already viewed this ebook
        $hasViewed = EbookView::where('ebook_id', $ebook->id)
            ->where('user_id', $user->id)
            ->exists();
        
        // Only increment view count if user hasn't viewed this ebook before
        if (!$hasViewed) {
            // Create a view record
            EbookView::create([
                'ebook_id' => $ebook->id,
                'user_id' => $user->id,
            ]);
            
            // Increment view count
            $ebook->increment('view_count');
        }
        
        // Reload to get updated view count
        $ebook->refresh();
        
        // Check if user has favorited this ebook
        $isFavorited = EbookFavorite::where('ebook_id', $ebook->id)
            ->where('user_id', $user->id)
            ->exists();
        
        return response()->json([
            'success' => true,
            'ebook' => [
                'id' => $ebook->id,
                'title' => $ebook->title,
                'category' => $ebook->category,
                'authors' => $ebook->authors,
                'description' => $ebook->description,
                'ebook_file_path' => $ebook->ebook_file_path ? asset('storage/' . $ebook->ebook_file_path) : null,
                'ebook_image_path' => $ebook->ebook_image_path ? asset('storage/' . $ebook->ebook_image_path) : null,
                'view_count' => $ebook->view_count ?? 0,
                'favorite_count' => $ebook->favorite_count ?? 0,
                'is_favorited' => $isFavorited,
            ],
        ]);
    }

    public function read(Ebook $ebook): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        if (!$ebook->ebook_file_path) {
            return redirect()->route('student.ebooks')->with('error', 'E-book file is not available.');
        }

        $user = Auth::user();
        
        // Check if user has already viewed this ebook
        $hasViewed = EbookView::where('ebook_id', $ebook->id)
            ->where('user_id', $user->id)
            ->exists();
        
        // Only increment view count if user hasn't viewed this ebook before
        if (!$hasViewed) {
            // Create a view record
            EbookView::create([
                'ebook_id' => $ebook->id,
                'user_id' => $user->id,
            ]);
            
            // Increment view count
            $ebook->increment('view_count');
        }

        // Use direct asset URL for PDF files - simpler and more reliable
        $ebookFileUrl = asset('storage/' . $ebook->ebook_file_path);
        $fileExtension = strtolower(pathinfo($ebook->ebook_file_path, PATHINFO_EXTENSION));

        return view('student.ebook-reader', [
            'ebook' => $ebook,
            'ebookFileUrl' => $ebookFileUrl,
            'fileExtension' => $fileExtension,
            'user' => $user,
        ]);
    }

    public function file(Ebook $ebook): Response
    {
        $this->ensureStudent();

        if (!$ebook->ebook_file_path) {
            abort(404, 'E-book file path is not set.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($ebook->ebook_file_path)) {
            abort(404, 'E-book file not found at: ' . $ebook->ebook_file_path);
        }

        try {
            // Determine MIME type from extension
            $extension = strtolower(pathinfo($ebook->ebook_file_path, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'epub' => 'application/epub+zip',
                'mobi' => 'application/x-mobipocket-ebook',
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/pdf';

            $fileName = basename($ebook->ebook_file_path);

            // Use Storage::response() which is the Laravel way to serve files
            return Storage::disk('public')->response(
                $ebook->ebook_file_path,
                $fileName,
                [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . addslashes($fileName) . '"',
                    'Cache-Control' => 'public, max-age=3600',
                    'Accept-Ranges' => 'bytes',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error serving ebook file', [
                'ebook_id' => $ebook->id,
                'file_path' => $ebook->ebook_file_path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error serving file: ' . $e->getMessage());
        }
    }

    public function toggleFavorite(Ebook $ebook): JsonResponse
    {
        $this->ensureStudent();
        
        $user = Auth::user();
        
        // Check if user has already favorited this ebook
        $favorite = EbookFavorite::where('ebook_id', $ebook->id)
            ->where('user_id', $user->id)
            ->first();
        
        if ($favorite) {
            // Unfavorite: Remove favorite and decrement count
            $favorite->delete();
            $ebook->decrement('favorite_count');
            $isFavorited = false;
        } else {
            // Favorite: Add favorite and increment count
            EbookFavorite::create([
                'ebook_id' => $ebook->id,
                'user_id' => $user->id,
            ]);
            $ebook->increment('favorite_count');
            $isFavorited = true;
        }
        
        $ebook->refresh();
        
        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'favorite_count' => $ebook->favorite_count,
        ]);
    }
}

