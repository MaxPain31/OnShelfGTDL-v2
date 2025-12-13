<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeacherRulesController extends BaseTeacherController
{
    public function index(): View|RedirectResponse
    {
        $this->ensureTeacher();
        if ($redirect = $this->ensureProfileSetup()) {
            return $redirect;
        }

        $rules = Rule::active()->ordered()->get();

        return view('teacher.rules', [
            'rules' => $rules,
        ]);
    }
}

