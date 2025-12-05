<?php

namespace App\Models\Cinema;

use App\Models\Cinema\Settings\Storage;
use App\Models\Movie;
use App\Models\Movie\MovieVersion;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;

class StorageItem extends Model
{
    protected $fillable = [
        'roomId',
        'storageId',
        'originId',
        'movieVersionId',
        'movieId',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class, 'storageId');
    }

    public function origin()
    {
        return $this->belongsTo(Storage::class, 'originId');
    }

    public function movieVersion()
    {
        return $this->belongsTo(MovieVersion::class, 'movieVersionId');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movieId');
    }
}