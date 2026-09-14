<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add unique constraint on account_types.name to prevent duplicate seeding.
 * Add index on transactions.debt_id for efficient debt-linked transaction queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('debt_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['debt_id']);
        });
    }
};
