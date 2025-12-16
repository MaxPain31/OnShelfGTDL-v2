<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Mail\StudentAccountCreatedMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class StudentManagementController extends Controller
{
    private function ensureTeacher(): void
    {
        $user = Auth::user();

        if (!$user || !$user->role || $user->role->name !== 'Teacher') {
            abort(403, 'Unauthorized access. Only teachers can access this page.');
        }
    }

    public function index(Request $request): View
    {
        $this->ensureTeacher();

        $studentsQuery = User::query()
            ->with(['userInfo', 'role'])
            ->whereHas('role', fn ($query) => $query->where('name', 'Student'));

        if ($search = $request->input('search')) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhereHas('userInfo', function ($relation) use ($search) {
                        $relation
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('lrn', 'like', "%{$search}%");
                    });
            });
        }

        $students = $studentsQuery
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher.manage-students', [
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureTeacher();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', 'string', 'max:20'],
            'lrn' => ['required', 'string', 'max:12', 'unique:user_info,lrn'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'grade' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Auto-generate password: LRN + Last Name
        $validated['password'] = $validated['lrn'] . $validated['last_name'];

        $roleId = Role::where('name', 'Student')->value('id');

        if (!$roleId) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Student role is not configured.'], 500);
            }
            return back()->withErrors(['error' => 'Student role is not configured.']);
        }

        try {
            $user = null;
            $plainPassword = $validated['password'];
            
            DB::transaction(function () use ($validated, $roleId, &$user) {
                $user = User::create([
                    'role_id' => $roleId,
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'deactivated' => $validated['status'] === 'inactive',
                    'email_verified_at' => now(),
                ]);

                $user->userInfo()->create([
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'last_name' => $validated['last_name'],
                    'extension_name' => $validated['extension_name'] ?? null,
                    'lrn' => $validated['lrn'],
                    'mobile' => $validated['mobile'] ?? null,
                    'grade' => $validated['grade'] ?? null,
                    'section' => $validated['section'] ?? null,
                ]);
            });

            // Send email notification with credentials
            if ($user) {
                $user->load('userInfo');
                $studentName = $user->userInfo->full_name ?? $validated['first_name'] . ' ' . $validated['last_name'];
                
                Mail::to($user->email)->send(new StudentAccountCreatedMail(
                    $studentName,
                    $user->email,
                    $plainPassword,
                    $validated['lrn']
                ));
            }
        } catch (\Exception $e) {
            Log::error('Failed to create student: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to create student account. Please try again.'], 500);
            }
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create student account. Please try again.']);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Student account has been added.']);
        }

        return redirect()
            ->route('teacher.manage-students')
            ->with('status', 'Student account has been added.');
    }

    public function edit(User $student): JsonResponse
    {
        $this->ensureTeacher();
        $this->ensureStudent($student);

        $student->load('userInfo');

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'email' => $student->email,
                'status' => $student->deactivated ? 'inactive' : 'active',
                'user_info' => $student->userInfo ? [
                    'first_name' => $student->userInfo->first_name ?? '',
                    'middle_name' => $student->userInfo->middle_name ?? '',
                    'last_name' => $student->userInfo->last_name ?? '',
                    'extension_name' => $student->userInfo->extension_name ?? '',
                    'lrn' => $student->userInfo->lrn ?? '',
                    'mobile' => $student->userInfo->mobile ?? '',
                    'grade' => $student->userInfo->grade ?? '',
                    'section' => $student->userInfo->section ?? '',
                ] : [
                    'first_name' => '',
                    'middle_name' => '',
                    'last_name' => '',
                    'extension_name' => '',
                    'lrn' => '',
                    'mobile' => '',
                    'grade' => '',
                    'section' => '',
                ],
            ],
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse|JsonResponse
    {
        $this->ensureTeacher();
        $this->ensureStudent($student);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'extension_name' => ['nullable', 'string', 'max:20'],
            'lrn' => ['required', 'string', 'max:12', 'unique:user_info,lrn,' . $student->userInfo->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $student->id],
            'mobile' => ['nullable', 'string', 'max:20'],
            'grade' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($validated, $student) {
            $student->fill([
                'email' => $validated['email'],
                'deactivated' => $validated['status'] === 'inactive',
            ]);

            if (!empty($validated['password'])) {
                $student->password = Hash::make($validated['password']);
            }

            $student->save();

            $student->userInfo()->updateOrCreate(
                ['user_id' => $student->id],
                [
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'last_name' => $validated['last_name'],
                    'extension_name' => $validated['extension_name'] ?? null,
                    'lrn' => $validated['lrn'],
                    'grade' => $validated['grade'] ?? null,
                    'section' => $validated['section'] ?? null,
                    'mobile' => $validated['mobile'] ?? null,
                ]
            );
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Student account has been updated.',
            ]);
        }

        return redirect()
            ->route('teacher.manage-students')
            ->with('status', 'Student account has been updated.');
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->ensureTeacher();
        $this->ensureStudent($student);

        DB::transaction(function () use ($student) {
            if ($student->userInfo) {
                $student->userInfo->delete();
            }
            $student->delete();
        });

        return redirect()
            ->route('teacher.manage-students')
            ->with('status', 'Student account has been deleted.');
    }

    public function toggleStatus(User $student): RedirectResponse
    {
        $this->ensureTeacher();
        $this->ensureStudent($student);

        $student->update([
            'deactivated' => !$student->deactivated,
        ]);

        return back()->with('status', 'Student status has been updated.');
    }

    private function ensureStudent(User $user): void
    {
        $user->loadMissing('role');

        abort_unless($user->role && $user->role->name === 'Student', 404);
    }
}

