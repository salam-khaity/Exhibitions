<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
     protected $fillable = [
        'user_id',
        'phone',
        'avatar',
        ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
