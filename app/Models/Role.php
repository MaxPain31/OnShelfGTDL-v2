<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is admin
     */
    public function isAdmin(): bool
    {
        return strtolower($this->name) === 'administrator';
    }

    /**
     * Check if role is teacher
     */
    public function isTeacher(): bool
    {
        return strtolower($this->name) === 'teacher';
    }

    /**
     * Check if role is student
     */
    public function isStudent(): bool
    {
        return strtolower($this->name) === 'student';
    }
}

