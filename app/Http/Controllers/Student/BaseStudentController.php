<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

abstract class BaseStudentController extends Controller
{
    protected function ensureStudent(): void
    {
        $user = Auth::user();

        if (!$user || !$user->isStudent()) {
            abort(403, 'Unauthorized access. Only students can access this page.');
        }
    }

    protected function ensureProfileSetup(): ?RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($this->needsProfileSetup($user)) {
            return redirect()
                ->route('student.profile.setup')
                ->with('status', 'Please complete your address details to continue.');
        }

        return null;
    }

    protected function needsProfileSetup(User $user): bool
    {
        if (!$user->isStudent()) {
            return false;
        }

        $info = $user->userInfo;

        return !$info
            || blank($info->zipcode)
            || blank($info->house_no)
            || blank($info->street_name)
            || blank($info->barangay)
            || blank($info->municipality)
            || blank($info->province);
    }
}

