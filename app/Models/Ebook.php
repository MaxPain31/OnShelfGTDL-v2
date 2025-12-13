<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'authors',
        'description',
        'ebook_file_path',
        'ebook_image_path',
        'view_count',
        'favorite_count',
    ];

    public function views()
    {
        return $this->hasMany(EbookView::class);
    }

    public function favorites()
    {
        return $this->hasMany(EbookFavorite::class);
    }
}

