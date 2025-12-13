<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'user_info';

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'extension_name',
        'lrn',
        'employee_number',
        'grade',
        'advisory_class',
        'section',
        'adviser',
        'mobile',
        'zipcode',
        'house_no',
        'street_name',
        'barangay',
        'municipality',
        'province',
        'country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . ($this->middle_name ?? '') . ' ' . $this->last_name);
        if ($this->extension_name) {
            $name .= ' ' . $this->extension_name;
        }
        return $name;
    }
}

