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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            
            // Polymorphic relationship for debit account
            $table->string('debit_accountable_type');
            $table->string('debit_accountable_id');
            
            // Polymorphic relationship for credit account
            $table->string('credit_accountable_type');
            $table->string('credit_accountable_id');
            
            $table->decimal('amount', 15, 2);
            $table->timestamp('transaction_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for polymorphic relationships and common queries
            $table->index(['debit_accountable_type', 'debit_accountable_id']);
            $table->index(['credit_accountable_type', 'credit_accountable_id']);
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
