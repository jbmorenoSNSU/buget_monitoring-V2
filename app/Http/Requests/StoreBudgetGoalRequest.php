<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetGoalRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $goal_id = $this->route('budget_goal')?->id ?? null;

        return [
            'category_id' => [
                'required', 'exists:categories,id',
                Rule::unique('budget_goals')->where(function ($query) {
                    $query->where('month', $this->input('month'))
                          ->where('year', $this->input('year'));
                })->ignore($goal_id),
            ],
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
            'limit_amount' => 'required|numeric|min:0.01|max:9999999999999.99',
        ];
    }
}
