@extends('layout.authentication')

@section('title', 'Register | OnShelf GTDL')
@section('body_class', 'overflow-y-auto')

@section('content')
    @php
        $fieldClass =
            'mt-2 w-full rounded-[8px] border bg-white/80 px-3.5 py-2.5 sm:px-4 sm:py-3 focus:ring-2 focus:outline-none';
        $errorFieldClass = $fieldClass . ' border-rose-400 focus:ring-rose-200';
        $defaultFieldClass = $fieldClass . ' border-[#f3cbe0] focus:ring-[#d96a9f]';
        $inputClass = function (string $field) use ($errors, $defaultFieldClass, $errorFieldClass) {
            return $errors->has($field) ? $errorFieldClass : $defaultFieldClass;
        };
        $state = fn(string $field) => $errors->has($field) ? 'error' : 'idle';
        $feedback = fn(string $field, string $default) => $errors->first($field) ?? $default;
    @endphp
    <div class="glass-panel rounded-[15px] p-6 sm:p-8 lg:p-12 space-y-8 sm:space-y-10">
        <div class="flex flex-col gap-3 text-center px-1">
            <button
                type="button"
                onclick="history.back()"
                class="self-start text-sm text-[#a03464] font-semibold hover:text-[#701c3f] inline-flex items-center gap-2"
            >
                <span aria-hidden="true">←</span>
                Back
            </button>
            <div class="flex flex-col items-center gap-3">
                <img
                    src="{{ asset('img/logo.png') }}"
                    class="w-32 h-32 object-contain drop-shadow-lg"
                />
                <h1 class="text-2xl sm:text-2xl lg:text-3xl font-semibold text-[#4b2036]">
                    Let’s create your account
                </h1>
            </div>
            <p class="text-[#6b4055] max-w-3xl mx-auto">
                Complete the form to create a student and stay connected with the library management system of Gen. Tiburcio De Leon National High School.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-[8px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-[8px] bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700" data-error-summary>
                Please review the highlighted fields.
            </div>
        @endif

        <form action="{{ route('register.submit') }}" method="POST" class="space-y-6 sm:space-y-8" novalidate>
            @csrf
            <input type="hidden" name="user_type" id="user_type" value="student" />

            <div class="flex flex-col gap-3">
                <label class="text-sm font-medium text-[#4b2036]">Account Type <span class="text-rose-500">*</span></label>
                <div class="flex gap-3">
                    <button
                        type="button"
                        id="btn-student"
                        class="flex-1 rounded-[8px] border-2 border-[#a03464] bg-[#a03464] text-white px-4 py-3 text-sm font-semibold transition-all hover:opacity-90"
                        data-account-type="student"
                    >
                        Student
                    </button>
                    <button
                        type="button"
                        id="btn-teacher"
                        class="flex-1 rounded-[8px] border-2 border-[#a03464] bg-[#a03464] text-white px-4 py-3 text-sm font-semibold transition-all hover:opacity-90"
                        data-account-type="teacher"
                    >
                        Teacher
                    </button>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="first_name">First Name <span class="text-rose-500">*</span></label>
                    <input
                        id="first_name"
                        name="first_name"
                        type="text"
                        class="{{ $inputClass('first_name') }}"
                        placeholder="Juan"
                        value="{{ old('first_name') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('first_name') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('first_name', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="middle_name">Middle Name <span class="text-rose-500">*</span></label>
                    <input
                        id="middle_name"
                        name="middle_name"
                        type="text"
                        class="{{ $inputClass('middle_name') }}"
                        placeholder="Santos"
                        value="{{ old('middle_name') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('middle_name') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('middle_name', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="last_name">Last Name <span class="text-rose-500">*</span></label>
                    <input
                        id="last_name"
                        name="last_name"
                        type="text"
                        class="{{ $inputClass('last_name') }}"
                        placeholder="Dela Cruz"
                        value="{{ old('last_name') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('last_name') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('last_name', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="extension_name">Extension Name <span class="text-rose-500">*</span></label>
                    <input
                        id="extension_name"
                        name="extension_name"
                        type="text"
                        class="{{ $inputClass('extension_name') }}"
                        placeholder="Jr., III, etc."
                        value="{{ old('extension_name') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('extension_name') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('extension_name', 'Required field.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-4">
                <div class="lg:col-span-2" data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="lrn_register" id="lrn_label">Learner's Reference Number <span class="text-rose-500">*</span></label>
                    <input
                        id="lrn_register"
                        name="lrn"
                        type="text"
                        inputmode="numeric"
                        maxlength="12"
                        class="{{ $inputClass('lrn') }}"
                        placeholder="12-digit LRN"
                        value="{{ old('lrn') }}"
                        data-rule="lrn"
                        data-lrn-placeholder="12-digit LRN"
                        data-emp-placeholder="Employee Number"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('lrn') }}"
                        data-default="Enter the 12-digit LRN."
                        id="lrn_feedback"
                        data-lrn-default="Enter the 12-digit LRN."
                        data-emp-default="Enter your employee number."
                    >
                        {{ $feedback('lrn', 'Enter the 12-digit LRN.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="grade_level" id="grade_label">Grade <span class="text-rose-500">*</span></label>
                    <select
                        id="grade_level"
                        name="grade"
                        class="{{ $inputClass('grade') }}"
                        data-rule="grade"
                    >
                        <option value="" id="grade_placeholder">Select grade</option>
                        @for ($grade = 7; $grade <= 12; $grade++)
                            <option value="Grade {{ $grade }}" @selected(old('grade') === "Grade {$grade}")>Grade {{ $grade }}</option>
                        @endfor
                    </select>
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('grade') }}"
                        data-default="Select a grade level."
                        id="grade_feedback"
                        data-grade-default="Select a grade level."
                        data-advisory-default="Select an advisory class."
                    >
                        {{ $feedback('grade', 'Select a grade level.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="section">Section <span class="text-rose-500">*</span></label>
                    <input
                        id="section"
                        name="section"
                        type="text"
                        class="{{ $inputClass('section') }}"
                        placeholder="e.g., Rizal"
                        value="{{ old('section') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('section') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('section', 'Required field.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-1">
                <div class="lg:col-span-2" data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="adviser">Adviser Name <span class="text-rose-500">*</span></label>
                    <input
                        id="adviser"
                        name="adviser"
                        type="text"
                        class="{{ $inputClass('adviser') }}"
                        placeholder="Mr./Ms. Adviser"
                        value="{{ old('adviser') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('adviser') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('adviser', 'Required field.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="house_no">House No. <span class="text-rose-500">*</span></label>
                    <input
                        id="house_no"
                        name="house_no"
                        type="text"
                        class="{{ $inputClass('house_no') }}"
                        placeholder="123"
                        value="{{ old('house_no') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('house_no') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('house_no', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="street_name">Street Name <span class="text-rose-500">*</span></label>
                    <input
                        id="street_name"
                        name="street_name"
                        type="text"
                        class="{{ $inputClass('street_name') }}"
                        placeholder="Example Street"
                        value="{{ old('street_name') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('street_name') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('street_name', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="barangay">Barangay <span class="text-rose-500">*</span></label>
                    <input
                        id="barangay"
                        name="barangay"
                        type="text"
                        class="{{ $inputClass('barangay') }}"
                        placeholder="Barangay"
                        value="{{ old('barangay') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('barangay') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('barangay', 'Required field.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-4">
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="municipality">Municipality <span class="text-rose-500">*</span></label>
                    <input
                        id="municipality"
                        name="municipality"
                        type="text"
                        class="{{ $inputClass('municipality') }}"
                        placeholder="Municipality"
                        value="{{ old('municipality') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('municipality') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('municipality', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="province">Province <span class="text-rose-500">*</span></label>
                    <input
                        id="province"
                        name="province"
                        type="text"
                        class="{{ $inputClass('province') }}"
                        placeholder="Province"
                        value="{{ old('province') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('province') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('province', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="country">Country <span class="text-rose-500">*</span></label>
                    <input
                        id="country"
                        name="country"
                        type="text"
                        class="{{ $inputClass('country') }}"
                        placeholder="Philippines"
                        value="{{ old('country', 'Philippines') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('country') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('country', 'Required field.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="zipcode">Zip Code <span class="text-rose-500">*</span></label>
                    <input
                        id="zipcode"
                        name="zipcode"
                        type="text"
                        inputmode="numeric"
                        maxlength="4"
                        class="{{ $inputClass('zipcode') }}"
                        placeholder="3014"
                        value="{{ old('zipcode') }}"
                        data-rule="required"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('zipcode') }}"
                        data-default="Required field."
                    >
                        {{ $feedback('zipcode', 'Required field.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="email">Email Address <span class="text-rose-500">*</span></label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="{{ $inputClass('email') }}"
                        placeholder="name@example.com"
                        value="{{ old('email') }}"
                        data-rule="email"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('email') }}"
                        data-default="Provide an email address."
                    >
                        {{ $feedback('email', 'Provide an email address.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="mobile">Mobile Number</label>
                    <input
                        id="mobile"
                        name="mobile"
                        type="text"
                        class="{{ $inputClass('mobile') }}"
                        placeholder="+639XXXXXXXXX or 09XXXXXXXXX"
                        value="{{ old('mobile') }}"
                        data-rule="mobile"
                    />
                    <p
                        class="validation-label"
                        data-feedback
                        data-state="{{ $state('mobile') }}"
                        data-default="Use +639********* or 09********* format."
                    >
                        {{ $feedback('mobile', 'Use +639********* or 09********* format.') }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="password_register">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input
                            id="password_register"
                            name="password"
                            type="password"
                            class="{{ $fieldClass }} pr-24"
                            placeholder="••••••••"
                            data-rule="password"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/3 inline-flex items-center rounded-full border border-[#f3cbe0] bg-white/80 px-2 py-1 text-[10px] font-semibold tracking-wide text-[#a03464] hover:text-[#7b1f46] focus:outline-none"
                            data-toggle-password
                            data-target="#password_register"
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
                        data-state="{{ $state('password') }}"
                        data-default="Minimum 8 characters with letters & numbers."
                    >
                        {{ $feedback('password', 'Minimum 8 characters with letters & numbers.') }}
                    </p>
                </div>
                <div data-field>
                    <label class="text-sm font-medium text-[#4b2036]" for="password_confirmation">Confirm Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="{{ $fieldClass }} pr-24"
                            placeholder="Re-type password"
                            data-rule="confirm"
                            data-match="#password_register"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/3 inline-flex items-center rounded-full border border-[#f3cbe0] bg-white/80 px-2 py-1 text-[10px] font-semibold tracking-wide text-[#a03464] hover:text-[#7b1f46] focus:outline-none"
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
                        data-state="{{ $state('password_confirmation') }}"
                        data-default="Re-type your password."
                    >
                        {{ $feedback('password_confirmation', 'Re-type your password.') }}
                    </p>
                </div>
            </div>

            <div class="rounded-[15px] border border-dashed border-[#f3cbe0] bg-white/70 p-5 sm:p-6 space-y-4" data-acceptance-group>
                <p class="text-sm text-[#6d4258]">Before continuing, please agree to the following:</p>
                <label class="flex items-start gap-3 text-sm text-[#4b2036]">
                    <input type="checkbox" class="mt-1 rounded border-[#d69bbc] text-[#a03464]" />
                    <span>
                        I have read and agree to the
                        <a href="#" class="text-[#a03464] font-medium">Privacy Policy</a>.
                    </span>
                </label>
                <label class="flex items-start gap-3 text-sm text-[#4b2036]">
                    <input type="checkbox" class="mt-1 rounded border-[#d69bbc] text-[#a03464]" />
                    <span>
                        I accept the
                        <a href="#" class="text-[#a03464] font-medium">Terms & Conditions</a>.
                    </span>
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button
                    type="submit"
                    class="self-center w-full sm:w-auto rounded-[8px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-8 py-3 text-xs sm:text-sm text-white font-semibold tracking-wide uppercase shadow-md shadow-[#e07aac]/30 transition"
                    data-acceptance-submit
                >
                    Create
                </button>
                <p class="text-center text-sm text-[#73445b]">
                    Already registered?
                    <a href="{{ route('login') }}" class="text-[#a03464] font-semibold hover:text-[#7b1f46]">Login</a>
                </p>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeInput = document.getElementById('user_type');
            const btnStudent = document.getElementById('btn-student');
            const btnTeacher = document.getElementById('btn-teacher');
            const lrnLabel = document.getElementById('lrn_label');
            const gradeLabel = document.getElementById('grade_label');
            const lrnInput = document.getElementById('lrn_register');
            const gradeSelect = document.getElementById('grade_level');
            const gradePlaceholder = document.getElementById('grade_placeholder');
            const lrnFeedback = document.getElementById('lrn_feedback');
            const gradeFeedback = document.getElementById('grade_feedback');

            // Initial state - Student is selected by default
            updateAccountType('student');

            function updateAccountType(type) {
                userTypeInput.value = type;

                if (type === 'teacher') {
                    // Update button styles
                    btnTeacher.classList.add('border-[#a03464]', 'bg-[#a03464]', 'text-white');
                    btnTeacher.classList.remove('border-[#f3cbe0]', 'bg-white/80', 'text-[#4b2036]');
                    btnStudent.classList.remove('border-[#a03464]', 'bg-[#a03464]', 'text-white');
                    btnStudent.classList.add('border-[#f3cbe0]', 'bg-white/80', 'text-[#4b2036]');

                    // Update labels
                    lrnLabel.innerHTML = 'Employee Number <span class="text-rose-500">*</span>';
                    gradeLabel.innerHTML = 'Advisory Class <span class="text-rose-500">*</span>';

                    // Update placeholder
                    lrnInput.placeholder = lrnInput.dataset.empPlaceholder;
                    gradePlaceholder.textContent = 'Select advisory class';

                    // Update feedback messages
                    lrnFeedback.dataset.default = lrnFeedback.dataset.empDefault;
                    gradeFeedback.dataset.default = gradeFeedback.dataset.advisoryDefault;

                    if (lrnFeedback.dataset.state === 'idle') {
                        lrnFeedback.textContent = lrnFeedback.dataset.empDefault;
                    }
                    if (gradeFeedback.dataset.state === 'idle') {
                        gradeFeedback.textContent = gradeFeedback.dataset.advisoryDefault;
                    }
                } else {
                    // Update button styles
                    btnStudent.classList.add('border-[#a03464]', 'bg-[#a03464]', 'text-white');
                    btnStudent.classList.remove('border-[#f3cbe0]', 'bg-white/80', 'text-[#4b2036]');
                    btnTeacher.classList.remove('border-[#a03464]', 'bg-[#a03464]', 'text-white');
                    btnTeacher.classList.add('border-[#f3cbe0]', 'bg-white/80', 'text-[#4b2036]');

                    // Update labels
                    lrnLabel.innerHTML = 'Learner\'s Reference Number <span class="text-rose-500">*</span>';
                    gradeLabel.innerHTML = 'Grade <span class="text-rose-500">*</span>';

                    // Update placeholder
                    lrnInput.placeholder = lrnInput.dataset.lrnPlaceholder;
                    gradePlaceholder.textContent = 'Select grade';

                    // Update feedback messages
                    lrnFeedback.dataset.default = lrnFeedback.dataset.lrnDefault;
                    gradeFeedback.dataset.default = gradeFeedback.dataset.gradeDefault;

                    if (lrnFeedback.dataset.state === 'idle') {
                        lrnFeedback.textContent = lrnFeedback.dataset.lrnDefault;
                    }
                    if (gradeFeedback.dataset.state === 'idle') {
                        gradeFeedback.textContent = gradeFeedback.dataset.gradeDefault;
                    }
                }
            }

            btnStudent.addEventListener('click', function() {
                updateAccountType('student');
            });

            btnTeacher.addEventListener('click', function() {
                updateAccountType('teacher');
            });
        });
    </script>
    @endpush
@endsection
