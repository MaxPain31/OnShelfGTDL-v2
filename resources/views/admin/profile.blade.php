@extends('layout.admin.app')

@section('title', 'My Profile | OnShelf GTDL')
@section('page_title', 'My Profile')

@section('content')
    <div
        x-data="{}"
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
            <h2 class="text-xl font-semibold text-[#4b2036] mb-6 flex items-center gap-2">
                <i data-lucide="shield" class="w-5 h-5 text-[#a03464]"></i>
                Account Information
            </h2>
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
    </div>
@endsection

