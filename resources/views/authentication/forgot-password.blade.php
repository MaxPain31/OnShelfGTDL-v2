@extends('layout.authentication')

@section('title', 'Forgot Password | OnShelf GTDL')
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
                    <p class="text-sm text-[#7c4c63]">Forgot Password</p>
                </div>
                
                @if (session('status'))
                    <div class="rounded-[8px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <p class="text-sm text-[#7c4c63] text-center">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form action="{{ route('password.email') }}" method="POST" class="flex flex-col gap-5" novalidate>
                    @csrf
                    <div data-field>
                        <label for="email" class="text-sm font-medium text-[#4b2036]">Email Address</label>
                        <div class="mt-2">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="Enter your email address"
                                value="{{ old('email') }}"
                                required
                                @class([
                                    'w-full rounded-[8px] border bg-white/80 px-3.5 py-2.5 sm:px-4 sm:py-3 focus:outline-none focus:ring-2',
                                    'border-[#f3cbe0] focus:ring-[#d96a9f]' => ! $errors->has('email'),
                                    'border-rose-400 focus:ring-rose-200' => $errors->has('email'),
                                ])
                                data-rule="email"
                            />
                        </div>
                        <p
                            class="validation-label"
                            data-feedback
                            data-state="{{ $errors->has('email') ? 'error' : 'idle' }}"
                        >
                            {{ $errors->first('email') }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-[8px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-8 py-3 text-xs sm:text-sm text-white font-semibold tracking-wide uppercase shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        Send Reset Link
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

