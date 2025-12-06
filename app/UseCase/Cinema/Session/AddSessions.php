<?php 

namespace App\UseCase\Cinema\Session;

use App\Exceptions\Cinema\Session\RoomCanotDiffuseMovie;
use App\Models\Movie\MovieVersion;
use App\Models\Room;

class AddSessions
{
    public static function handle(int $cinemaId, array $sessions)
    {
        foreach ($sessions as $sessionData) {
            $room = Room::find($sessionData['roomId']);
            $movieVersion = MovieVersion::find($sessionData['movieVersionId']);

            if (!$room || !$movieVersion) {
                continue; // Skip if room or movie version not found
            }

            if (!$room->canDifuseMovieVersion($movieVersion)) {
                throw new RoomCanotDiffuseMovie($room, $movieVersion);
            }

            \App\Models\Session::create([
                'movieVersionId' => $sessionData['movieVersionId'],
                'roomId' => $sessionData['roomId'],
                'movieId' => $movieVersion->movieId,
                'cinemaId' => $cinemaId,
                'startTime' =>  date('Y-m-d H:i:s', strtotime($sessionData['startAt'])),
                'endTime' => date('Y-m-d H:i:s', strtotime($sessionData['startAt'])), // You might want to calculate this based on movie duration
            ]);
        }
        return []; // Return the created sessions
    }
}