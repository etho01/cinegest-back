<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Models\CinemaApi\Price;
use App\Models\Entity\Cinema;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $cinemaSpecificPrices = [];

        $cinemaApi = $request->get('cinemaApi');

        $cinemaSpecificPrices = Cinema::with('options')->whereIn("id", $cinemaApi->cinemas->pluck("id"))->get();

        return response()->json([
            "generalPrices" => Price::where('cinema_api_id', $cinemaApi->id)->get(),
            "cinemaSpecificPrices" => $cinemaSpecificPrices,
        ]);
    }
}
