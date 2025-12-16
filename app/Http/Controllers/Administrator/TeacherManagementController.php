<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Mail\TeacherAccountCreatedMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class TeacherManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $teachersQuery = User::query()
            ->with(['userInfo', 'role'])
            ->whereHas('role', fn ($query) => $query->where('name', 'Teacher'));

        if ($search = $request->input('search')) {
            $teachersQuery->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhereHas('userInfo', function ($relation) use ($search) {
                        $relation
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_number', 'like', "%{$search}%");
                    });
            });
        }

        $teachers = $teachersQuery
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.manage-users', [
            'userType' => 'teachers',
            'teachers' => $teachers,
        ]);
    }

    public function store(StoreTeacherRequest $request): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();

        $roleId = $this->teacherRoleId();

        try {
            $user = null;
            $plainPassword = $request->input('password');
            
            DB::transaction(function () use ($request, $roleId, &$user) {
                $user = User::create([
                    'role_id' => $roleId,
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'deactivated' => $request->input('status') === 'inactive',
                    'email_verified_at' => now(),
                ]);

                $user->userInfo()->create([
                    'first_name' => $request->input('first_name'),
                    'middle_name' => $request->input('middle_name'),
                    'last_name' => $request->input('last_name'),
                    'extension_name' => $request->input('extension_name'),
                    'employee_number' => $request->input('employee_number'),
                    'advisory_class' => $request->input('advisory_class'),
                    'mobile' => $request->input('mobile'),
                ]);
            });

            // Send email notification with credentials
            if ($user) {
                $user->load('userInfo');
                $teacherName = $user->userInfo->full_name ?? $request->input('first_name') . ' ' . $request->input('last_name');
                
                Mail::to($user->email)->send(new TeacherAccountCreatedMail(
                    $teacherName,
                    $user->email,
                    $plainPassword,
                    $request->input('employee_number')
                ));
            }
        } catch (\Exception $e) {
            Log::error('Failed to create teacher: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create teacher account. Please try again.',
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create teacher account. Please try again.']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Teacher account has been added.',
            ], 201);
        }

        return redirect()
            ->route('admin.manage-teachers')
            ->with('status', 'Teacher account has been added.');
    }

    public function edit(User $teacher): JsonResponse
    {
        $this->ensureAdminAccess();
        $this->ensureTeacher($teacher);

        $teacher->load('userInfo');

        return response()->json([
            'success' => true,
            'teacher' => [
                'id' => $teacher->id,
                'email' => $teacher->email,
                'status' => $teacher->deactivated ? 'inactive' : 'active',
                'user_info' => $teacher->userInfo ? [
                    'first_name' => $teacher->userInfo->first_name ?? '',
                    'middle_name' => $teacher->userInfo->middle_name ?? '',
                    'last_name' => $teacher->userInfo->last_name ?? '',
                    'extension_name' => $teacher->userInfo->extension_name ?? '',
                    'employee_number' => $teacher->userInfo->employee_number ?? '',
                    'advisory_class' => $teacher->userInfo->advisory_class ?? '',
                    'mobile' => $teacher->userInfo->mobile ?? '',
                ] : [
                    'first_name' => '',
                    'middle_name' => '',
                    'last_name' => '',
                    'extension_name' => '',
                    'employee_number' => '',
                    'advisory_class' => '',
                    'mobile' => '',
                ],
            ],
        ]);
    }

    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse|JsonResponse
    {
        $this->ensureAdminAccess();
        $this->ensureTeacher($teacher);

        DB::transaction(function () use ($request, $teacher) {
            $teacher->fill([
                'email' => $request->input('email'),
                'deactivated' => $request->input('status') === 'inactive',
            ]);

            if ($request->filled('password')) {
                $teacher->password = Hash::make($request->input('password'));
            }

            $teacher->save();

            $teacher->userInfo()->updateOrCreate(
                ['user_id' => $teacher->id],
                [
                    'first_name' => $request->input('first_name'),
                    'middle_name' => $request->input('middle_name'),
                    'last_name' => $request->input('last_name'),
                    'extension_name' => $request->input('extension_name'),
                    'employee_number' => $request->input('employee_number'),
                    'advisory_class' => $request->input('advisory_class'),
                    'mobile' => $request->input('mobile'),
                ]
            );
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Teacher account has been updated.',
            ]);
        }

        return redirect()
            ->route('admin.manage-teachers')
            ->with('status', 'Teacher account has been updated.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        $this->ensureAdminAccess();
        $this->ensureTeacher($teacher);
        DB::transaction(function () use ($teacher) {
            // Remove related records first to avoid orphaned rows.
            $teacher->userInfo()->delete();
            // If you add more relations in the future (e.g., tokens, sessions), remove them here.
        $teacher->delete();
        });

        return redirect()
            ->route('admin.manage-teachers')
            ->with('status', 'Teacher account has been deleted.');
    }

    public function toggleStatus(User $teacher): RedirectResponse
    {
        $this->ensureAdminAccess();
        $this->ensureTeacher($teacher);

        $teacher->update([
            'deactivated' => ! $teacher->deactivated,
        ]);

        return back()->with('status', 'Teacher status has been updated.');
    }

    private function teacherRoleId(): int
    {
        static $roleId;

        if (! $roleId) {
            $roleId = Role::where('name', 'Teacher')->value('id');
        }

        abort_if(! $roleId, 500, 'Teacher role is not configured.');

        return $roleId;
    }

    private function ensureTeacher(User $user): void
    {
        $user->loadMissing('role');

        abort_unless($user->role && $user->role->name === 'Teacher', 404);
    }

    public function export(Request $request, string $format)
    {
        $this->ensureAdminAccess();

        $validFormats = ['pdf', 'excel'];
        abort_if(!in_array($format, $validFormats), 404, 'Invalid export format.');

        // Get the same filtered data as the index page
        $teachersQuery = User::query()
            ->with(['userInfo', 'role'])
            ->whereHas('role', fn ($query) => $query->where('name', 'Teacher'));

        if ($search = $request->input('search')) {
            $teachersQuery->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhereHas('userInfo', function ($relation) use ($search) {
                        $relation
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_number', 'like', "%{$search}%");
                    });
            });
        }

        $teachers = $teachersQuery->orderByDesc('id')->get();
        $searchQuery = $request->input('search');

        if ($format === 'pdf') {
            return $this->exportToPdf($teachers, $searchQuery);
        } else {
            return $this->exportToExcel($teachers, $searchQuery);
        }
    }

    private function exportToPdf($teachers, ?string $searchQuery): Response
    {
        $html = view('admin.users.export-teachers-pdf', [
            'teachers' => $teachers,
            'searchQuery' => $searchQuery,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'teachers_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportToExcel($teachers, ?string $searchQuery): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data = $teachers->map(function ($teacher, $index) {
            return [
                '#' => $index + 1,
                'name' => $teacher->userInfo->full_name ?? '—',
                'email' => $teacher->email,
                'employee_number' => $teacher->userInfo->employee_number ?? '—',
                'advisory_class' => $teacher->userInfo->advisory_class ?? '—',
                'mobile' => $teacher->userInfo->mobile ?? '—',
                'status' => $teacher->deactivated ? 'Inactive' : 'Active',
            ];
        })->toArray();

        $export = new class($data) implements FromCollection, WithHeadings, WithMapping {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection(): Collection
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return ['#', 'Name', 'Email', 'Employee Number', 'Advisory Class', 'Mobile', 'Status'];
            }

            public function map($row): array
            {
                return array_values($row);
            }
        };

        $filename = 'teachers_list_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }

    private function ensureAdminAccess(): void
    {
        $user = auth()->user();

        abort_if(! $user || ! $user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }
}
