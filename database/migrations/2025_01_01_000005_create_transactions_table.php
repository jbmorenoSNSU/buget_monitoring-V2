<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the transactions table for recording all financial transactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->enum('type', ['income', 'expense', 'transfer'])->default('expense');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->foreignId('recurring_id')->nullable()->constrained('recurring_transactions')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('account_id');
            $table->index('category_id');
            $table->index('type');
            $table->index('transaction_date');
            $table->index('transfer_to_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
