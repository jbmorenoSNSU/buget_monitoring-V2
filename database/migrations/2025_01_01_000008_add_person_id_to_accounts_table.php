<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add person_id foreign key to accounts table for ownership tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->foreignId('person_id')->nullable()->after('account_type_id')
                ->constrained('persons')->nullOnDelete();
            $table->index('person_id');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropForeign(['person_id']);
            $table->dropIndex(['person_id']);
            $table->dropColumn('person_id');
        });
    }
};
