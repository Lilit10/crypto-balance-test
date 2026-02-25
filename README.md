# Модуль учёта крипто-баланса пользователя (Laravel)

Простой модуль для **зачисления** и **списания** крипто-баланса пользователя с учётом рисков (гонки, двойное списание, отрицательный баланс).

## Стек

- PHP 8.2+, Laravel 12

## Установка

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Учёт рисков

| Риск | Решение |
|------|--------|
| **Гонка (race condition)** | Транзакция БД + `lockForUpdate()` по строке баланса при credit/debit |
| **Двойное списание/зачисление** | Идемпотентность: параметр `idempotency_key` — повторный запрос с тем же ключом возвращает существующую операцию |
| **Отрицательный баланс** | Проверка достаточности средств перед списанием; при нехватке — `InsufficientBalanceException` |
| **Точность сумм** | Поля `balance` и `amount` — `decimal(24,8)` |
| **Асинхронность блокчейна** | Зачисление вызывается извне (воркер, webhook) после подтверждения в сети; списание вывода можно вызывать при создании заявки (с последующей синхронизацией по статусу вывода) |

## API

Базовый URL: `/api` (например, `http://localhost:8000/api`).

### Зачислить средства

```http
POST /api/balance/credit
Content-Type: application/json

{
  "user_id": 1,
  "currency": "USDT",
  "amount": "100.50",
  "idempotency_key": "deposit-abc-123",
  "reference": "tx_hash_0x..."
}
```

- `idempotency_key` и `reference` — необязательные.

### Списать средства

```http
POST /api/balance/debit
Content-Type: application/json

{
  "user_id": 1,
  "currency": "USDT",
  "amount": "50.25",
  "idempotency_key": "withdrawal-xyz-456",
  "reference": "withdrawal_id_789"
}
```

При недостатке средств ответ `409` с сообщением об ошибке.

### Получить баланс

```http
GET /api/balance/{user_id}/{currency}
```

Пример: `GET /api/balance/1/USDT`.

## Использование сервиса в коде

```php
use App\Services\CryptoBalanceService;

$service = app(CryptoBalanceService::class);

// Зачисление
$operation = $service->credit($userId, 'USDT', '100.00', 'deposit-unique-key', 'tx_hash');

// Списание (вывод, платёж, комиссия)
$operation = $service->debit($userId, 'USDT', '50.00', 'withdraw-unique-key', 'withdrawal_123');

// Текущий баланс
$balance = $service->getBalance($userId, 'USDT');
```

## Структура

- **Миграции:** `crypto_balances` (баланс по пользователю и валюте), `balance_operations` (история операций + idempotency_key).
- **Модели:** `CryptoBalance`, `BalanceOperation`.
- **Сервис:** `App\Services\CryptoBalanceService` — методы `credit()`, `debit()`, `getBalance()`.
- **Исключения:** `InsufficientBalanceException`, `InvalidAmountException`.

## Публикация на GitHub

1. Создайте репозиторий на https://github.com (например, `crypto-balance-module`).
2. В корне проекта выполните:

```bash
git init
git add .
git commit -m "Crypto balance module: credit/debit with risk handling"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/crypto-balance-module.git
git push -u origin main
```

3. Убедитесь, что репозиторий **публичный** (Settings → General → Danger Zone → Change visibility → Public), чтобы доступ на просмотр был открыт.
