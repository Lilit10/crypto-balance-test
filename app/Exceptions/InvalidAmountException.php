<?php

namespace App\Exceptions;

use Exception;

class InvalidAmountException extends Exception
{
    public function __construct(string $message = 'Amount must be positive.')
    {
        parent::__construct($message);
    }
}
