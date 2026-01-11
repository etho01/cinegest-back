<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function index(Request $request)
    {
        $cinemaApi = $request->get('cinemaApi');
        
        return response()->json($cinemaApi->cinemas);
    }
}
