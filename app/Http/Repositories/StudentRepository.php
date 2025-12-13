<?php

namespace App\Http\Repositories;

use App\Models\User;
use App\Models\UserInfo;

class StudentRepository
{
    public function findByLrn(string $lrn): ?User
    {
        return User::whereHas('userInfo', function ($query) use ($lrn) {
            $query->where('lrn', $lrn);
        })->first();
    }

    public function findByEmployeeNumber(string $employeeNumber): ?User
    {
        return User::whereHas('userInfo', function ($query) use ($employeeNumber) {
            $query->where('employee_number', $employeeNumber);
        })->first();
    }
}

