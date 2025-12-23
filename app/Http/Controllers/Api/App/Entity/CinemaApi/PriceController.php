<?php

namespace App\Http\Controllers\Api\App\Entity\CinemaApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function store(Request $request, Int $cinemaApi)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0',
        ]);

        $price = \App\Models\CinemaApi\Price::create(array_merge($data, [
            'cinema_api_id' => $cinemaApi,
        ]));

        return $price;
    }

    public function update(Request $request, Int $cinemaApi, Int $id)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0',
        ]);

        $price = \App\Models\CinemaApi\Price::findOrFail($id);
        $price->update($data);

        return $price;
    }

    public function delete(Int $cinemaApi, Int $id)
    {
        $price = \App\Models\CinemaApi\Price::findOrFail($id);
        $price->delete();
    }
}
