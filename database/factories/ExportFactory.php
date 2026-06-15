<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Export;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Export>
 */
class ExportFactory extends Factory
{
    protected $model = Export::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'type' => fake()->randomElement(['income-expense', 'category-expense', 'budget-goal']),
            'format' => fake()->randomElement(['xlsx', 'pdf']),
            'file_name' => null,
            'file_path' => null,
            'status' => 'pending',
            'error' => null,
        ];
    }

    /**
     * Set as completed with a file.
     */
    public function completed(): static
    {
        $fileName = 'test-report.xlsx';

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'file_name' => $fileName,
            'file_path' => 'exports/'.$fileName,
        ]);
    }

    /**
     * Set as failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error' => 'Test error message',
        ]);
    }
}
