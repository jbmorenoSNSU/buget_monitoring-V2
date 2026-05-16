<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the budget_goals table for monthly spending limits per category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('limit_amount', 15, 2);
            $table->timestamps();

            $table->unique(['category_id', 'month', 'year']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_goals');
    }
};
