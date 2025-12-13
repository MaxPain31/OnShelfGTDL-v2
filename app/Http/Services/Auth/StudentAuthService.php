<?php

namespace App\Http\Services\Auth;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentAuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Get role based on user_type
            $roleName = $data['user_type'] ?? 'student';
            // Capitalize first letter to match database format
            $roleNameCapitalized = ucfirst($roleName);
            $role = Role::where('name', $roleNameCapitalized)->firstOrFail();

            // Create user
            $user = User::create([
                'role_id' => $role->id,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'deactivated' => false,
            ]);

            // Create user info
            $userInfoData = [
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'extension_name' => $data['extension_name'] ?? null,
                'section' => $data['section'] ?? null,
                'adviser' => $data['adviser'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'zipcode' => $data['zipcode'] ?? null,
                'house_no' => $data['house_no'] ?? null,
                'street_name' => $data['street_name'] ?? null,
                'barangay' => $data['barangay'] ?? null,
                'municipality' => $data['municipality'] ?? null,
                'province' => $data['province'] ?? null,
                'country' => $data['country'] ?? 'Philippines',
            ];

            // Add role-specific fields
            if ($roleName === 'student') {
                $userInfoData['lrn'] = $data['lrn'] ?? null;
                $userInfoData['grade'] = $data['grade'] ?? null;
            } elseif ($roleName === 'teacher') {
                $userInfoData['employee_number'] = $data['lrn'] ?? null;
                $userInfoData['advisory_class'] = $data['grade'] ?? null;
            }

            UserInfo::create($userInfoData);

            Auth::login($user);

            return $user;
        });
    }

    public function login(array $credentials, bool $remember = false): bool
    {
        $identifier = $credentials['lrn'] ?? $credentials['employee_number'] ?? null;

        if (!$identifier) {
            return false;
        }

        // Find user by LRN or employee number
        $user = User::whereHas('userInfo', function ($query) use ($identifier) {
            $query->where('lrn', $identifier)
                  ->orWhere('employee_number', $identifier);
        })->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        Auth::login($user, $remember);

        return true;
    }
}

