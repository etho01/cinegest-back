<?php

namespace App\Exceptions\Site;

use App\Exceptions\CustomException;

class InsufficientCapacityException extends CustomException
{
    public function __construct(int $requested, int $available) {
        parent::__construct(
            "Il n'y a pas assez de places disponibles. Demandées: {$requested}, Disponibles: {$available}", 
            400, 
            [
                'requested' => $requested,
                'available' => $available,
            ], 
            'INSUFFICIENT_CAPACITY'
        );
    }
}
