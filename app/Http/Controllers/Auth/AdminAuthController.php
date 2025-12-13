<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\AdminAuthService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function __construct(private readonly AdminAuthService $adminAuthService)
    {
    }

    public function showLogin(Request $request)
    {
        // If user is already authenticated, redirect to their dashboard
        if (Auth::check()) {
            /** @var User|null $user */
            $user = Auth::user();
            
            if ($user instanceof User) {
                if ($user->isStudent()) {
                    return redirect()->route('student.dashboard');
                } elseif ($user->isTeacher()) {
                    return redirect()->route('teacher.dashboard');
                } elseif ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }
            }
        }

        return view('authentication.admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = $this->adminAuthService->attempt($credentials['email'], $credentials['password']);

        if (! $admin) {
            throw ValidationException::withMessages([
                'email' => __('Invalid email or password.'),
            ])->redirectTo(route('admin.login'));
        }

        Auth::login($admin);
        $request->session()->regenerate();

        $adminName = $admin->userInfo ? $admin->userInfo->full_name : $admin->email;

        if ($this->needsProfileSetup($admin)) {
            $profileSetupRoute = ($admin->role && $admin->role->name === 'Teacher')
                ? 'teacher.profile.setup'
                : 'admin.profile.setup';

            return redirect()
                ->route($profileSetupRoute)
                ->with('status', 'Welcome! Please complete your address details to continue.');
        }

        // Redirect based on role
        if ($admin->role && $admin->role->name === 'Teacher') {
            return redirect()
                ->route('teacher.dashboard')
                ->with('status', 'Welcome back, ' . ($admin->userInfo->full_name ?? $admin->email) . '!');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Welcome back, ' . ($admin->userInfo->full_name ?? $admin->email) . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isStudent = $user && $user->isStudent();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect students to /login, admins/teachers to /admin/login
        if ($isStudent) {
            return redirect()->route('login')->with('status', 'Signed out successfully.');
        }

        return redirect()->route('admin.login')->with('status', 'Signed out successfully.');
    }

    public function showProfileSetup(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->needsProfileSetup($user)) {
            // Redirect based on role
            if ($user->role && $user->role->name === 'Teacher') {
                return redirect()->route('teacher.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        return view('auth.profile-setup', [
            'user' => $user,
            'info' => $user->userInfo,
        ]);
    }

    public function saveProfileSetup(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->needsProfileSetup($user)) {
            // Redirect based on role
            if ($user->role && $user->role->name === 'Teacher') {
                return redirect()->route('teacher.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        $data = $request->validate([
            'zipcode' => ['required', 'string', 'max:20'],
            'house_no' => ['required', 'string', 'max:50'],
            'street_name' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
        ]);

        $user->userInfo()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        // Redirect based on role
        if ($user->role && $user->role->name === 'Teacher') {
            return redirect()
                ->route('teacher.dashboard')
                ->with('status', 'Profile completed. Welcome!');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Profile completed. Welcome!');
    }

    private function needsProfileSetup(User $user): bool
    {
        if (! $user->role || $user->role->name !== 'Teacher') {
            return false;
        }

        $info = $user->userInfo;

        return ! $info
            || blank($info->zipcode)
            || blank($info->house_no)
            || blank($info->street_name)
            || blank($info->barangay)
            || blank($info->municipality)
            || blank($info->province);
    }
}

