<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'email',
        'password',
        'deactivated',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deactivated' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user info associated with the user.
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && strtolower($this->role->name) === 'administrator';
    }

    /**
     * Check if user is teacher
     */
    public function isTeacher(): bool
    {
        return $this->role && strtolower($this->role->name) === 'teacher';
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        // Check by role_id (1 = Student) or by role name (case-insensitive)
        return ($this->role_id === 1) || ($this->role && strtolower($this->role->name) === 'student');
    }

    /**
     * Get user's LRN (for students) or Employee Number (for teachers)
     */
    public function getIdentifierAttribute(): ?string
    {
        if (!$this->userInfo) {
            return null;
        }

        if ($this->isStudent()) {
            return $this->userInfo->lrn;
        }

        if ($this->isTeacher()) {
            return $this->userInfo->employee_number;
        }

        return null;
    }

    /**
     * Get the borrows for the user.
     */
    public function borrows()
    {
        return $this->hasMany(BookBorrow::class);
    }

    /**
     * Get active borrows (not returned) for the user.
     */
    public function activeBorrows()
    {
        return $this->hasMany(BookBorrow::class)->whereIn('status', ['borrowed', 'overdue']);
    }

    /**
     * Get overdue borrows for the user.
     */
    public function overdueBorrows()
    {
        return $this->hasMany(BookBorrow::class)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now()->startOfDay());
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations()
    {
        return $this->hasMany(BookReservation::class);
    }

    /**
     * Get active reservations (pending and not expired) for the user.
     */
    public function activeReservations()
    {
        return $this->hasMany(BookReservation::class)
            ->where('status', 'pending')
            ->where('claim_deadline', '>=', now()->startOfDay());
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->whereNull('read_at')->orderBy('created_at', 'desc');
    }
}
