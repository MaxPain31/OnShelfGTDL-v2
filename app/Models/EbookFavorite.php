<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'user_id',
    ];

    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
