<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function show(): View|RedirectResponse
    {
        $this->ensureAdminAccess();

        $user = Auth::user();
        $user->load(['userInfo', 'role']);

        return view('admin.profile', [
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'The current password is incorrect.',
                    'errors' => ['current_password' => ['The current password is incorrect.']]
                ], 422);
            }
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        // Update password
        $user->password = Hash::make($validated['password']);
        $user->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Password changed successfully.',
            ], 200);
        }

        return redirect()->route('admin.profile')->with('success', 'Password changed successfully.');
    }
}

