@extends('layout.admin.app')

@section('title', 'Manage Attendance | OnShelf GTDL')
@section('page_title', 'Manage Attendance')

@section('content')
    <div
        x-data="{
            showAddModal: false,
            showEditModal: false,
            showDeleteModal: false,
            deletingAttendanceId: null,
            deletingAttendanceName: '',
            editingAttendance: null,
            searchQuery: '{{ $searchQuery ?? '' }}',
            selectedDate: '{{ $selectedDate }}',
            isSubmitting: false,
            message: '',
            messageType: 'success',
            openAddModal() {
                this.showAddModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeAddModal() {
                this.showAddModal = false;
            },
            openEditModal(attendance) {
                this.editingAttendance = attendance;
                this.showEditModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            formatDate(dateString) {
                if (!dateString) return '—';
                const date = new Date(dateString);
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
            },
            formatTime(timeString) {
                if (!timeString) return '—';
                const time = timeString.substring(0, 5);
                const [hours, minutes] = time.split(':');
                const hour12 = hours % 12 || 12;
                const ampm = hours >= 12 ? 'PM' : 'AM';
                return `${hour12}:${minutes} ${ampm}`;
            },
            closeEditModal() {
                this.showEditModal = false;
                this.editingAttendance = null;
            },
            openDeleteModal(id, name) {
                this.deletingAttendanceId = id;
                this.deletingAttendanceName = name;
                this.showDeleteModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deletingAttendanceId = null;
                this.deletingAttendanceName = '';
            },
            confirmDelete() {
                if (!this.deletingAttendanceId) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('admin.manage-attendance.destroy', ':id') }}`.replace(':id', this.deletingAttendanceId);

                const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_token';
                csrfField.value = csrfToken;
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        }"
        class="space-y-6"
    >
        {{-- Header with Actions --}}
        <div class="flex flex-col lg:flex-row items-start lg:items-center lg:justify-between gap-4 px-3 sm:px-0">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-3 md:gap-4 w-full md:w-auto">
                <form method="GET" action="{{ route('admin.manage-attendance') }}" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <input
                        type="date"
                        name="date"
                        value="{{ $selectedDate }}"
                        onchange="this.form.submit()"
                        class="rounded-full border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                    >
                    <input
                        type="text"
                        name="search"
                        value="{{ $searchQuery }}"
                        placeholder="Search by name..."
                        class="rounded-full border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f] w-full md:w-auto md:min-w-[200px]"
                    >
                    <button type="submit" class="hidden md:inline-flex items-center gap-2 rounded-full bg-[#a03464] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#821a4f] transition active:scale-95">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Search</span>
                    </button>
                </form>
            </div>
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 md:gap-3 w-full md:w-auto">
                <div class="flex gap-2">
                    <a
                        href="{{ route('admin.manage-attendance.export', ['format' => 'pdf', 'date' => $selectedDate, 'search' => $searchQuery]) }}"
                        class="inline-flex items-center gap-2 rounded-[14px] bg-red-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-red-700 transition active:scale-95 justify-center"
                        title="Export to PDF"
                    >
                        <i data-lucide="file-text" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span class="hidden sm:inline">PDF</span>
                    </a>
                    <a
                        href="{{ route('admin.manage-attendance.export', ['format' => 'excel', 'date' => $selectedDate, 'search' => $searchQuery]) }}"
                        class="inline-flex items-center gap-2 rounded-[14px] bg-green-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-green-700 transition active:scale-95 justify-center"
                        title="Export to Excel"
                    >
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                </div>
                <button
                    type="button"
                    @click="openAddModal()"
                    class="inline-flex items-center gap-2 rounded-[14px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:opacity-95 transition active:scale-95 w-full sm:w-auto justify-center"
                >
                    <i data-lucide="plus" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    <span>Add Attendance</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mx-3 sm:mx-0 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Mobile & Tablet Card Layout --}}
        <div class="lg:hidden space-y-3 px-3">
            @forelse($attendances as $attendance)
                <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-base text-[#4b2036]">{{ $attendance->visitor_name }}</h3>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="openEditModal({
                                    id: {{ $attendance->id }},
                                    visitor_name: '{{ addslashes($attendance->visitor_name) }}',
                                    visit_date: '{{ $attendance->visit_date->format('Y-m-d') }}',
                                    visit_time: '{{ $attendance->visit_time ? \Carbon\Carbon::parse($attendance->visit_time)->format('H:i:s') : '' }}'
                                })"
                                class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition active:scale-95"
                                title="Edit"
                            >
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <button
                                type="button"
                                @click="openDeleteModal({{ $attendance->id }}, '{{ addslashes($attendance->visitor_name) }}')"
                                class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition active:scale-95"
                                title="Delete"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs pt-2 border-t border-[#f7d6e6]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#7c4c63] font-medium">Date:</span>
                            <span class="text-[#4b2036]">{{ $attendance->visit_date->format('M d, Y') }}</span>
                        </div>
                        @if($attendance->visit_time)
                        <div class="flex items-center justify-between">
                            <span class="text-[#7c4c63] font-medium">Time:</span>
                            <span class="text-[#4b2036]">{{ \Carbon\Carbon::parse($attendance->visit_time)->format('h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-3 py-6 text-center text-sm text-[#7c4c63]">
                    No attendance records found for this date.
                </div>
            @endforelse
        </div>

        {{-- Desktop Table Layout --}}
        <div class="hidden lg:block rounded-[24px] border border-[#f3cbe0] bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">Visitor Name</th>
                            <th class="px-6 py-4 whitespace-nowrap">Date</th>
                            <th class="px-6 py-4 whitespace-nowrap">Time</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse($attendances as $attendance)
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                <td class="px-6 py-4 font-semibold">{{ $attendance->visitor_name }}</td>
                                <td class="px-6 py-4">{{ $attendance->visit_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">{{ $attendance->visit_time ? \Carbon\Carbon::parse($attendance->visit_time)->format('h:i A') : '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal({
                                                id: {{ $attendance->id }},
                                                visitor_name: '{{ addslashes($attendance->visitor_name) }}',
                                                visit_date: '{{ $attendance->visit_date->format('Y-m-d') }}',
                                                visit_time: '{{ $attendance->visit_time ? \Carbon\Carbon::parse($attendance->visit_time)->format('H:i:s') : '' }}'
                                            })"
                                            class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition active:scale-95"
                                            title="Edit"
                                        >
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openDeleteModal({{ $attendance->id }}, '{{ addslashes($attendance->visitor_name) }}')"
                                            class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition active:scale-95"
                                            title="Delete"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-[#7c4c63]">
                                    No attendance records found for this date.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($attendances->hasPages())
            <div class="px-3 sm:px-0">
                {{ $attendances->links() }}
            </div>
        @endif

        {{-- Add Modal --}}
        <div
            x-show="showAddModal"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeAddModal()"
        >
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Add Attendance</h2>
                        <button @click="closeAddModal()" class="p-2 rounded-lg hover:bg-[#fff7fb] transition">
                            <i data-lucide="x" class="w-5 h-5 text-[#7c4c63]"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('admin.manage-attendance.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-[#4b2036] mb-2">Visitor Name *</label>
                        <input
                            type="text"
                            name="visitor_name"
                            required
                            autofocus
                            class="w-full rounded-[14px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            placeholder="Enter visitor name"
                        >
                        <p class="mt-2 text-xs text-[#7c4c63]">Date and time will be automatically recorded when you click "Add Attendance"</p>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeAddModal()"
                            class="flex-1 rounded-[14px] border border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#4b2036] hover:bg-[#fff7fb] transition active:scale-95"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 rounded-[14px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-4 py-3 text-sm font-semibold text-white hover:opacity-95 transition active:scale-95"
                        >
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div
            x-show="showEditModal"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeEditModal()"
        >
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Edit Attendance</h2>
                        <button @click="closeEditModal()" class="p-2 rounded-lg hover:bg-[#fff7fb] transition">
                            <i data-lucide="x" class="w-5 h-5 text-[#7c4c63]"></i>
                        </button>
                    </div>
                </div>
                <template x-if="editingAttendance">
                    <form :action="`{{ route('admin.manage-attendance.update', ':id') }}`.replace(':id', editingAttendance.id)" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-[#4b2036] mb-2">Visitor Name *</label>
                            <input
                                type="text"
                                name="visitor_name"
                                :value="editingAttendance.visitor_name"
                                required
                                autofocus
                                class="w-full rounded-[14px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            >
                        </div>
                        <div class="text-xs text-[#7c4c63] bg-[#fff7fb] p-3 rounded-lg border border-[#f3cbe0]">
                            <p class="font-medium mb-1">Recorded Information:</p>
                            <p>Date: <span x-text="formatDate(editingAttendance.visit_date)"></span></p>
                            <p x-show="editingAttendance.visit_time">Time: <span x-text="formatTime(editingAttendance.visit_time)"></span></p>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="closeEditModal()"
                                class="flex-1 rounded-[14px] border border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#4b2036] hover:bg-[#fff7fb] transition active:scale-95"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 rounded-[14px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-4 py-3 text-sm font-semibold text-white hover:opacity-95 transition active:scale-95"
                            >
                                Update
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div
            x-show="showDeleteModal"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeDeleteModal()"
        >
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-md" @click.stop>
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Confirm Delete</h2>
                        <button @click="closeDeleteModal()" class="p-2 rounded-lg hover:bg-[#fff7fb] transition">
                            <i data-lucide="x" class="w-5 h-5 text-[#7c4c63]"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-[#4b2036] mb-2">
                                Are you sure you want to delete the attendance record for
                                <span class="font-semibold" x-text="deletingAttendanceName"></span>?
                            </p>
                            <p class="text-xs text-[#7c4c63]">This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeDeleteModal()"
                            class="flex-1 rounded-[14px] border border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#4b2036] hover:bg-[#fff7fb] transition active:scale-95"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="confirmDelete()"
                            class="flex-1 rounded-[14px] bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700 transition active:scale-95"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

