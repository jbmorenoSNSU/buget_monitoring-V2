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
            if (!Schema::hasColumn('transactions', 'id')) return;

            $indexesToCreate = [];

            $existingIndexes = DB::select('SHOW INDEXES FROM transactions WHERE Key_name NOT IN ("PRIMARY")');
            $existingIndexNames = collect($existingIndexes)->pluck('Key_name')->toArray();

            if (!in_array('transactions_transaction_date_index', $existingIndexNames)) {
                $table->index('transaction_date');
            }
            if (!in_array('transactions_account_id_index', $existingIndexNames)) {
                $table->index('account_id');
            }
            if (!in_array('transactions_category_id_index', $existingIndexNames)) {
                $table->index('category_id');
            }
            if (!in_array('transactions_type_index', $existingIndexNames)) {
                $table->index('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $existingIndexes = DB::select('SHOW INDEXES FROM transactions WHERE Key_name NOT IN ("PRIMARY")');
            $existingIndexNames = collect($existingIndexes)->pluck('Key_name')->toArray();

            if (in_array('transactions_transaction_date_index', $existingIndexNames)) {
                $table->dropIndex(['transaction_date']);
            }
            if (in_array('transactions_account_id_index', $existingIndexNames)) {
                $table->dropIndex(['account_id']);
            }
            if (in_array('transactions_category_id_index', $existingIndexNames)) {
                $table->dropIndex(['category_id']);
            }
            if (in_array('transactions_type_index', $existingIndexNames)) {
                $table->dropIndex(['type']);
            }
        });
    }
};
