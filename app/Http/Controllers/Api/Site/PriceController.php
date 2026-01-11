<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\UseCase\Site\Price\GetPrices;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    private GetPrices $getPrices;

    public function __construct(GetPrices $getPrices)
    {
        $this->getPrices = $getPrices;
    }

    public function index(Request $request)
    {
        $cinemaApi = $request->get('cinemaApi');
        
        $prices = $this->getPrices->handle($cinemaApi);

        return response()->json($prices);
    }
}
