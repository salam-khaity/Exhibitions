<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exhibition extends Model
{
    protected $table = 'exhibitions';

    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'location',
        'start_date',
        'end_date',
        'status',
    ];
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function booths()
    {
        return $this->hasMany(Booth::class, 'exhibition_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'exhibition_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'exhibition_id');
    }


}
