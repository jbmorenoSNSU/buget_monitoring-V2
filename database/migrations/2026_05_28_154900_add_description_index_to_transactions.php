<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'description')) return;

            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                $existingIndexes = DB::select("PRAGMA index_list('transactions')");
                $existingIndexNames = collect($existingIndexes)->pluck('name')->toArray();
            } else {
                $existingIndexes = DB::select('SHOW INDEXES FROM transactions WHERE Key_name NOT IN ("PRIMARY")');
                $existingIndexNames = collect($existingIndexes)->pluck('Key_name')->toArray();
            }

            if (!in_array('transactions_description_index', $existingIndexNames)) {
                if ($driver === 'sqlite') {
                    $table->index('description', 'transactions_description_index');
                } else {
                    $table->index([DB::raw('description(191)')], 'transactions_description_index');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'sqlite') {
                $existingIndexes = DB::select("PRAGMA index_list('transactions')");
                $existingIndexNames = collect($existingIndexes)->pluck('name')->toArray();
            } else {
                $existingIndexes = DB::select('SHOW INDEXES FROM transactions WHERE Key_name NOT IN ("PRIMARY")');
                $existingIndexNames = collect($existingIndexes)->pluck('Key_name')->toArray();
            }

            if (in_array('transactions_description_index', $existingIndexNames)) {
                $table->dropIndex('transactions_description_index');
            }
        });
    }
};
