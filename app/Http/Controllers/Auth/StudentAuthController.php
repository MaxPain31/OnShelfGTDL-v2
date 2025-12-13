<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Services\Auth\StudentAuthService;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentAuthController extends Controller
{
    public function __construct(private readonly StudentAuthService $studentAuthService)
    {
    }

    public function home(Request $request): RedirectResponse
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

        // If not authenticated, redirect to student login
        return redirect()->route('login');
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

        return view('authentication.login');
    }

    public function register(Request $request): RedirectResponse
    {
        $userType = $request->input('user_type', 'student');

        $rules = [
            'user_type' => ['required', 'in:student,teacher'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:100'],
            'adviser' => ['required', 'string', 'max:255'],
            'zipcode' => ['required', 'string', 'max:10'],
            'house_no' => ['required', 'string', 'max:100'],
            'street_name' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['nullable', 'regex:/^(\+639|09)\d{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Add role-specific validation rules
        if ($userType === 'student') {
            $rules['lrn'] = ['required', 'digits:12', Rule::unique('user_info', 'lrn')];
            $rules['grade'] = ['required', 'string', 'max:50'];
        } elseif ($userType === 'teacher') {
            $rules['lrn'] = ['required', 'string', 'max:50', Rule::unique('user_info', 'employee_number')];
            $rules['grade'] = ['required', 'string', 'max:50']; // This will be stored as advisory_class
        }

        $validated = $request->validate($rules);

        $this->studentAuthService->register($validated);

        $request->session()->regenerate();

        $redirectRoute = $userType === 'teacher' ? 'teacher.dashboard' : 'student.dashboard';
        $message = $userType === 'teacher'
            ? 'Welcome to OnShelf GTDL! Your teacher account is now active.'
            : 'Welcome to OnShelf GTDL! Your account is now active.';

        return redirect()->route($redirectRoute)->with('status', $message);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'lrn' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if ($this->studentAuthService->login($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var User|null $user */
            $user = Auth::user();
            
            if ($user instanceof User && $user->isTeacher()) {
                return redirect()->route('teacher.dashboard')->with('status', 'You are now signed in.');
            }

            // Check if student needs profile setup
            if ($user instanceof User && $user->isStudent() && $this->needsProfileSetup($user)) {
                return redirect()
                    ->route('student.profile.setup')
                    ->with('status', 'Welcome! Please complete your address details to continue.');
            }

            return redirect()->route('student.dashboard')->with('status', 'You are now signed in.');
        }

        return back()
            ->withErrors(['password' => __('These credentials do not match our records.')])
            ->withInput($request->only('lrn'));
    }

    public function showProfileSetup(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user || !$user->isStudent()) {
            abort(403, 'Unauthorized access. Only students can access this page.');
        }

        if (!$this->needsProfileSetup($user)) {
            return redirect()->route('student.dashboard');
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

        if (!$user || !$user->isStudent()) {
            abort(403, 'Unauthorized access. Only students can access this page.');
        }

        if (!$this->needsProfileSetup($user)) {
            return redirect()->route('student.dashboard');
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

        return redirect()
            ->route('student.dashboard')
            ->with('status', 'Profile completed. Welcome!');
    }

    public function showForgotPassword()
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

        return view('authentication.forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Generate token
        $token = Str::random(64);

        // Delete existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Get user name for email
        $userName = $user->userInfo->full_name ?? $user->email;

        // Send email
        Mail::to($user->email)->send(new PasswordResetMail($token, $user->email, $userName));

        return back()->with('status', 'We have emailed your password reset link!');
    }

    public function showResetPassword(Request $request, string $token)
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

        $email = $request->query('email');

        return view('authentication.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Find the password reset record
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Check if token is valid (within 60 minutes)
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Verify token
        if (!Hash::check($request->token, $passwordReset->token)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid password reset token.']);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the password reset token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully! You can now login with your new password.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Something went wrong. Please try again.']);
    }

    private function needsProfileSetup(User $user): bool
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

