<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'account_type_id' => 'required|exists:account_types,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'initial_balance' => 'sometimes|numeric|min:-9999999999999.99|max:9999999999999.99',
            'color' => 'required|string|max:7',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
