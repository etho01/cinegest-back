<?php

namespace App\Models;

use App\Models\Cinema\Settings\Option;
use App\Models\Cinema\Settings\Storage;
use App\Models\Entity\Cinema;
use App\Models\Movie\MovieVersion;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'cinema_id',
        'serveurSize',
    ];

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }
    
    public function Options()
    {
        return $this->belongsToMany(Option::class, 'room_options');
    }

    public function Storages()
    {
        return $this->belongsToMany(Storage::class, 'room_storages');
    }

    public function canDifuseMovieVersion(MovieVersion $movieVersion): bool
    {
        $requiredOptions = $movieVersion->Options->pluck('id')->toArray();
        $roomOptions = $this->Options->pluck('id')->toArray();

        foreach ($requiredOptions as $optionId) {
            if (!in_array($optionId, $roomOptions)) {
                return false;
            }
        }

        return true;
    }
}
