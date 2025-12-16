<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EbookManagementController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();

        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $ebooksQuery = Ebook::query();
        $editingEbook = null;

        if ($search = $request->input('search')) {
            $ebooksQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('authors', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $ebooksQuery->where('category', $request->input('category'));
        }

        if ($request->filled('edit')) {
            $editingEbook = Ebook::find($request->integer('edit'));
        }

        $ebooks = $ebooksQuery->orderByDesc('id')->paginate(12)->withQueryString();

        // Get distinct categories for filter dropdown
        $categories = Ebook::distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.manage-ebooks', [
            'ebooks' => $ebooks,
            'editingEbook' => $editingEbook,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'authors' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ebook_file' => ['nullable', 'file', 'mimes:pdf,epub,mobi'],
            'ebook_image' => ['nullable', 'image'],
        ]);

        if ($request->hasFile('ebook_file')) {
            $validated['ebook_file_path'] = $request->file('ebook_file')->store('ebooks/files', 'public');
        }

        if ($request->hasFile('ebook_image')) {
            $validated['ebook_image_path'] = $request->file('ebook_image')->store('ebooks/images', 'public');
        }

        Ebook::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'E-book has been added.']);
        }

        return back()->with('status', 'E-book has been added.');
    }

    public function update(Request $request, Ebook $ebook): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'authors' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ebook_file' => ['nullable', 'file', 'mimes:pdf,epub,mobi'],
            'ebook_image' => ['nullable', 'image'],
        ]);

        if ($request->hasFile('ebook_file')) {
            if ($ebook->ebook_file_path) {
                Storage::disk('public')->delete($ebook->ebook_file_path);
            }
            $validated['ebook_file_path'] = $request->file('ebook_file')->store('ebooks/files', 'public');
        }

        if ($request->hasFile('ebook_image')) {
            if ($ebook->ebook_image_path) {
                Storage::disk('public')->delete($ebook->ebook_image_path);
            }
            $validated['ebook_image_path'] = $request->file('ebook_image')->store('ebooks/images', 'public');
        }

        $ebook->update($validated);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'E-book has been updated.']);
        }

        return redirect()->route('admin.manage-ebooks')->with('status', 'E-book has been updated.');
    }

    public function destroy(Request $request, Ebook $ebook): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        if ($ebook->ebook_file_path) {
            Storage::disk('public')->delete($ebook->ebook_file_path);
        }

        if ($ebook->ebook_image_path) {
            Storage::disk('public')->delete($ebook->ebook_image_path);
        }

        $ebook->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'E-book has been deleted.']);
        }

        return redirect()->route('admin.manage-ebooks')->with('status', 'E-book has been deleted.');
    }

    public function export(Request $request, string $format): Response
    {
        $this->ensureAdminAccess();

        $validFormats = ['pdf', 'excel'];
        abort_if(!in_array($format, $validFormats), 404, 'Invalid export format.');

        $ebooksQuery = Ebook::query();

        if ($search = $request->input('search')) {
            $ebooksQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('authors', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $ebooksQuery->where('category', $request->input('category'));
        }

        $ebooks = $ebooksQuery->orderByDesc('id')->get();
        $searchQuery = $request->input('search');
        $categoryFilter = $request->input('category');

        if ($format === 'pdf') {
            return $this->exportToPdf($ebooks, $searchQuery, $categoryFilter);
        } else {
            return $this->exportToExcel($ebooks, $searchQuery, $categoryFilter);
        }
    }

    private function exportToPdf($ebooks, ?string $searchQuery, ?string $categoryFilter): Response
    {
        $html = view('admin.ebooks.export-pdf', [
            'ebooks' => $ebooks,
            'searchQuery' => $searchQuery,
            'categoryFilter' => $categoryFilter,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'ebooks_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportToExcel($ebooks, ?string $searchQuery, ?string $categoryFilter): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $export = new class($ebooks, $searchQuery, $categoryFilter) implements FromCollection, WithHeadings, WithMapping {
            private $ebooks;
            private $searchQuery;
            private $categoryFilter;

            public function __construct($ebooks, ?string $searchQuery, ?string $categoryFilter)
            {
                $this->ebooks = $ebooks;
                $this->searchQuery = $searchQuery;
                $this->categoryFilter = $categoryFilter;
            }

            public function collection(): Collection
            {
                return $this->ebooks->map(function ($ebook, $index) {
                    return [
                        '#' => $index + 1,
                        'Title' => $ebook->title ?? '—',
                        'Authors' => $ebook->authors ?? '—',
                        'Category' => $ebook->category ?? '—',
                        'Description' => $ebook->description ? substr(strip_tags($ebook->description), 0, 100) . '...' : '—',
                    ];
                });
            }

            public function headings(): array
            {
                $headings = [
                    '#',
                    'Title',
                    'Authors',
                    'Category',
                    'Description',
                ];

                $info = [
                    ['E-Books List'],
                ];

                if ($this->categoryFilter) {
                    $info[] = ['Category: ' . $this->categoryFilter];
                }

                if ($this->searchQuery) {
                    $info[] = ['Search: ' . $this->searchQuery];
                }

                $info[] = ['Total Records: ' . $this->ebooks->count()];
                $info[] = ['Generated: ' . Carbon::now('Asia/Manila')->format('F d, Y h:i A')];
                $info[] = []; // Empty row for spacing

                return array_merge($info, [$headings]);
            }

            public function map($row): array
            {
                return array_values($row);
            }
        };

        $filename = 'ebooks_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }
}

