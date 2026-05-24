<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date'        => 'required|date_format:Y-m-d',
            'coa_id'      => 'required|integer|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'debit'       => 'nullable|numeric|min:0',
            'credit'      => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'    => 'Tanggal transaksi wajib diisi.',
            'date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'coa_id.required'  => 'Akun (COA) wajib dipilih.',
            'coa_id.exists'    => 'Akun yang dipilih tidak valid.',
        ];
    }
}
