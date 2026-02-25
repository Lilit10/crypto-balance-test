<?php

namespace App\Repositories;

use App\Models\BalanceOperation;

interface BalanceOperationRepositoryInterface
{
    public function findByIdempotencyKey(?string $idempotencyKey): ?BalanceOperation;

    public function create(array $data): BalanceOperation;
}
