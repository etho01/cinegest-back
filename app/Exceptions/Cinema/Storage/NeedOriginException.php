<?php
namespace App\Exceptions\Cinema\Storage;
use App\Exceptions\CustomException;

class NeedOriginException extends CustomException
{
    public function __construct() {
        parent::__construct('L\'origine est requise lorsque le stockage est une salle.', 400, [], 'NEED_ORIGIN');
    }
}