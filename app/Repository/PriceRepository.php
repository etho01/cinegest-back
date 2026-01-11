<?php

namespace App\Repository;

use App\Models\CinemaApi\Price;
use Illuminate\Database\Eloquent\Collection;

class PriceRepository
{
    /**
     * Find price by ID
     */
    public function find(int $id): ?Price
    {
        return Price::find($id);
    }

    /**
     * Get prices by cinema API ID
     */
    public function getByCinemaApiId(int $cinemaApiId): Collection
    {
        return Price::where('cinema_api_id', $cinemaApiId)->get();
    }

    /**
     * Create a new price
     */
    public function create(array $data): Price
    {
        return Price::create($data);
    }

    /**
     * Update a price
     */
    public function update(Price $price, array $data): bool
    {
        return $price->update($data);
    }

    /**
     * Delete a price
     */
    public function delete(Price $price): bool
    {
        return $price->delete();
    }
}
