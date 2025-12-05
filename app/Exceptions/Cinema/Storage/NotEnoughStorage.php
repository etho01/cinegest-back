<?php

namespace App\Exceptions\Cinema\Storage;
use App\Exceptions\CustomException;

class NotEnoughStorage extends CustomException 
{
     public function __construct(float $storageNeeded = 0, float $sizeStorageElement = 0) {
        parent::__construct("Espace de stockage insuffisant. Nécessaire : {$storageNeeded} Go, Disponible : {$sizeStorageElement} Go", 400, [], 'NOT_ENOUGH_STORAGE');
    }
}