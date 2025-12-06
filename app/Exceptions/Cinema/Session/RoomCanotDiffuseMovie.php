<?php 

namespace App\Exceptions\Cinema\Session;

use App\Exceptions\CustomException;
use App\Models\Movie;
use App\Models\Movie\MovieVersion;
use App\Models\Room;

class RoomCanotDiffuseMovie extends CustomException
{
    public function __construct(Room $room, MovieVersion $movieVersion)
    {
        $message = "La salle '{$room->name}' ne peut pas diffuser la version '{$movieVersion->versionName}' du film '{$movieVersion->movie->title}' en raison d'options manquantes.";
        parent::__construct($message, 400);
    }
}