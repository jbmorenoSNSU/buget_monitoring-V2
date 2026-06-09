<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01|max:9999999999999.99',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',
            'transfer_to_account_id' => 'nullable|exists:accounts,id|different:account_id',
            'split_with_person_id' => 'nullable|exists:persons,id',
            'split_amount' => 'nullable|numeric|min:0.01',
        ];
    }
}
