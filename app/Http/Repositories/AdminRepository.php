<?php

namespace App\Http\Repositories;

use App\Models\Role;
use App\Models\User;

class AdminRepository
{
    public function findByEmail(string $email): ?User
    {
        $roleIds = Role::whereIn('name', ['Administrator', 'Teacher'])->pluck('id');

        if ($roleIds->isEmpty()) {
            return null;
        }

        return User::where('email', $email)
            ->whereIn('role_id', $roleIds)
            ->first();
    }

    public function findById(int $id): ?User
    {
        $roleIds = Role::whereIn('name', ['Administrator', 'Teacher'])->pluck('id');

        if ($roleIds->isEmpty()) {
            return null;
        }

        return User::where('id', $id)
            ->whereIn('role_id', $roleIds)
            ->first();
    }
}

