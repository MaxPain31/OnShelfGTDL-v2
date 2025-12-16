@extends('layout.admin.app')

@php
    $isTeacherView = ($userType ?? 'students') === 'teachers';
    $pageTitle = $isTeacherView ? 'Manage Teachers' : 'Manage Students';
    $labelClass = 'block text-xs font-semibold text-[#7c4c63] mb-1';
    $inputClass = 'w-full rounded-[10px] border border-[#f3cbe0] bg-[#fff7fb] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]';
@endphp

@section('title', $pageTitle . ' | OnShelf GTDL')
@section('page_title', $pageTitle)

@section('content')
    <style>
        input[type="search"].custom-search::-webkit-search-cancel-button {
            -webkit-appearance: none;
        }
    </style>
    <div
        x-data="{
            showAddModal: {{ $errors->any() && old('_token') ? 'true' : 'false' }},
            showEditModal: false,
            editingTeacher: null,
            isSavingTeacher: false,
            isUpdatingTeacher: false,
            formErrors: {},
            statusMessage: {{ json_encode(session('status') ?? '') }},
            searchQuery: '{{ request('search', '') }}',
            showConfirmModal: false,
            confirmAction: { formId: null, message: '' },
            openConfirm(formId, message) {
                this.confirmAction = { formId, message };
                this.showConfirmModal = true;
            },
            closeConfirm() {
                this.confirmAction = { formId: null, message: '' };
                this.showConfirmModal = false;
            },
            submitConfirm() {
                if (this.confirmAction.formId) {
                    const form = document.getElementById(this.confirmAction.formId);
                    if (form) form.submit();
                }
                this.closeConfirm();
            },
            async refreshTeacherTable() {
                try {
                    const response = await fetch('{{ route('admin.manage-teachers') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        credentials: 'same-origin',
                    });

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newBody = doc.querySelector('#teacher-table-body');
                    const newMeta = doc.querySelector('#teacher-meta');
                    const newMobileCards = doc.querySelector('#teacher-mobile-cards');

                    if (newBody && this.$refs.teacherTableBody) {
                        this.$refs.teacherTableBody.innerHTML = newBody.innerHTML;
                    }
                    if (newMobileCards && this.$refs.teacherMobileCards) {
                        this.$refs.teacherMobileCards.innerHTML = newMobileCards.innerHTML;
                    }
                    if (newMeta && this.$refs.teacherMeta) {
                        this.$refs.teacherMeta.innerHTML = newMeta.innerHTML;
                    }

                    // Reinitialize icons after DOM update
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                } catch (error) {
                    console.error('Failed to refresh teacher table', error);
                }
            },
            async submitAddTeacher(event) {
                this.isSavingTeacher = true;
                this.formErrors = {};

                const form = event.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    const data = response.status === 204 ? {} : await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.formErrors = Object.fromEntries(
                                Object.entries(data.errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
                            );
                            return;
                        }

                        this.statusMessage = data.message || 'Failed to save teacher. Please try again.';
                        return;
                    }

                    this.statusMessage = data.message || 'Teacher account has been added.';
                    this.showAddModal = false;
                    form.reset();
                    await this.refreshTeacherTable();
                } catch (error) {
                    this.statusMessage = 'Failed to save teacher. Please try again.';
                } finally {
                    this.isSavingTeacher = false;
                }
            },
            async openEditModal(teacherId) {
                this.showEditModal = true;
                this.formErrors = {};
                this.editingTeacher = null;

                try {
                    const response = await fetch(`{{ route('admin.manage-teachers') }}/${teacherId}/edit`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.editingTeacher = data.teacher;
                        // Initialize icons after data is loaded
                        this.$nextTick(() => {
                            if (window.lucide) {
                                lucide.createIcons();
                            }
                        });
                    } else {
                        this.statusMessage = 'Failed to load teacher data.';
                        this.showEditModal = false;
                    }
                } catch (error) {
                    console.error('Failed to load teacher data', error);
                    this.statusMessage = 'Failed to load teacher data.';
                    this.showEditModal = false;
                }
            },
            closeEditModal() {
                this.showEditModal = false;
                this.editingTeacher = null;
                this.formErrors = {};
            },
            async submitEditTeacher(event) {
                if (!this.editingTeacher) return;

                this.isUpdatingTeacher = true;
                this.formErrors = {};

                const form = event.target;
                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '',
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    const data = response.status === 204 ? {} : await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.formErrors = Object.fromEntries(
                                Object.entries(data.errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
                            );
                            return;
                        }

                        this.statusMessage = data.message || 'Failed to update teacher. Please try again.';
                        return;
                    }

                    this.statusMessage = data.message || 'Teacher account has been updated.';
                    this.closeEditModal();
                    await this.refreshTeacherTable();
                } catch (error) {
                    this.statusMessage = 'Failed to update teacher. Please try again.';
                } finally {
                    this.isUpdatingTeacher = false;
                }
            }
        }"
        x-effect="if ((showAddModal || showEditModal || showConfirmModal) && window.lucide) { lucide.createIcons(); }"
        x-init="@if(session('status') && !$errors->any()) showAddModal = false; @endif"
    >
        <div
            x-cloak
            x-show="statusMessage"
            x-transition
            class="rounded-2xl border border-green-200 bg-green-50 px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm text-green-800 mb-4 sm:mb-6"
        >
            <span x-text="statusMessage"></span>
        </div>

        @if ($isTeacherView)

            {{-- Teacher table --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 border-b border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                    <h2 class="text-base sm:text-lg font-semibold text-[#4b2036]">Teacher Accounts</h2>
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto">
                        <div class="flex gap-2">
                            <a
                                href="{{ route('admin.manage-teachers.export', ['format' => 'pdf', 'search' => request('search')]) }}"
                                class="inline-flex items-center gap-2 rounded-[14px] bg-red-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-red-700 transition active:scale-95 justify-center"
                                title="Export to PDF"
                            >
                                <i data-lucide="file-text" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span class="hidden sm:inline">PDF</span>
                            </a>
                            <a
                                href="{{ route('admin.manage-teachers.export', ['format' => 'excel', 'search' => request('search')]) }}"
                                class="inline-flex items-center gap-2 rounded-[14px] bg-green-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-green-700 transition active:scale-95 justify-center"
                                title="Export to Excel"
                            >
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span class="hidden sm:inline">Excel</span>
                            </a>
                        </div>
                        <form method="GET" action="{{ route('admin.manage-teachers') }}" class="relative w-full md:w-auto">
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                x-model="searchQuery"
                                placeholder="Search"
                                class="custom-search w-full md:w-auto rounded-full border border-[#f3cbe0] bg-[#fff7fb] pr-12 pl-4 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            />
                            <div class="absolute inset-y-0 right-2 flex items-center">
                                <button
                                    type="button"
                                    x-show="searchQuery"
                                    @click="searchQuery=''; $event.target.closest('form').submit();"
                                    class="flex h-7 w-7 items-center justify-center rounded-full text-[#a03464]/70 hover:text-[#821a4f] focus:outline-none"
                                    aria-label="Clear search"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
                                    </svg>
                                </button>
                                <button
                                    type="submit"
                                    x-show="!searchQuery"
                                    class="flex h-7 w-7 items-center justify-center rounded-full text-[#a03464]/70 hover:text-[#821a4f] focus:outline-none"
                                    aria-label="Search"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                        <button
                            type="button"
                            @click="showAddModal = true"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#a03464] px-4 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-md hover:bg-[#821a4f] w-full sm:w-auto"
                        >
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span>Add Teacher</span>
                        </button>
                    </div>
                </div>

                {{-- Mobile & Tablet Card Layout --}}
                <div class="lg:hidden min-h-[570px] space-y-3 px-3 py-4" id="teacher-mobile-cards" x-ref="teacherMobileCards">
                    @forelse (($teachers ?? collect()) as $teacher)
                        @php
                            $info = $teacher->userInfo;
                        @endphp
                        <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-sm text-[#4b2036] truncate">{{ $info->full_name ?? 'Unknown' }}</h3>
                                    <p class="text-xs text-[#7c4c63] mt-0.5 truncate">{{ $teacher->email }}</p>
                                </div>
                                <span class="{{ $teacher->deactivated ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700' }} px-2.5 py-1 text-xs font-semibold rounded-full whitespace-nowrap ml-2">
                                    {{ $teacher->deactivated ? 'Inactive' : 'Active' }}
                                </span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#7c4c63] font-medium">Employee No.:</span>
                                    <span class="text-[#4b2036] font-semibold">{{ $info->employee_number ?? '—' }}</span>
                                </div>
                                @if($info->advisory_class)
                                <div class="flex items-center justify-between">
                                    <span class="text-[#7c4c63] font-medium">Advisory Class:</span>
                                    <span class="text-[#4b2036]">{{ $info->advisory_class }}</span>
                                </div>
                                @endif
                                @if($info->mobile)
                                <div class="flex items-center justify-between">
                                    <span class="text-[#7c4c63] font-medium">Mobile:</span>
                                    <span class="text-[#4b2036]">{{ $info->mobile }}</span>
                                </div>
                                @endif
                                @if($teacher->last_login_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-[#7c4c63] font-medium">Last Login:</span>
                                    <span class="text-[#4b2036]">{{ $teacher->last_login_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#f7d6e6]">
                                <form id="status-form-mobile-{{ $teacher->id }}" method="POST" action="{{ route('admin.manage-teachers.status', $teacher) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                        title="{{ $teacher->deactivated ? 'Activate' : 'Deactivate' }}"
                                        @click="openConfirm('status-form-mobile-{{ $teacher->id }}', '{{ $teacher->deactivated ? 'Activate this teacher?' : 'Deactivate this teacher?' }}')"
                                    >
                                        <i data-lucide="{{ $teacher->deactivated ? 'check' : 'slash' }}" class="h-4 w-4"></i>
                                        <span class="sr-only">{{ $teacher->deactivated ? 'Activate' : 'Deactivate' }}</span>
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    @click="openEditModal({{ $teacher->id }})"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                    title="Edit"
                                >
                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                    <span class="sr-only">Edit</span>
                                </button>
                                <form id="delete-form-mobile-{{ $teacher->id }}" method="POST" action="{{ route('admin.manage-teachers.destroy', $teacher) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="button"
                                        class="flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50 active:scale-95 transition-transform"
                                        title="Delete"
                                        @click="openConfirm('delete-form-mobile-{{ $teacher->id }}', 'Delete this teacher account?')"
                                    >
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        <span class="sr-only">Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-6 text-center text-sm text-[#7c4c63]">
                            No teacher accounts found.
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table Layout --}}
                <div class="hidden lg:block overflow-x-auto min-h-[570px]">
                    <table class="min-w-full text-left text-sm text-[#4b2036]">
                        <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">Employee No.</th>
                                <th class="px-6 py-3 whitespace-nowrap">Full Name</th>
                                <th class="px-6 py-3 whitespace-nowrap hidden lg:table-cell">Advisory Class</th>
                                <th class="px-6 py-3 whitespace-nowrap">Email</th>
                                <th class="px-6 py-3 whitespace-nowrap hidden lg:table-cell">Mobile</th>
                                <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                <th class="px-6 py-3 whitespace-nowrap hidden xl:table-cell">Last Login</th>
                                <th class="px-6 py-3 whitespace-nowrap hidden xl:table-cell">Created At</th>
                                <th class="px-6 py-3 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teacher-table-body" x-ref="teacherTableBody" class="divide-y divide-[#f7d6e6]">
                            @forelse (($teachers ?? collect()) as $teacher)
                                @php
                                    $info = $teacher->userInfo;
                                    $initial = $info && $info->first_name ? mb_strtoupper(mb_substr($info->first_name, 0, 1)) : 'T';
                                @endphp
                                <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                    <td class="px-6 py-3 font-semibold whitespace-nowrap text-sm">{{ $info->employee_number ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $info->full_name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm hidden lg:table-cell">{{ $info->advisory_class ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $teacher->email }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm hidden lg:table-cell">{{ $info->mobile ?? '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="{{ $teacher->deactivated ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700' }} px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ $teacher->deactivated ? 'Inactive' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm hidden xl:table-cell">{{ $teacher->last_login_at ? $teacher->last_login_at->format('M d, Y h:i A') : '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm hidden xl:table-cell">{{ $teacher->created_at ? $teacher->created_at->format('M d, Y h:i A') : '—' }}</td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <form id="status-form-{{ $teacher->id }}" method="POST" action="{{ route('admin.manage-teachers.status', $teacher) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="button"
                                                    class="flex h-8 w-8 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                                    title="{{ $teacher->deactivated ? 'Activate' : 'Deactivate' }}"
                                                    @click="openConfirm('status-form-{{ $teacher->id }}', '{{ $teacher->deactivated ? 'Activate this teacher?' : 'Deactivate this teacher?' }}')"
                                                >
                                                    <i data-lucide="{{ $teacher->deactivated ? 'check' : 'slash' }}" class="h-4 w-4"></i>
                                                    <span class="sr-only">{{ $teacher->deactivated ? 'Activate' : 'Deactivate' }}</span>
                                                </button>
                                            </form>
                                            <button
                                                type="button"
                                                @click="openEditModal({{ $teacher->id }})"
                                                class="flex h-8 w-8 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                                title="Edit"
                                            >
                                                <i data-lucide="pencil" class="h-4 w-4"></i>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <form
                                                id="delete-form-{{ $teacher->id }}"
                                                method="POST"
                                                action="{{ route('admin.manage-teachers.destroy', $teacher) }}"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="button"
                                                    class="flex h-8 w-8 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50 active:scale-95 transition-transform"
                                                    title="Delete"
                                                    @click="openConfirm('delete-form-{{ $teacher->id }}', 'Delete this teacher account?')"
                                                >
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    <span class="sr-only">Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-6 text-center text-sm text-[#7c4c63]">
                                        No teacher accounts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="teacher-meta" x-ref="teacherMeta" class="border-t border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                    @php
                        $prevUrl = $teachers->previousPageUrl();
                        $nextUrl = $teachers->nextPageUrl();
                    @endphp
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-[#7c4c63]">
                        <p class="leading-tight text-center sm:text-left">
                            @if ($teachers->total())
                                Showing {{ $teachers->firstItem() }} to {{ $teachers->lastItem() }} of {{ $teachers->total() }} teachers
                            @else
                                Showing 0 to 0 of 0 teachers
                            @endif
                        </p>
                        <div class="flex items-center justify-center gap-2">
                            <a
                                href="{{ $prevUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $prevUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                                @if(!$prevUrl) aria-disabled="true" @endif
                            >
                                Previous
                            </a>
                            <span class="text-[#a03464] font-semibold px-2">{{ $teachers->currentPage() }}</span>
                            <a
                                href="{{ $nextUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $nextUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                                @if(!$nextUrl) aria-disabled="true" @endif
                            >
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add teacher modal --}}
            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="showAddModal"
                    class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/40 px-2 sm:px-4 py-4 sm:py-8"
                >
                    <div
                        x-on:click.away="showAddModal = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-8"
                        class="w-full max-w-4xl max-h-[95vh] rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0] flex flex-col"
                    >
                        <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 border-b border-[#f3cbe0] flex-shrink-0">
                            <div>
                                <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#4b2036] leading-tight">Add Teacher</h2>
                            </div>
                            <button
                                type="button"
                                class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                @click="showAddModal = false"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span class="sr-only">Close</span>
                            </button>
                        </div>
                        <div class="px-3 sm:px-6 pb-3 sm:pb-6 overflow-y-auto flex-1">
                            <form
                                method="POST"
                                action="{{ route('admin.manage-teachers.store') }}"
                                class="space-y-4"
                                @submit.prevent="submitAddTeacher($event)"
                            >
                                @csrf
                            <div class="grid gap-4 sm:gap-5 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">First Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="{{ $inputClass }}" placeholder="Juan">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('first_name') {{ $message }} @enderror
                                        <span x-show="formErrors.first_name" x-text="formErrors.first_name"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Middle Name</label>
                                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="{{ $inputClass }}" placeholder="Santos">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Last Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="{{ $inputClass }}" placeholder="Dela Cruz">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('last_name') {{ $message }} @enderror
                                        <span x-show="formErrors.last_name" x-text="formErrors.last_name"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Suffix</label>
                                    <input type="text" name="extension_name" value="{{ old('extension_name') }}" class="{{ $inputClass }}" placeholder="Jr., III, etc.">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('extension_name') {{ $message }} @enderror
                                        <span x-show="formErrors.extension_name" x-text="formErrors.extension_name"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Employee No. <span class="text-rose-500">*</span></label>
                                    <input type="text" name="employee_number" value="{{ old('employee_number') }}" class="{{ $inputClass }}" placeholder="e.g., TCH-2024-001">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('employee_number') {{ $message }} @enderror
                                        <span x-show="formErrors.employee_number" x-text="formErrors.employee_number"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Advisory Class</label>
                                    <input type="text" name="advisory_class" value="{{ old('advisory_class') }}" class="{{ $inputClass }}" placeholder="Grade 10 - Rizal">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Email <span class="text-rose-500">*</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="{{ $inputClass }}" placeholder="name@example.com">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('email') {{ $message }} @enderror
                                        <span x-show="formErrors.email" x-text="formErrors.email"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Mobile</label>
                                    <input type="text" name="mobile" value="{{ old('mobile') }}" class="{{ $inputClass }}" placeholder="+639XX-XXX-XXXX">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('mobile') {{ $message }} @enderror
                                        <span x-show="formErrors.mobile" x-text="formErrors.mobile"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Password <span class="text-rose-500">*</span></label>
                                    <input type="password" name="password" class="{{ $inputClass }}" placeholder="Create a strong password">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('password') {{ $message }} @enderror
                                        <span x-show="formErrors.password" x-text="formErrors.password"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Confirm Password <span class="text-rose-500">*</span></label>
                                    <input type="password" name="password_confirmation" class="{{ $inputClass }}" placeholder="Re-enter password">
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('password_confirmation') {{ $message }} @enderror
                                        <span x-show="formErrors.password_confirmation" x-text="formErrors.password_confirmation"></span>
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">Status <span class="text-rose-500">*</span></label>
                                    <select name="status" class="{{ $inputClass }}">
                                        <option value="active" @selected(old('status') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                    </select>
                                    <p class="text-xs text-[#a03464]/70">
                                        @error('status') {{ $message }} @enderror
                                        <span x-show="formErrors.status" x-text="formErrors.status"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-[#f3cbe0] mt-4">
                                <button
                                    type="button"
                                    class="rounded-[12px] border border-[#f3cbe0] px-6 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                    @click="showAddModal = false"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#a03464] px-6 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] disabled:cursor-not-allowed disabled:opacity-70 active:scale-95 transition-transform"
                                    :disabled="isSavingTeacher"
                                >
                                    <svg
                                        x-show="isSavingTeacher"
                                        class="h-4 w-4 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                                        <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                                    </svg>
                                    <span x-text="isSavingTeacher ? 'Saving...' : 'Save'"></span>
                                </button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Edit teacher modal --}}
            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="showEditModal"
                    class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/40 px-2 sm:px-4 py-4 sm:py-8"
                >
                    <div
                        x-on:click.away="closeEditModal"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-8"
                        class="w-full max-w-4xl max-h-[95vh] rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0] flex flex-col"
                    >
                        <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 border-b border-[#f3cbe0] flex-shrink-0">
                            <div>
                                <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#4b2036] leading-tight">Edit Teacher</h2>
                            </div>
                            <button
                                type="button"
                                class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                @click="closeEditModal"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span class="sr-only">Close</span>
                            </button>
                        </div>
                        <div class="px-3 sm:px-6 pb-3 sm:pb-6 overflow-y-auto flex-1" x-show="editingTeacher">
                            <form
                                method="POST"
                                :action="editingTeacher ? '{{ route('admin.manage-teachers.update', ':id') }}'.replace(':id', editingTeacher.id) : ''"
                                class="space-y-4"
                                @submit.prevent="submitEditTeacher($event)"
                            >
                                @csrf
                                <div class="grid gap-4 sm:gap-5 md:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">First Name <span class="text-rose-500">*</span></label>
                                        <input type="text" name="first_name" x-model="editingTeacher?.user_info?.first_name" class="{{ $inputClass }}" placeholder="Juan" required>
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.first_name" x-text="formErrors.first_name"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Middle Name</label>
                                        <input type="text" name="middle_name" x-model="editingTeacher?.user_info?.middle_name" class="{{ $inputClass }}" placeholder="Santos">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Last Name <span class="text-rose-500">*</span></label>
                                        <input type="text" name="last_name" x-model="editingTeacher?.user_info?.last_name" class="{{ $inputClass }}" placeholder="Dela Cruz" required>
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.last_name" x-text="formErrors.last_name"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Suffix</label>
                                        <input type="text" name="extension_name" x-model="editingTeacher?.user_info?.extension_name" class="{{ $inputClass }}" placeholder="Jr., III, etc.">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Employee No. <span class="text-rose-500">*</span></label>
                                        <input type="text" name="employee_number" x-model="editingTeacher?.user_info?.employee_number" class="{{ $inputClass }}" placeholder="e.g., TCH-2024-001" required>
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.employee_number" x-text="formErrors.employee_number"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Advisory Class</label>
                                        <input type="text" name="advisory_class" x-model="editingTeacher?.user_info?.advisory_class" class="{{ $inputClass }}" placeholder="Grade 10 - Rizal">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Email <span class="text-rose-500">*</span></label>
                                        <input type="email" name="email" x-model="editingTeacher?.email" class="{{ $inputClass }}" placeholder="name@example.com" required>
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.email" x-text="formErrors.email"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Mobile</label>
                                        <input type="text" name="mobile" x-model="editingTeacher?.user_info?.mobile" class="{{ $inputClass }}" placeholder="+639XX-XXX-XXXX">
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.mobile" x-text="formErrors.mobile"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">New Password</label>
                                        <input type="password" name="password" class="{{ $inputClass }}" placeholder="Leave blank to keep current">
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.password" x-text="formErrors.password"></span>
                                        </p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="{{ $inputClass }}" placeholder="Leave blank to keep current">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="{{ $labelClass }}">Status <span class="text-rose-500">*</span></label>
                                        <select name="status" x-model="editingTeacher?.status" class="{{ $inputClass }}" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <p class="text-xs text-[#a03464]/70">
                                            <span x-show="formErrors.status" x-text="formErrors.status"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-[#f3cbe0] mt-4">
                                    <button
                                        type="button"
                                        class="rounded-[12px] border border-[#f3cbe0] px-6 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                        @click="closeEditModal"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#a03464] px-6 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] disabled:cursor-not-allowed disabled:opacity-70 active:scale-95 transition-transform"
                                        :disabled="isUpdatingTeacher"
                                    >
                                        <svg
                                            x-show="isUpdatingTeacher"
                                            class="h-4 w-4 animate-spin"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                                        </svg>
                                        <span x-text="isUpdatingTeacher ? 'Updating...' : 'Update Teacher'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div x-show="!editingTeacher" class="px-3 sm:px-6 pb-3 sm:pb-6">
                            <div class="flex items-center justify-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#a03464]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Confirm action modal --}}
            <template x-teleport="body">
                <div
                    x-cloak
                    x-show="showConfirmModal"
                    class="fixed inset-0 z-[1300] flex items-center justify-center bg-black/40 px-3 sm:px-4 py-4 sm:py-8"
                >
                    <div
                        x-on:click.away="closeConfirm"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-4"
                        class="w-full max-w-md rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0]"
                    >
                        <div class="px-4 sm:px-5 py-3 sm:py-4 flex items-center justify-end">
                            <button
                                type="button"
                                class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                @click="closeConfirm"
                            >
                                <i data-lucide="x" class="w-4 h-4"></i>
                                <span class="sr-only">Close</span>
                            </button>
                        </div>
                        <div class="py-3 sm:py-4 px-4 sm:px-5 text-center text-[#4b2036]">
                            <h3 class="text-base sm:text-lg font-semibold mb-2">Please Confirm</h3>
                            <p class="text-sm" x-text="confirmAction.message"></p>
                        </div>
                        <div class="px-4 sm:px-5 py-3 sm:py-4 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                            <button
                                type="button"
                                class="rounded-[10px] border border-[#f3cbe0] px-4 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                @click="closeConfirm"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-[10px] bg-[#a03464] px-4 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] active:scale-95 transition-transform"
                                @click="submitConfirm"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        @else
            {{-- Students view (admin read-only, no add) --}}
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white">
                <div class="px-3 sm:px-6 py-3 sm:py-4">
                    <h2 class="text-base sm:text-lg font-semibold text-[#4b2036] mb-4">Student Accounts</h2>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-3 mb-4">
                        <form method="GET" action="{{ route('admin.manage-students') }}" class="relative flex-1 md:flex-initial md:w-auto md:min-w-[250px]" x-data="{ searchQuery: '{{ request('search', '') }}' }">
                            <input
                                type="search"
                                name="search"
                                x-model="searchQuery"
                                placeholder="Search"
                                class="custom-search w-full rounded-full border border-[#f3cbe0] bg-[#fff7fb] pl-4 pr-10 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            />
                            <button
                                type="submit"
                                x-show="!searchQuery"
                                class="absolute inset-y-0 right-3 flex items-center text-[#a03464]/60 hover:text-[#a03464]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                x-show="searchQuery"
                                @click="searchQuery = ''; $el.closest('form').submit();"
                                class="absolute inset-y-0 right-3 flex items-center text-[#a03464]/60 hover:text-[#a03464]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                        <div class="flex gap-2">
                            <a
                                href="{{ route('admin.manage-students.export', ['format' => 'pdf', 'search' => request('search')]) }}"
                                class="inline-flex items-center gap-2 rounded-[14px] bg-red-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-red-700 transition active:scale-95 justify-center"
                                title="Export to PDF"
                            >
                                <i data-lucide="file-text" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span>PDF</span>
                            </a>
                            <a
                                href="{{ route('admin.manage-students.export', ['format' => 'excel', 'search' => request('search')]) }}"
                                class="inline-flex items-center gap-2 rounded-[14px] bg-green-600 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base font-semibold text-white hover:bg-green-700 transition active:scale-95 justify-center"
                                title="Export to Excel"
                            >
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span>Excel</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                    {{-- Mobile & Tablet Card Layout --}}
                    <div class="lg:hidden min-h-[570px] space-y-3">
                        @forelse (($students ?? collect()) as $student)
                            @php
                                $info = $student->userInfo;
                            @endphp
                            <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm text-[#4b2036] truncate">{{ $info->full_name ?? 'Unknown' }}</h3>
                                        <p class="text-xs text-[#7c4c63] mt-0.5 truncate">{{ $student->email }}</p>
                                    </div>
                                    <span class="{{ $student->deactivated ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700' }} px-2.5 py-1 text-xs font-semibold rounded-full whitespace-nowrap ml-2">
                                        {{ $student->deactivated ? 'Inactive' : 'Active' }}
                                    </span>
                                </div>
                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">LRN:</span>
                                        <span class="text-[#4b2036] font-semibold">{{ $info->lrn ?? '—' }}</span>
                                    </div>
                                    @if($info->mobile)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Mobile:</span>
                                        <span class="text-[#4b2036]">{{ $info->mobile }}</span>
                                    </div>
                                    @endif
                                    @if($student->last_login_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Last Login:</span>
                                        <span class="text-[#4b2036]">{{ $student->last_login_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    @endif
                                    @if($student->created_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#7c4c63] font-medium">Created:</span>
                                        <span class="text-[#4b2036]">{{ $student->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-3 py-6 text-center text-sm text-[#7c4c63]">
                                No student accounts found.
                            </div>
                        @endforelse
                    </div>

                    {{-- Desktop Table Layout --}}
                    <div class="hidden lg:block overflow-x-auto min-h-[570px]">
                        <table class="min-w-full text-left text-sm text-[#4b2036]">
                            <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                                <tr>
                                    <th class="px-6 py-3 whitespace-nowrap">LRN</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Full Name</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Email</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Mobile</th>
                                    <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f7d6e6]">
                                @forelse (($students ?? collect()) as $student)
                                    @php
                                        $info = $student->userInfo;
                                    @endphp
                                    <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])>
                                        <td class="px-6 py-3 font-semibold whitespace-nowrap text-sm">{{ $info->lrn ?? '—' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $info->full_name ?? 'Unknown' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $student->email }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $info->mobile ?? '—' }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap">
                                            <span class="{{ $student->deactivated ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700' }} px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ $student->deactivated ? 'Inactive' : 'Active' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-6 text-center text-sm text-[#7c4c63]">
                                            No student accounts found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="student-meta" class="border-t border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4">
                    @php
                        $prevUrl = $students->previousPageUrl();
                        $nextUrl = $students->nextPageUrl();
                    @endphp
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-[#7c4c63]">
                        <p class="leading-tight text-center sm:text-left">
                            @if ($students->total())
                                Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} students
                            @else
                                Showing 0 to 0 of 0 students
                            @endif
                        </p>
                        <div class="flex items-center justify-center gap-2">
                            <a
                                href="{{ $prevUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $prevUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                                @if(!$prevUrl) aria-disabled="true" @endif
                            >
                                Previous
                            </a>
                            <span class="text-[#a03464] font-semibold px-2">{{ $students->currentPage() }}</span>
                            <a
                                href="{{ $nextUrl ?: '#' }}"
                                class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $nextUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                                @if(!$nextUrl) aria-disabled="true" @endif
                            >
                                Next
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

