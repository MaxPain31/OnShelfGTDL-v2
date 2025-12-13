<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
}

