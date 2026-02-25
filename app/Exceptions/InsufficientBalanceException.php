<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(
        string $currency,
        string $required,
        string $available
    ) {
        parent::__construct(
            "Insufficient {$currency} balance: required {$required}, available {$available}."
        );
    }
}
