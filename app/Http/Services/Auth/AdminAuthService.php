<?php

namespace App\Http\Services\Auth;

use App\Http\Repositories\AdminRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public function __construct(private readonly AdminRepository $admins)
    {
    }

    public function attempt(string $email, string $password): ?User
    {
        $admin = $this->admins->findByEmail($email);

        if (! $admin || ! Hash::check($password, $admin->password)) {
            return null;
        }

        return $admin;
    }
}

