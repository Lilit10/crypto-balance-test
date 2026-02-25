<?php

namespace App\DTO;

use App\Http\Requests\CreditBalanceRequest;
use App\Http\Requests\DebitBalanceRequest;
use Spatie\LaravelData\Data;

class BalanceOperationDto extends Data
{
    public int $userId;

    public string $currency;

    public string $amount;

    public ?string $idempotencyKey;

    public ?string $reference;

    public static function fromRequest(CreditBalanceRequest|DebitBalanceRequest $request): self
    {
        $v = $request->validated();

        return self::from([
            'userId' => (int) $v['user_id'],
            'currency' => $v['currency'],
            'amount' => $v['amount'],
            'idempotencyKey' => $v['idempotency_key'] ?? null,
            'reference' => $v['reference'] ?? null,
        ]);
    }
}
