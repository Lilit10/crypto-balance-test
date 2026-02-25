<?php

namespace App\Http\Controllers;

use App\DTO\BalanceOperationDto;
use App\Http\Requests\CreditBalanceRequest;
use App\Http\Requests\DebitBalanceRequest;
use App\Http\Resources\BalanceResource;
use App\Http\Resources\OperationResultResource;
use App\Services\CryptoBalanceService;
use Illuminate\Http\JsonResponse;

class CryptoBalanceController extends Controller
{
    public function __construct(
        private CryptoBalanceService $balanceService
    ) {}

    public function credit(CreditBalanceRequest $request): JsonResponse
    {
        $dto = BalanceOperationDto::fromRequest($request);

        $operation = $this->balanceService->credit($dto);

        return (new OperationResultResource([
            'message' => 'Credited',
            'operation_id' => $operation->id,
            'balance' => $this->balanceService->getBalance($dto->userId, $dto->currency),
        ]))->response()->setStatusCode(201);
    }

    public function debit(DebitBalanceRequest $request): OperationResultResource
    {
        $dto = BalanceOperationDto::fromRequest($request);

        $operation = $this->balanceService->debit($dto);

        return new OperationResultResource([
            'message' => 'Debited',
            'operation_id' => $operation->id,
            'balance' => $this->balanceService->getBalance($dto->userId, $dto->currency),
        ]);
    }

    public function show(int $userId, string $currency): BalanceResource
    {
        $balance = $this->balanceService->getBalance($userId, $currency);

        return new BalanceResource([
            'user_id' => $userId,
            'currency' => $currency,
            'balance' => $balance,
        ]);
    }
}
