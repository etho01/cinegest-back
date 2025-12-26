<?php

namespace App\Models;

use App\Models\Cinema\Settings\Option;
use App\Models\Entity\Cinema;
use App\Models\Movie\MovieVersion;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = [
        'movieVersionId',
        'movieId',
        'roomId',
        'cinemaId',
        'startTime',
        'endTime',
        'status',
    ];

    public function movieVersion()
    {
        return $this->belongsTo(MovieVersion::class, 'movieVersionId');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }
    
    public function cinema()
    {
        return $this->belongsTo(Cinema::class, 'cinemaId');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movieId');
    }
}
