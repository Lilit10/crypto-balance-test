#!/bin/bash
# Базовый URL (запустите: php artisan serve)
BASE="http://127.0.0.1:8000/api"

# 1. Зачислить (credit)
curl -s -X POST "$BASE/balance/credit" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_id":1,"currency":"USDT","amount":"100.50","idempotency_key":"dep-1","reference":"tx1"}'

echo -e "\n---"

# 2. Получить баланс
curl -s -X GET "$BASE/balance/1/USDT" -H "Accept: application/json"

echo -e "\n---"

# 3. Списать (debit)
curl -s -X POST "$BASE/balance/debit" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"user_id":1,"currency":"USDT","amount":"50.25","idempotency_key":"wd-1","reference":"withdrawal_1"}'

echo -e "\n---"

# 4. Баланс после списания
curl -s -X GET "$BASE/balance/1/USDT" -H "Accept: application/json"

echo ""
