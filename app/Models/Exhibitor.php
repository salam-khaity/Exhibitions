<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibitor extends Model
{
    protected $fillable = [
        'user_id',
        'brand_name',
        'industry',
        'phone',
        'city',
        'country',
        'description',
        'logo',
    ];
}
