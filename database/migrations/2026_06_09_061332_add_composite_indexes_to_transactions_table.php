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
            $table->index(['transaction_date', 'account_id', 'type'], 'idx_trans_date_acc_type');
            $table->index(['transaction_date', 'transfer_to_account_id', 'type'], 'idx_trans_date_transfer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_trans_date_acc_type');
            $table->dropIndex('idx_trans_date_transfer_type');
        });
    }
};
