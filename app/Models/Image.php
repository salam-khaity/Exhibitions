<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'exhibition_id',
        'booth_id',
        'image_path',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class, 'exhibition_id');
    }
    public function booth()
    {
        return $this->belongsTo(Booth::class, 'booth_id');
    }
}
