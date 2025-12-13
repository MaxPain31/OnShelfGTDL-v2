@extends('layout.authentication')

@section('title', 'Reset Password | OnShelf GTDL')
@section('body_class', 'overflow-y-hidden')

@section('content')
    <div class="w-full max-w-[480px] mx-auto px-2 sm:px-0">
        <section class="glass-panel rounded-[10px] p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center text-center gap-4">
                    <img
                        src="{{ asset('img/logo.png') }}"
                        class="w-24 h-24 sm:w-24 sm:h-24 object-contain drop-shadow-lg mx-auto"
                    />
                    <h2 class="text-xl font-semibold text-[#661d44] text-center sm:text-xl md:text-2xl lg:text-3xl">OnShelfGTDL</h2>
                    <p class="text-sm text-[#7c4c63]">Reset Password</p>
                </div>
                
                @if (session('status'))
                    <div class="rounded-[8px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <p class="text-sm text-[#7c4c63] text-center">
                    Enter your new password below.
                </p>

                <form action="{{ route('password.update') }}" method="POST" class="flex flex-col gap-5" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div data-field>
                        <label for="email-display" class="text-sm font-medium text-[#4b2036]">Email Address</label>
                        <div class="mt-2">
                            <input
                                id="email-display"
                                type="email"
                                value="{{ $email }}"
                                disabled
                                class="w-full rounded-[8px] border border-[#f3cbe0] bg-gray-100 px-3.5 py-2.5 sm:px-4 sm:py-3 focus:outline-none text-[#7c4c63]"
                            />
                        </div>
                    </div>

                    <div data-field>
                        <label for="password" class="text-sm font-medium text-[#4b2036]">New Password</label>
                        <div class="mt-2 relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Enter your new password"
                                required
                                minlength="8"
                                @class([
                                    'w-full rounded-[8px] border bg-white/80 px-3.5 py-2.5 sm:px-4 sm:py-3 pr-24 focus:outline-none focus:ring-2',
                                    'border-[#f3cbe0] focus:ring-[#d96a9f]' => ! $errors->has('password'),
                                    'border-rose-400 focus:ring-rose-200' => $errors->has('password'),
                                ])
                                data-rule="password"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 inline-flex items-center rounded-full border border-[#f3cbe0] bg-white/80 px-2 py-1 text-[10px] font-semibold tracking-wide text-[#a03464] hover:text-[#7b1f46] focus:outline-none"
                                data-toggle-password
                                data-target="#password"
                                aria-pressed="false"
                            >
                                <span class="sr-only">Toggle password visibility</span>
                                <span data-label-show>SHOW</span>
                                <span data-label-hide class="hidden">HIDE</span>
                            </button>
                        </div>
                        <p
                            class="validation-label"
                            data-feedback
                            data-state="{{ $errors->has('password') ? 'error' : 'idle' }}"
                        >
                            {{ $errors->first('password') }}
                        </p>
                    </div>

                    <div data-field>
                        <label for="password_confirmation" class="text-sm font-medium text-[#4b2036]">Confirm New Password</label>
                        <div class="mt-2 relative">
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="Confirm your new password"
                                required
                                minlength="8"
                                @class([
                                    'w-full rounded-[8px] border bg-white/80 px-3.5 py-2.5 sm:px-4 sm:py-3 pr-24 focus:outline-none focus:ring-2',
                                    'border-[#f3cbe0] focus:ring-[#d96a9f]' => ! $errors->has('password_confirmation'),
                                    'border-rose-400 focus:ring-rose-200' => $errors->has('password_confirmation'),
                                ])
                                data-rule="password"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 inline-flex items-center rounded-full border border-[#f3cbe0] bg-white/80 px-2 py-1 text-[10px] font-semibold tracking-wide text-[#a03464] hover:text-[#7b1f46] focus:outline-none"
                                data-toggle-password
                                data-target="#password_confirmation"
                                aria-pressed="false"
                            >
                                <span class="sr-only">Toggle password visibility</span>
                                <span data-label-show>SHOW</span>
                                <span data-label-hide class="hidden">HIDE</span>
                            </button>
                        </div>
                        <p
                            class="validation-label"
                            data-feedback
                            data-state="{{ $errors->has('password_confirmation') ? 'error' : 'idle' }}"
                        >
                            {{ $errors->first('password_confirmation') }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-[8px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-8 py-3 text-xs sm:text-sm text-white font-semibold tracking-wide uppercase shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        Reset Password
                    </button>
                </form>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-[#a03464] font-medium hover:text-[#7b1f46]">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

