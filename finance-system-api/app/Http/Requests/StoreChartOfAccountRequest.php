<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChartOfAccountRequest extends FormRequest
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
            'code' => 'required|string|max:255|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'coa_category_id' => 'required|exists:coa_categories,id',   
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The code field is required.',
            'code.string' => 'The code must be a string.',
            'code.max' => 'The code may not be greater than 255 characters.',
            'code.unique' => 'The code has already been taken.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'coa_category_id.required' => 'The coa category id field is required.',
            'coa_category_id.exists' => 'The selected coa category id is invalid.',
        ];
    }
}
