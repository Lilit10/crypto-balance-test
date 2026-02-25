<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource['message'],
            'operation_id' => $this->resource['operation_id'],
            'balance' => $this->resource['balance'],
        ];
    }
}
