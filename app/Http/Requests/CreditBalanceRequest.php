<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'currency' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'string'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
