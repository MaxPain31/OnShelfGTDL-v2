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

        $ebooks = $ebooksQuery->orderByDesc('id')->paginate(9)->withQueryString();
        
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
}

