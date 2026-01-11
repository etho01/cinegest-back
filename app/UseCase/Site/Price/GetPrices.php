<?php

namespace App\UseCase\Site\Price;

use App\Repository\PriceRepository;
use App\Repository\CinemaRepository;
use App\Models\CinemaApi;

class GetPrices
{
    private PriceRepository $priceRepository;
    private CinemaRepository $cinemaRepository;

    public function __construct(
        PriceRepository $priceRepository,
        CinemaRepository $cinemaRepository
    ) {
        $this->priceRepository = $priceRepository;
        $this->cinemaRepository = $cinemaRepository;
    }

    /**
     * Get general and cinema-specific prices
     */
    public function handle(CinemaApi $cinemaApi): array
    {
        $generalPrices = $this->priceRepository->getByCinemaApiId($cinemaApi->id);
        
        $cinemaIds = $cinemaApi->cinemas->pluck('id')->toArray();
        $cinemaSpecificPrices = $this->cinemaRepository->getByIdsWithRelations($cinemaIds, ['options']);

        return [
            'generalPrices' => $generalPrices,
            'cinemaSpecificPrices' => $cinemaSpecificPrices,
        ];
    }
}
