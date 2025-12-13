@php
    $user = auth()->user();
    $isTeacher = $user && $user->role && $user->role->name === 'Teacher';
    $layout = $isTeacher ? 'layout.teacher.app' : 'layout.admin.app';
    $labelClass = 'block text-xs font-semibold text-[#7c4c63] mb-1';
    $inputClass = 'w-full rounded-[10px] border border-[#f3cbe0] bg-[#fff7fb] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]';
@endphp

@extends($layout)

@section('title', 'Complete Profile | OnShelf GTDL')
@section('page_title', 'Complete Profile')

@section('content')
    <div class="space-y-6 max-w-4xl">
        @if (session('status'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6">
            <h2 class="text-lg font-semibold text-[#4b2036] mb-4">Set up your address</h2>
            <p class="text-sm text-[#7c4c63] mb-4">
                Please complete your address details to continue to your dashboard.
            </p>
            <form method="POST" action="{{ route($isTeacher ? 'teacher.profile.setup.save' : 'admin.profile.setup.save') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Zip Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="zipcode" value="{{ old('zipcode', $info->zipcode ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('zipcode') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">House No. <span class="text-rose-500">*</span></label>
                        <input type="text" name="house_no" value="{{ old('house_no', $info->house_no ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('house_no') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Street Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="street_name" value="{{ old('street_name', $info->street_name ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('street_name') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Barangay <span class="text-rose-500">*</span></label>
                        <input type="text" name="barangay" value="{{ old('barangay', $info->barangay ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('barangay') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Municipality <span class="text-rose-500">*</span></label>
                        <input type="text" name="municipality" value="{{ old('municipality', $info->municipality ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('municipality') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Province <span class="text-rose-500">*</span></label>
                        <input type="text" name="province" value="{{ old('province', $info->province ?? '') }}" class="{{ $inputClass }}" required>
                        <p class="text-xs text-[#a03464]/70">@error('province') {{ $message }} @enderror</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="submit"
                        class="rounded-[12px] bg-[#a03464] px-6 py-2 text-sm font-semibold text-white hover:bg-[#821a4f]"
                    >
                        Save and Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

