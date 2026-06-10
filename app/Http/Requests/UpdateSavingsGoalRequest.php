<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'account_id' => 'nullable|exists:accounts,id',
            'person_id' => 'nullable|exists:persons,id',
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01|max:9999999999.99',
            'current_amount' => 'required|numeric|min:0|max:9999999999.99',
            'target_date' => 'nullable|date',
        ];
    }
}
