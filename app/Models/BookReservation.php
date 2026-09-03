<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'reserve_date',
        'due_date',
        'claim_deadline',
        'status',
        'claimed_at',
    ];


    protected $casts = [
        'reserve_date' => 'date',
        'due_date' => 'date',
        'claim_deadline' => 'date',
        'claimed_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the reservation is expired (past claim deadline)
     */
    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->claim_deadline < now()->startOfDay();
    }

    /**
     * Check if the reservation can be claimed
     */
    public function canBeClaimed(): bool
    {
        return $this->status === 'pending' && $this->claim_deadline >= now()->startOfDay();
    }

    /**
     * Scope to get pending reservations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get claimed reservations
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope to get voided reservations
     */
    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    /**
     * Scope to get expired reservations (past claim deadline)
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('claim_deadline', '<', now()->startOfDay());
    }

    /**
     * Scope to get active reservations (pending and not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('claim_deadline', '>=', now()->startOfDay());
    }
}
