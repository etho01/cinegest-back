<?php

namespace App\Models\Cinema;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $fillable = [
        'cinemaId',
        'roomId',
        'movieVersionId',
        'dateStart',
        'dateEnd',
    ];

    public function movieVersion()
    {
        return $this->belongsTo(\App\Models\Movie\MovieVersion::class, 'movieVersionId');
    }

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class, 'roomId');
    }
}
