<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_type',
        'phone',
        'city',
        'country',
        'commercial_register',
        'website',
        'bio',
    ];
}
