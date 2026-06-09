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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('name');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('minimum_payment', 15, 2);
            $table->integer('due_date_day')->nullable(); // e.g. 15th of every month
            $table->string('status')->default('active'); // active, paid_off
            $table->timestamps();
            $table->softDeletes();

            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
