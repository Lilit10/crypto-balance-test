<?php

namespace App\Repositories;

use App\Models\CryptoBalance;

interface CryptoBalanceRepositoryInterface
{
    public function getForUserAndCurrency(int $userId, string $currency, bool $lock = false): ?CryptoBalance;

    public function create(int $userId, string $currency, int $balance = 0): CryptoBalance;

    public function getOrCreateLocked(int $userId, string $currency): CryptoBalance;

    public function incrementBalance(CryptoBalance $balance, int $amount): void;

    public function decrementBalance(CryptoBalance $balance, int $amount): void;
}
