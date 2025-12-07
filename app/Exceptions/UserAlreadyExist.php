<?php

namespace App\Exceptions;
use App\Exceptions\CustomException;

class UserAlreadyExist extends CustomException
{
    public function __construct()
    {
        $message = "L'utilisateur existe déjà.";
        parent::__construct($message, 400);
    }
}