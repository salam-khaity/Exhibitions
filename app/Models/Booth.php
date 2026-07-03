<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    protected $table = 'booths';

    protected $fillable = [
        'exhibition_id',
        'booth_number',
        'size',
        'price',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class, 'exhibition_id');
    }
}
