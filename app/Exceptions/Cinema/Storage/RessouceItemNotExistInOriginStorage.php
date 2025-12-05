<?php
 
namespace App\Exceptions\Cinema\Storage;

use App\Exceptions\CustomException;
use App\Models\Movie\MovieVersion;

class RessouceItemNotExistInOriginStorage  extends CustomException
{
    public function __construct(MovieVersion $movieVersion) {
        parent::__construct('La version "' . $movieVersion->versionName . '" du film "' . $movieVersion->movie->title . '" n\'existe pas dans le stockage d\'origine.', 400, [], 'RESOURCE_ITEM_NOT_EXIST_IN_ORIGIN');
    }
}