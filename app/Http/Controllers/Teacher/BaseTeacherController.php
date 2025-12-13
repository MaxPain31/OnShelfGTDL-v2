<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

abstract class BaseTeacherController extends Controller
{
    protected function ensureTeacher(): void
    {
        $user = Auth::user();

        if (!$user || !$user->isTeacher()) {
            abort(403, 'Unauthorized access. Only teachers can access this page.');
        }
    }

    protected function ensureProfileSetup(): ?RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($this->needsProfileSetup($user)) {
            return redirect()
                ->route('teacher.profile.setup')
                ->with('status', 'Please complete your profile details to continue.');
        }

        return null;
    }

    protected function needsProfileSetup(User $user): bool
    {
        if (!$user->isTeacher()) {
            return false;
        }

        // Teachers might not need address setup, but we can add checks here if needed
        // For now, return false as teachers might not need profile setup
        return false;
    }
}

