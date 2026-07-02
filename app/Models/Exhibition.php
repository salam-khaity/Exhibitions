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
        'end_date'
    ];
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
