<?php

namespace App\Repositories;

use App\Models\CryptoBalance;
use Illuminate\Database\UniqueConstraintViolationException;

class CryptoBalanceRepository implements CryptoBalanceRepositoryInterface
{
    public function getForUserAndCurrency(int $userId, string $currency, bool $lock = false): ?CryptoBalance
    {
        $query = CryptoBalance::query()
            ->where('user_id', $userId)
            ->where('currency', $currency);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function create(int $userId, string $currency, int $balance = 0): CryptoBalance
    {
        return CryptoBalance::query()->create([
            'user_id' => $userId,
            'currency' => $currency,
            'balance' => $balance,
        ]);
    }

    public function getOrCreateLocked(int $userId, string $currency): CryptoBalance
    {
        $balance = $this->getForUserAndCurrency($userId, $currency, true);

        if ($balance !== null) {
            return $balance;
        }

        try {
            $balance = $this->create($userId, $currency, 0);

            return CryptoBalance::query()->where('id', $balance->id)->lockForUpdate()->firstOrFail();
        } catch (UniqueConstraintViolationException) {
            return $this->getForUserAndCurrency($userId, $currency, true) ?? throw new \RuntimeException('Balance row missing after duplicate create.');
        }
    }

    public function incrementBalance(CryptoBalance $balance, int $amount): void
    {
        $balance->increment('balance', $amount);
    }

    public function decrementBalance(CryptoBalance $balance, int $amount): void
    {
        $balance->decrement('balance', $amount);
    }
}
