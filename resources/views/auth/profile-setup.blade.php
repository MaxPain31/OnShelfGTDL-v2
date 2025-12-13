<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile | OnShelf GTDL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Manrope', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fde7f0 0%, #f6e5ef 50%, #f8d9e9 100%);
            color: #4b2036;
        }
        .floating-label {
            transition: all 0.3s ease;
        }
        .form-group {
            position: relative;
        }
        .form-group input {
            transition: all 0.3s ease;
            padding-top: 1.5rem;
            padding-bottom: 0.5rem;
        }
        .form-group input:focus,
        .form-group input.has-value {
            padding-top: 1.75rem;
            padding-bottom: 0.25rem;
        }
        .form-group label {
            position: absolute;
            left: 3rem;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            pointer-events: none;
            background: transparent;
            padding: 0 0.25rem;
            z-index: 1;
        }
        .form-group input:focus ~ label,
        .form-group input.has-value ~ label {
            top: 0.5rem;
            left: 2.75rem;
            transform: scale(0.85);
            color: #a03464;
            background: #fff7fb;
            padding: 0 0.5rem;
        }
        .form-group input:focus ~ label {
            background: white;
        }
        .form-group input:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(160, 52, 100, 0.15);
        }
        .progress-bar {
            transition: width 0.5s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }
        .field-icon {
            transition: all 0.3s ease;
        }
        .form-group:focus-within .field-icon {
            color: #a03464;
            transform: scale(1.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10" x-data="{ submitting: false }">
    @php
        $user = auth()->user();
        $isTeacher = $user && $user->role && $user->role->name === 'Teacher';
        $isStudent = $user && $user->role && $user->role->name === 'Student';
        $info = $user->userInfo ?? null;
    @endphp

    <div class="w-full max-w-4xl animate-slide-in">
        <!-- Header with Progress -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#a03464] to-[#d96a9f] flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform">
                        <img src="{{ asset('img/logo.png') }}" alt="OnShelf GTDL" class="w-12 h-12 object-contain" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#7c4c63] opacity-80">OnShelf GTDL</p>
                        <h1 class="text-3xl font-bold text-[#4b2036]">Complete Your Profile</h1>
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2 border border-[#f3cbe0] shadow-sm">
                        <p class="text-sm font-semibold text-[#4b2036]">{{ $user->userInfo->full_name ?? $user->name ?? $user->email }}</p>
                        <p class="text-xs text-[#7c4c63]">{{ ucwords($user->role->display_name ?? $user->role->name ?? 'User') }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 border border-[#f3cbe0] shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-[#4b2036]">Setup Progress</span>
                    <span class="text-sm font-semibold text-[#a03464]" x-text="'Step 1 of 1'">Step 1 of 1</span>
                </div>
                <div class="w-full bg-[#f3cbe0] rounded-full h-2.5 overflow-hidden">
                    <div class="progress-bar bg-gradient-to-r from-[#a03464] to-[#d96a9f] h-full rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-green-300 bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-4 text-sm text-green-800 shadow-sm flex items-center gap-3 animate-slide-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white/90 backdrop-blur-lg rounded-3xl border border-[#f3cbe0] shadow-[0_20px_60px_rgba(160,52,100,0.15)] overflow-hidden">
            <div class="bg-gradient-to-r from-[#a03464] to-[#d96a9f] px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">Address Information</h2>
                        <p class="text-sm text-white/90 mt-1">Let's set up your address details</p>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route($isTeacher ? 'teacher.profile.setup.save' : ($isStudent ? 'student.profile.setup.save' : 'admin.profile.setup.save')) }}"
                class="p-8"
                @submit="submitting = true"
            >
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Zip Code -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="hash" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="zipcode"
                                id="zipcode"
                                value="{{ old('zipcode', $info->zipcode ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('zipcode', $info->zipcode ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="zipcode" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                Zip Code <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('zipcode')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- House No -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="home" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="house_no"
                                id="house_no"
                                value="{{ old('house_no', $info->house_no ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('house_no', $info->house_no ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="house_no" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                House No. <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('house_no')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Street Name -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="navigation" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="street_name"
                                id="street_name"
                                value="{{ old('street_name', $info->street_name ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('street_name', $info->street_name ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="street_name" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                Street Name <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('street_name')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Barangay -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="barangay"
                                id="barangay"
                                value="{{ old('barangay', $info->barangay ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('barangay', $info->barangay ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="barangay" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                Barangay <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('barangay')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Municipality -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="map" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="municipality"
                                id="municipality"
                                value="{{ old('municipality', $info->municipality ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('municipality', $info->municipality ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="municipality" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                Municipality <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('municipality')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Province -->
                    <div class="form-group floating-label">
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 field-icon text-[#7c4c63]">
                                <i data-lucide="globe" class="w-5 h-5"></i>
                            </div>
                            <input
                                type="text"
                                name="province"
                                id="province"
                                value="{{ old('province', $info->province ?? '') }}"
                                class="w-full pl-12 pr-4 rounded-xl border-2 border-[#f3cbe0] bg-[#fff7fb] text-[#4b2036] focus:outline-none focus:border-[#a03464] focus:bg-white transition-all {{ old('province', $info->province ?? '') ? 'has-value' : '' }}"
                                required
                                x-on:input="$el.classList.toggle('has-value', $el.value.length > 0)"
                            >
                            <label for="province" class="absolute left-12 top-1/2 -translate-y-1/2 text-[#7c4c63] font-medium pointer-events-none origin-left">
                                Province <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        @error('province')
                            <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end gap-3">
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="group relative inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[#a03464] to-[#d96a9f] px-8 py-4 text-sm font-semibold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <span x-show="!submitting">Save and Continue</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                        <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform" x-show="!submitting"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center">
            <p class="text-sm text-[#7c4c63] opacity-70">
                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                This information is required to complete your profile setup
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
