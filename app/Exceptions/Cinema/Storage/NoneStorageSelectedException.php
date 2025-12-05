<?php
namespace App\Exceptions\Cinema\Storage;
use App\Exceptions\CustomException;

class NoneStorageSelectedException extends CustomException
{
    public function __construct() {
        parent::__construct('Vous n\'avez sélectionné aucun stockage.', 400, [], 'NO_STORAGE_SELECTED');
    }
}