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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans');
            $table->decimal('amount', 15, 2); // Total amount paid
            $table->decimal('principal_paid', 15, 2); // Amount applied to principal
            $table->decimal('interest_paid', 15, 2); // Amount applied to interest
            $table->timestamp('repayment_date');
            $table->string('source'); // Source of the repayment
            $table->foreignId('coop_account_receipt_id')->constrained('cooperative_accounts', 'id');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('repayment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
