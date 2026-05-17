<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the persons table for tracking account ownership.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('color', 7)->default('#6366F1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
