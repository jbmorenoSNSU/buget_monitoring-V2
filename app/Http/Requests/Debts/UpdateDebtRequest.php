<?php

namespace App\Http\Requests\Debts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDebtRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
