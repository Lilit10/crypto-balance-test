<?php

namespace App\Exceptions;

use Exception;

class BalanceNotFoundException extends Exception
{
    public function __construct(int $userId, string $currency)
    {
        parent::__construct("Balance not found for user {$userId} and currency {$currency}.");
    }
}
