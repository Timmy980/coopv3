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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('transaction_date');
            $table->enum('transaction_type', [
                'saving',
                'withdrawal',
                'loan_disbursement',
                'loan_repayment',
                'interest_accrual',
                'fee',
                'journal_entry'
            ]);
            
            // Reference fields
            $table->enum('reference_type', [
                'saving',
                'loan',
                'withdrawal_request',
                'journal_entry'
            ]);
            $table->string('reference_id');
            
            // Account details
            $table->enum('account_type', ['member_account', 'coop_account']);
            $table->string('account_id');
            
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description');
            $table->string('reference_number')->unique();
            $table->foreignId('created_by')->constrained('users');
            
            // Reversal tracking
            $table->unsignedBigInteger('reversal_of_transaction_id')->nullable();
            $table->boolean('is_reversed')->default(false);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['transaction_type', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['account_type', 'account_id']);
            $table->index('reversal_of_transaction_id');
        });

        // Add the self-referencing foreign key constraint after table creation
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('reversal_of_transaction_id')
                  ->references('id')
                  ->on('transactions')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
