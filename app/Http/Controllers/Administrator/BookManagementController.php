<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class BookManagementController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();

        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $booksQuery = Book::query();
        $editingBook = null;

        if ($search = $request->input('search')) {
            $booksQuery->where(function ($query) use ($search) {
                $query->where('isbn', 'like', "%{$search}%")
                    ->orWhere('book_name', 'like', "%{$search}%")
                    ->orWhere('authors_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $booksQuery->where('category', $request->input('category'));
        }

        if ($request->filled('edit')) {
            $editingBook = Book::find($request->integer('edit'));
        }

        $books = $booksQuery->orderByDesc('id')->paginate(12)->withQueryString();

        // Get distinct categories for filter dropdown
        $categories = Book::distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.manage-books', [
            'books' => $books,
            'editingBook' => $editingBook,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn'],
            'book_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'authors_name' => ['required', 'string', 'max:255'],
            'book_shelf' => ['required', 'string', 'max:255'],
            'copyright' => ['required', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'publication_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('books', 'public');
        }

        Book::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Book has been added.']);
        }

        return back()->with('status', 'Book has been added.');
    }

    public function update(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();
        Log::info('Update book request:', $request->all());

        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:50', 'unique:books,isbn,' . $book->id],
            'book_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'authors_name' => ['required', 'string', 'max:255'],
            'book_shelf' => ['required', 'string', 'max:255'],
            'copyright' => ['required', 'string', 'max:255'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'publication_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($book->image_path) {
                Storage::disk('public')->delete($book->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('books', 'public');
        }

        $book->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Book has been updated.']);
        }

        return redirect()->route('admin.manage-books')->with('status', 'Book has been updated.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        if ($book->image_path) {
            Storage::disk('public')->delete($book->image_path);
        }

        $book->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Book has been deleted.']);
        }

        return redirect()->route('admin.manage-books')->with('status', 'Book has been deleted.');
    }

    public function export(Request $request, string $format): Response
    {
        $this->ensureAdminAccess();

        $validFormats = ['pdf', 'excel'];
        abort_if(!in_array($format, $validFormats), 404, 'Invalid export format.');

        $booksQuery = Book::query();

        if ($search = $request->input('search')) {
            $booksQuery->where(function ($query) use ($search) {
                $query->where('isbn', 'like', "%{$search}%")
                    ->orWhere('book_name', 'like', "%{$search}%")
                    ->orWhere('authors_name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $booksQuery->where('category', $request->input('category'));
        }

        $books = $booksQuery->orderByDesc('id')->get();
        $searchQuery = $request->input('search');
        $categoryFilter = $request->input('category');

        if ($format === 'pdf') {
            return $this->exportToPdf($books, $searchQuery, $categoryFilter);
        } else {
            return $this->exportToExcel($books, $searchQuery, $categoryFilter);
        }
    }

    private function exportToPdf($books, ?string $searchQuery, ?string $categoryFilter): Response
    {
        $html = view('admin.books.export-pdf', [
            'books' => $books,
            'searchQuery' => $searchQuery,
            'categoryFilter' => $categoryFilter,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'books_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportToExcel($books, ?string $searchQuery, ?string $categoryFilter): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $export = new class($books, $searchQuery, $categoryFilter) implements FromCollection, WithHeadings, WithMapping {
            private $books;
            private $searchQuery;
            private $categoryFilter;

            public function __construct($books, ?string $searchQuery, ?string $categoryFilter)
            {
                $this->books = $books;
                $this->searchQuery = $searchQuery;
                $this->categoryFilter = $categoryFilter;
            }

            public function collection(): Collection
            {
                return $this->books->map(function ($book, $index) {
                    return [
                        '#' => $index + 1,
                        'ISBN' => $book->isbn ?? '—',
                        'Book Name' => $book->book_name ?? '—',
                        'Authors' => $book->authors_name ?? '—',
                        'Category' => $book->category ?? '—',
                        'Book Shelf' => $book->book_shelf ?? '—',
                        'Copyright' => $book->copyright ?? '—',
                        'Stock Quantity' => $book->stock_quantity ?? 0,
                        'Publication' => $book->publication_name ?? '—',
                    ];
                });
            }

            public function headings(): array
            {
                $headings = [
                    '#',
                    'ISBN',
                    'Book Name',
                    'Authors',
                    'Category',
                    'Book Shelf',
                    'Copyright',
                    'Stock Quantity',
                    'Publication',
                ];

                $info = [
                    ['Books List'],
                ];

                if ($this->categoryFilter) {
                    $info[] = ['Category: ' . $this->categoryFilter];
                }

                if ($this->searchQuery) {
                    $info[] = ['Search: ' . $this->searchQuery];
                }

                $info[] = ['Total Records: ' . $this->books->count()];
                $info[] = ['Generated: ' . Carbon::now('Asia/Manila')->format('F d, Y h:i A')];
                $info[] = []; // Empty row for spacing

                return array_merge($info, [$headings]);
            }

            public function map($row): array
            {
                return array_values($row);
            }
        };

        $filename = 'books_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }
}

