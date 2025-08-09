<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileStore extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'address',
        'city',
        'phone',
        'instagram',
        'facebook',
        'tiktok',
    ];
}
