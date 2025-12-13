<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RulesManagementController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $rulesQuery = Rule::query();
        $editingRule = null;

        if ($search = $request->input('search')) {
            $rulesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('edit')) {
            $editingRule = Rule::find($request->integer('edit'));
        }

        $rules = $rulesQuery->ordered()->paginate(10)->withQueryString();

        return view('admin.manage-rules', [
            'rules' => $rules,
            'editingRule' => $editingRule,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        Rule::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rule created successfully!',
            ]);
        }

        return redirect()
            ->route('admin.manage-rules')
            ->with('success', 'Rule created successfully!');
    }

    public function update(Request $request, Rule $rule): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['order'] = $validated['order'] ?? $rule->order;

        $rule->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rule updated successfully!',
            ]);
        }

        return redirect()
            ->route('admin.manage-rules')
            ->with('success', 'Rule updated successfully!');
    }

    public function destroy(Rule $rule): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $rule->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rule deleted successfully!',
            ]);
        }

        return redirect()
            ->route('admin.manage-rules')
            ->with('success', 'Rule deleted successfully!');
    }

    public function toggleStatus(Rule $rule): JsonResponse
    {
        $this->ensureAdminAccess();

        $rule->update([
            'is_active' => !$rule->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rule status updated successfully!',
            'is_active' => $rule->is_active,
        ]);
    }
}

