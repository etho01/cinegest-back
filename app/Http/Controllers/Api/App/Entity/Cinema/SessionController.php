<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\App\Entity\Cinema\SessionResource;
use App\Models\Session;
use App\UseCase\Cinema\Session\AddSessions;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->input('rooms', []);
        $movie = $request->input('movie', []);
        $status = $request->input('status', []);
        $query = Session::with(['room', 'movieVersion', 'movie']);
        if (!empty($rooms)) {
            $query->whereIn('roomId', $rooms);
        }
        if (!empty($movie)) {
            $query->whereIn('movieId', $movie);
        }
        if (!empty($status)) {
            $query->whereIn('status', $status);
        }
        return SessionResource::collection($query->paginate(30));
    }

    public function addSessions(Request $request, int $entityId, int $cinemaId)
    {
        $data = $request->validate([
            'sessions' => 'required|array',
            'sessions.*.movieVersionId' => 'required|exists:movie_versions,id',
            'sessions.*.roomId' => 'required|exists:rooms,id',
            'sessions.*.startAt' => 'required|date',
        ]);

        AddSessions::handle($cinemaId, $data['sessions']);

        return [];
    }

    public function destroy(int $entityId, int $cinemaId, Session $session)
    {
        $session->delete();
        return [];
    }
}
