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
            $table->boolean('is_rollover_enabled')->default(true)->after('limit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            $table->dropColumn('is_rollover_enabled');
        });
    }
};
