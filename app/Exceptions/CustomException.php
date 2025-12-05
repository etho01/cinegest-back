<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected array $errors;
    protected string $type;

    public function __construct($message = "", $code = 400, array $errors = [], string $type = "API_ERROR")
    {
        parent::__construct($message, $code, null);
        $this->errors = $errors;
        $this->type = $type;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function type(): string
    {
        return $this->type;
    }
}
