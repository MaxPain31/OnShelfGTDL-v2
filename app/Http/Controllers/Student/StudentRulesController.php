<?php

namespace App\Http\Controllers\Student;

use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentRulesController extends BaseStudentController
{
    public function index(): View|RedirectResponse
    {
        $this->ensureStudent();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $rules = Rule::active()->ordered()->get();

        return view('student.rules', [
            'rules' => $rules,
        ]);
    }
}

