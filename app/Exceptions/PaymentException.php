<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public function __construct(string $message, private int $httpStatus = 400)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->httpStatus;
    }
}