@extends('layout.admin.app')

@section('title', 'My Profile | OnShelf GTDL')
@section('page_title', 'My Profile')

@section('content')
    <div
        x-data="{
            showChangePasswordModal: false,
            isChangingPassword: false,
            formErrors: {},
            successMessage: '',
            showSuccessMessage: false,
            openChangePasswordModal() {
                this.showChangePasswordModal = true;
                this.formErrors = {};
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeChangePasswordModal() {
                this.showChangePasswordModal = false;
                this.formErrors = {};
            },
            async submitChangePassword(event) {
                this.isChangingPassword = true;
                this.formErrors = {};

                const form = event.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token'),
                        },
                        body: formData,
                        credentials: 'same-origin',
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.formErrors = Object.fromEntries(
                                Object.entries(data.errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
                            );
                            return;
                        }
                        this.formErrors = { general: data.message || 'Failed to change password. Please try again.' };
                        return;
                    }

                    this.successMessage = data.message || 'Password changed successfully.';
                    this.showSuccessMessage = true;
                    this.closeChangePasswordModal();
                    form.reset();
                    
                    setTimeout(() => {
                        this.showSuccessMessage = false;
                    }, 5000);
                } catch (error) {
                    this.formErrors = { general: 'Failed to change password. Please try again.' };
                } finally {
                    this.isChangingPassword = false;
                }
            }
        }"
        x-init="if (window.lucide) { lucide.createIcons(); }"
        class="space-y-6"
    >
        {{-- Profile Header --}}
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-8 shadow-sm">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                {{-- Profile Picture --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-full border-4 border-[#f3cbe0] bg-gradient-to-br from-[#fde7f0] to-[#fff7fb] flex items-center justify-center overflow-hidden shadow-lg">
                        <img
                            src="{{ asset('img/dp.jpg') }}"
                            alt="{{ html_entity_decode($user->userInfo->full_name ?? $user->email) }}"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                {{-- Profile Information --}}
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold text-[#4b2036] mb-2">
                        {{ html_entity_decode($user->userInfo->full_name ?? $user->email) }}
                    </h1>
                    <p class="text-lg text-[#7c4c63] mb-4">
                        {{ ucwords(str_replace('_', ' ', $user->role->display_name ?? $user->role->name ?? 'User')) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-[#4b2036] mb-6 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-[#a03464]"></i>
                Personal Information
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Full Name</p>
                    <p class="text-base text-[#4b2036]">{{ html_entity_decode($user->userInfo->full_name ?? '—') }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Email</p>
                    <p class="text-base text-[#4b2036]">{{ $user->email }}</p>
                </div>
                @if($user->userInfo && $user->userInfo->mobile)
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Mobile</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->mobile }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Address Information --}}
        @if($user->userInfo && ($user->userInfo->house_no || $user->userInfo->street_name || $user->userInfo->barangay))
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-[#4b2036] mb-6 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-[#a03464]"></i>
                Address Information
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">House No.</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->house_no ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Street Name</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->street_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Barangay</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->barangay ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Municipality</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->municipality ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Province</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->province ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Zipcode</p>
                    <p class="text-base text-[#4b2036]">{{ $user->userInfo->zipcode ?? '—' }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Account Information --}}
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-[#4b2036] flex items-center gap-2">
                    <i data-lucide="shield" class="w-5 h-5 text-[#a03464]"></i>
                    Account Information
                </h2>
                <button
                    type="button"
                    @click="openChangePasswordModal()"
                    class="inline-flex items-center gap-2 rounded-[14px] bg-[#a03464] px-4 py-2 text-sm font-semibold text-white hover:bg-[#821a4f] transition active:scale-95"
                >
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>Change Password</span>
                </button>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Account Status</p>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $user->deactivated ? 'bg-rose-50 text-rose-700' : 'bg-green-50 text-green-700' }}">
                        <i data-lucide="{{ $user->deactivated ? 'x-circle' : 'check-circle' }}" class="w-3 h-3"></i>
                        {{ $user->deactivated ? 'Inactive' : 'Active' }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Role</p>
                    <p class="text-base text-[#4b2036]">{{ ucwords(str_replace('_', ' ', $user->role->display_name ?? $user->role->name ?? 'User')) }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Member Since</p>
                    <p class="text-base text-[#4b2036]">{{ $user->created_at->format('F d, Y') }}</p>
                </div>
                @if($user->last_login_at)
                <div>
                    <p class="text-sm font-semibold text-[#7c4c63] mb-1">Last Login</p>
                    <p class="text-base text-[#4b2036]">{{ $user->last_login_at->format('F d, Y h:i A') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Success Message --}}
        <div
            x-cloak
            x-show="showSuccessMessage"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
        >
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                <span x-text="successMessage"></span>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Change Password Modal --}}
        <div
            x-show="showChangePasswordModal"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeChangePasswordModal()"
        >
            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Change Password</h2>
                        <button @click="closeChangePasswordModal()" class="p-2 rounded-lg hover:bg-[#fff7fb] transition">
                            <i data-lucide="x" class="w-5 h-5 text-[#7c4c63]"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('admin.profile.change-password') }}" method="POST" class="p-6 space-y-4" @submit.prevent="submitChangePassword($event)">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-[#4b2036] mb-2">Current Password *</label>
                        <input
                            type="password"
                            name="current_password"
                            required
                            autofocus
                            class="w-full rounded-[14px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            placeholder="Enter current password"
                        >
                        <p class="mt-1 text-xs text-[#a03464]/70">
                            @error('current_password') {{ $message }} @enderror
                            <span x-show="formErrors.current_password" x-text="formErrors.current_password"></span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#4b2036] mb-2">New Password *</label>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            class="w-full rounded-[14px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            placeholder="Enter new password (min. 8 characters)"
                        >
                        <p class="mt-1 text-xs text-[#a03464]/70">
                            @error('password') {{ $message }} @enderror
                            <span x-show="formErrors.password" x-text="formErrors.password"></span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#4b2036] mb-2">Confirm New Password *</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            minlength="8"
                            class="w-full rounded-[14px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            placeholder="Confirm new password"
                        >
                        <p class="mt-1 text-xs text-[#a03464]/70">
                            @error('password_confirmation') {{ $message }} @enderror
                            <span x-show="formErrors.password_confirmation" x-text="formErrors.password_confirmation"></span>
                        </p>
                    </div>
                    <div x-show="formErrors.general" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <span x-text="formErrors.general"></span>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeChangePasswordModal()"
                            class="flex-1 rounded-[14px] border border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#4b2036] hover:bg-[#fff7fb] transition active:scale-95"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isChangingPassword"
                            class="flex-1 rounded-[14px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-4 py-3 text-sm font-semibold text-white hover:opacity-95 transition active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span x-show="!isChangingPassword">Change Password</span>
                            <span x-show="isChangingPassword" class="flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Changing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

