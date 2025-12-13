@extends('layout.authentication')

@section('title', 'Login | OnShelf GTDL')
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
                </div>
                @if (session('status'))
                    <div class="rounded-[8px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif
                <form action="{{ route('login.submit') }}" method="POST" class="flex flex-col gap-5" novalidate>
                    @csrf
                    <div class="space-y-3">
                        <div data-field>
                            <label for="lrn" class="text-sm font-medium text-[#4b2036]">Learner’s Reference Number</label>
                            <div class="mt-2">
                                <input
                                    id="lrn"
                                    name="lrn"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="12"
                                    placeholder="12-digit LRN"
                                    value="{{ old('lrn') }}"
                                    @class([
                                        'w-full rounded-[8px] border bg-white/80 px-3.5 py-2.5 sm:px-4 sm:py-3 focus:outline-none focus:ring-2',
                                        'border-[#f3cbe0] focus:ring-[#d96a9f]' => ! $errors->has('lrn'),
                                        'border-rose-400 focus:ring-rose-200' => $errors->has('lrn'),
                                    ])
                                    data-rule="lrn"
                                />
                            </div>
                            <p
                                class="validation-label"
                                data-feedback
                                data-state="{{ $errors->has('lrn') ? 'error' : 'idle' }}"
                            >
                                {{ $errors->first('lrn') }}
                            </p>
                        </div>

                        <div data-field>
                            <label for="password" class="text-sm font-medium text-[#4b2036]">Password</label>
                            <div class="mt-2 relative">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="Enter your password"
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
                    </div>

                    <div class="flex flex-row justify-between text-sm text-[#7c4c63]">
                        <label class="flex items-center gap-2 text-[#5e3147]">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-[#d69bbc] text-[#a03464]"
                                @checked(old('remember'))
                            />
                            Remember device
                        </label>
                        <a href="{{ route('password.request') }}" class="text-[#a03464] font-medium hover:text-[#7b1f46]">Forgot Password?</a>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-[8px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-8 py-3 text-xs sm:text-sm text-white font-semibold tracking-wide uppercase shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        Login
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
