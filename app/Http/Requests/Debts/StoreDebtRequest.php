<?php

declare(strict_types=1);

namespace App\Http\Requests\Debts;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'person_id' => ['nullable', 'exists:persons,id'],
            'name' => ['required', 'string', 'max:255'],
            'principal_amount' => ['required', 'numeric', 'min:0'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_payment' => ['required', 'numeric', 'min:0'],
            'due_date_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'status' => ['required', 'string', 'in:active,paid_off'],
        ];
    }
}
