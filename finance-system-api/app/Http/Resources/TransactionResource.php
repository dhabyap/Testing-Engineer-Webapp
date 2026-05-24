<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date instanceof \Carbon\Carbon ? $this->date->format('Y-m-d') : $this->date,
            'coa_id' => $this->coa_id,
            'coa_code' => $this->chartOfAccount->code ?? null,
            'coa_name' => $this->chartOfAccount->name ?? null,
            'description' => $this->description,
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
