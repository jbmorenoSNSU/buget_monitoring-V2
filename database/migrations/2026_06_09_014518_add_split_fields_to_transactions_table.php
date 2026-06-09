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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('split_with_person_id')->nullable()->after('description');
            $table->decimal('split_amount', 15, 2)->nullable()->after('split_with_person_id');

            $table->index('split_with_person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['split_with_person_id']);
            $table->dropColumn(['split_with_person_id', 'split_amount']);
        });
    }
};
