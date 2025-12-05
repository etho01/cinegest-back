<?php
namespace App\Exceptions\Cinema\Storage;
use App\Exceptions\CustomException;
use App\Models\Movie\MovieVersion;

class StorageItemExist extends CustomException
{
    public function __construct(MovieVersion $movieVersion) {
        parent::__construct('La version "' . $movieVersion->versionName . '" du film "' . $movieVersion->movie->title . '" existe déjà dans le stockage.', 400, [], 'STORAGE_ITEM_EXIST');
    }
}