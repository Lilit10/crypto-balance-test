<?php

namespace App\Repositories;

use App\Models\BalanceOperation;

class BalanceOperationRepository implements BalanceOperationRepositoryInterface
{
    public function findByIdempotencyKey(?string $idempotencyKey): ?BalanceOperation
    {
        if ($idempotencyKey === null || $idempotencyKey === '') {
            return null;
        }

        return BalanceOperation::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    public function create(array $data): BalanceOperation
    {
        return BalanceOperation::query()->create($data);
    }
}
