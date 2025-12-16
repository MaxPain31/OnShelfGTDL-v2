<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    private function ensureAdminAccess(): void
    {
        $user = Auth::user();
        abort_if(!$user || !$user->role || $user->role->name !== 'Administrator', 403, 'Unauthorized access.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdminAccess();

        $query = Attendance::query()->orderBy('visit_date', 'desc')->orderBy('visit_time', 'desc');

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        } else {
            // Default to today (Philippine time)
            $query->whereDate('visit_date', Carbon::today('Asia/Manila'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('visitor_name', 'like', '%' . $request->search . '%');
        }

        $attendances = $query->with('recorder')->paginate(20);

        return view('admin.manage-attendance', [
            'attendances' => $attendances,
            'selectedDate' => $request->input('date', Carbon::today('Asia/Manila')->format('Y-m-d')),
            'searchQuery' => $request->input('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
        ]);

        $now = Carbon::now('Asia/Manila');
        $validated['visit_date'] = $now->format('Y-m-d');
        $validated['visit_time'] = $now->format('H:i:s');
        $validated['recorded_by'] = Auth::id();

        Attendance::create($validated);

        return redirect()->route('admin.manage-attendance', ['date' => $validated['visit_date']])
            ->with('success', 'Attendance recorded successfully.');
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
        ]);

        $attendance->update($validated);

        return redirect()->route('admin.manage-attendance', ['date' => $attendance->visit_date->format('Y-m-d')])
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->ensureAdminAccess();

        $visitDate = $attendance->visit_date->format('Y-m-d');
        $attendance->delete();

        return redirect()->route('admin.manage-attendance', ['date' => $visitDate])
            ->with('success', 'Attendance deleted successfully.');
    }

    public function export(Request $request, string $format)
    {
        $this->ensureAdminAccess();

        $validFormats = ['pdf', 'excel'];
        abort_if(!in_array($format, $validFormats), 404, 'Invalid export format.');

        // Get the same filtered data as the index page
        $query = Attendance::query()->orderBy('visit_date', 'desc')->orderBy('visit_time', 'desc');

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        } else {
            $query->whereDate('visit_date', Carbon::today('Asia/Manila'));
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('visitor_name', 'like', '%' . $request->search . '%');
        }

        $attendances = $query->with('recorder')->get();

        $selectedDate = $request->input('date', Carbon::today('Asia/Manila')->format('Y-m-d'));
        $searchQuery = $request->input('search');

        if ($format === 'pdf') {
            return $this->exportToPdf($attendances, $selectedDate, $searchQuery);
        } else {
            return $this->exportToExcel($attendances, $selectedDate, $searchQuery);
        }
    }

    private function exportToPdf($attendances, string $selectedDate, ?string $searchQuery): Response
    {
        $html = view('admin.attendance.export-pdf', [
            'attendances' => $attendances,
            'selectedDate' => $selectedDate,
            'searchQuery' => $searchQuery,
            'generatedAt' => Carbon::now('Asia/Manila')->format('F d, Y h:i A'),
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'attendance_list_' . $selectedDate . '_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportToExcel($attendances, string $selectedDate, ?string $searchQuery): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data = $attendances->map(function ($attendance, $index) {
            return [
                '#' => $index + 1,
                'visitor_name' => $attendance->visitor_name,
                'visit_date' => $attendance->visit_date->format('M d, Y'),
                'visit_time' => $attendance->visit_time ? Carbon::parse($attendance->visit_time)->format('h:i A') : '—',
                'recorded_by' => $attendance->recorder->userInfo->full_name ?? $attendance->recorder->email ?? '—',
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
                return ['#', 'Visitor Name', 'Date', 'Time', 'Recorded By'];
            }

            public function map($row): array
            {
                return array_values($row);
            }
        };

        $filename = 'attendance_list_' . $selectedDate . '_' . Carbon::now('Asia/Manila')->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
