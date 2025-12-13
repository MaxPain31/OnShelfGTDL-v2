<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn',
        'book_name',
        'category',
        'authors_name',
        'book_shelf',
        'copyright',
        'stock_quantity',
        'publication_name',
        'image_path',
        'description',
        'view_count',
        'favorite_count',
    ];

    public function views()
    {
        return $this->hasMany(BookView::class);
    }

    public function favorites()
    {
        return $this->hasMany(BookFavorite::class);
    }

    public function borrows()
    {
        return $this->hasMany(BookBorrow::class);
    }

    public function activeBorrows()
    {
        return $this->hasMany(BookBorrow::class)->whereIn('status', ['borrowed', 'overdue']);
    }

    public function reservations()
    {
        return $this->hasMany(BookReservation::class);
    }

    public function activeReservations()
    {
        return $this->hasMany(BookReservation::class)
            ->where('status', 'pending')
            ->where('claim_deadline', '>=', now()->startOfDay());
    }
}

