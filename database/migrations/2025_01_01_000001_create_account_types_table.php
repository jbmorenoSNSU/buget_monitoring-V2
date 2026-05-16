<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the account_types table for categorizing financial accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('icon', 50)->default('wallet');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
