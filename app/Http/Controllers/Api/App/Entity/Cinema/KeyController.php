<?php

namespace App\Http\Controllers\Api\App\Entity\Cinema;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function index(Int $entityId, Int $cinemaId)
    {
        $query = Key::where('cinemaId', $cinemaId);
        return $query->paginate(30);
    }
}
