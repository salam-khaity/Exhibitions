<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    protected $table = 'booths';

    protected $fillable = [
        'exhibition_id',
        'exhibitor_id',
        'booth_number',
        'size',
        'price',
        'status',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class, 'exhibition_id');
    }
    public function exhibitor()
    {
        return $this->belongsTo(User::class, 'exhibitor_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'booth_id');
    }

}
