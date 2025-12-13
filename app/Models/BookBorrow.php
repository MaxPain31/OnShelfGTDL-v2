<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookBorrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
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
     * Check if the borrow is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && $this->due_date < now()->startOfDay();
    }

    /**
     * Scope to get active borrows (not returned)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'borrowed');
    }

    /**
     * Scope to get overdue borrows
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
            ->where('due_date', '<', now()->startOfDay());
    }
}
