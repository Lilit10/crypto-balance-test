<?php

namespace App\Services;

use App\DTO\BalanceOperationDto;
use App\Exceptions\BalanceNotFoundException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidAmountException;
use App\Models\BalanceOperation;
use App\Repositories\BalanceOperationRepositoryInterface;
use App\Repositories\CryptoBalanceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CryptoBalanceService
{
    private const SCALE = 8;

    public function __construct(
        private CryptoBalanceRepositoryInterface    $cryptoBalanceRepository,
        private BalanceOperationRepositoryInterface $balanceOperationRepository
    ) {}

    public function credit(BalanceOperationDto $dto): BalanceOperation
    {
        return $this->creditRaw(
            $dto->userId,
            $dto->currency,
            $dto->amount,
            $dto->idempotencyKey,
            $dto->reference
        );
    }

    public function creditRaw(
        int $userId,
        string $currency,
        string $amount,
        ?string $idempotencyKey = null,
        ?string $reference = null
    ): BalanceOperation {
        $amountInt = $this->parseAmountToInt($amount);
        $this->assertPositiveAmount($amountInt);

        $currency = strtoupper(trim($currency));

        return DB::transaction(function () use ($userId, $currency, $amountInt, $idempotencyKey, $reference) {
            $existing = $this->balanceOperationRepository->findByIdempotencyKey($idempotencyKey);

            if ($existing !== null) {
                return $existing;
            }

            $balance = $this->cryptoBalanceRepository->getOrCreateLocked($userId, $currency);
            $this->cryptoBalanceRepository->incrementBalance($balance, $amountInt);

            return $this->balanceOperationRepository->create([
                'user_id' => $userId,
                'currency' => $currency,
                'type' => BalanceOperation::TYPE_CREDIT,
                'amount' => $amountInt,
                'status' => BalanceOperation::STATUS_CONFIRMED,
                'idempotency_key' => $idempotencyKey,
                'reference' => $reference,
                'risk_flags' => null,
            ]);
        });
    }

    public function debit(BalanceOperationDto $dto): BalanceOperation
    {
        return $this->debitRaw(
            $dto->userId,
            $dto->currency,
            $dto->amount,
            $dto->idempotencyKey,
            $dto->reference
        );
    }

    public function debitRaw(
        int $userId,
        string $currency,
        string $amount,
        ?string $idempotencyKey = null,
        ?string $reference = null
    ): BalanceOperation {
        $amountInt = $this->parseAmountToInt($amount);
        $this->assertPositiveAmount($amountInt);

        $currency = strtoupper(trim($currency));

        return DB::transaction(function () use ($userId, $currency, $amountInt, $idempotencyKey, $reference) {
            $existing = $this->balanceOperationRepository->findByIdempotencyKey($idempotencyKey);

            if ($existing !== null) {
                return $existing;
            }

            $balance = $this->cryptoBalanceRepository->getForUserAndCurrency($userId, $currency, true);
            $availableInt = $balance !== null ? (int) $balance->balance : 0;

            if ($availableInt < $amountInt) {
                $required = $this->formatIntToAmount($amountInt);
                $available = $this->formatIntToAmount($availableInt);
                $message = "Insufficient {$currency} balance: required {$required}, available {$available}.";

                $this->balanceOperationRepository->create([
                    'user_id' => $userId,
                    'currency' => $currency,
                    'type' => BalanceOperation::TYPE_DEBIT,
                    'amount' => $amountInt,
                    'status' => BalanceOperation::STATUS_FAILED,
                    'idempotency_key' => $idempotencyKey,
                    'reference' => $reference,
                    'risk_flags' => ['reason' => $message],
                ]);

                throw new InsufficientBalanceException($currency, $required, $available);
            }

            $this->cryptoBalanceRepository->decrementBalance($balance, $amountInt);

            return $this->balanceOperationRepository->create([
                'user_id' => $userId,
                'currency' => $currency,
                'type' => BalanceOperation::TYPE_DEBIT,
                'amount' => $amountInt,
                'status' => BalanceOperation::STATUS_CONFIRMED,
                'idempotency_key' => $idempotencyKey,
                'reference' => $reference,
                'risk_flags' => null,
            ]);
        });
    }

    public function getBalance(int $userId, string $currency): string
    {
        $currency = strtoupper(trim($currency));

        $balance = $this->cryptoBalanceRepository->getForUserAndCurrency($userId, $currency, false);

        if ($balance === null) {
            throw new BalanceNotFoundException($userId, $currency);
        }

        return $this->formatIntToAmount((int) $balance->balance);
    }

    private function parseAmountToInt(string $amount): int
    {
        $amount = trim($amount);

        if (!preg_match('/^\d+(\.\d+)?$/', $amount)) {
            throw new InvalidAmountException('Invalid amount format.');
        }

        [$intPart, $fracPart] = array_pad(explode('.', $amount, 2), 2, '');

        if (strlen($fracPart) > self::SCALE) {
            throw new InvalidAmountException('Too many decimal places.');
        }

        $fracPart = str_pad($fracPart, self::SCALE, '0', STR_PAD_RIGHT);

        $normalized = ltrim($intPart . $fracPart, '0');
        $normalized = $normalized === '' ? '0' : $normalized;

        if (strlen($normalized) > 18) {
            throw new InvalidAmountException('Amount is too large.');
        }

        return (int) $normalized;
    }

    private function formatIntToAmount(int $value): string
    {
        if ($value === 0) {
            return '0';
        }

        $str = (string) $value;
        $len = strlen($str);

        if ($len <= self::SCALE) {
            $dec = rtrim(str_pad($str, self::SCALE, '0', STR_PAD_LEFT), '0');

            return '0.' . ($dec === '' ? '0' : $dec);
        }

        $intPart = substr($str, 0, $len - self::SCALE);
        $fracPart = rtrim(substr($str, -self::SCALE), '0');

        return $fracPart !== '' ? "{$intPart}.{$fracPart}" : $intPart;
    }

    private function assertPositiveAmount(int $amountInt): void
    {
        if ($amountInt <= 0) {
            throw new InvalidAmountException('Amount must be positive.');
        }
    }
}
