<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            $table->dropUnique('budget_goals_category_id_month_year_unique');
            $table->unique(['category_id', 'person_id', 'month', 'year'], 'budget_goals_unique_category_person_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            $table->dropUnique('budget_goals_unique_category_person_date');
            $table->unique(['category_id', 'month', 'year']);
        });
    }
};
