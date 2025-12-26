<?php

namespace App\Exceptions\Site;

use App\Exceptions\CustomException;

class CinemaNotAllowed extends CustomException
{
    public function __construct() {
        parent::__construct('Le cinema n\'est pas autorisé pour cette API Cinema.', 400, [], 'BAD_CINEMA_API_CINEMA');
    }
}