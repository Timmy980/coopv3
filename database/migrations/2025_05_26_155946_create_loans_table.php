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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->timestamp('application_date');
            $table->decimal('requested_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->timestamp('disbursement_date')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'disbursed',
                'active',
                'fully_paid',
                'defaulted',
                'written_off'
            ])->default('pending');
            $table->decimal('total_interest_due', 15, 2)->default(0);
            $table->decimal('outstanding_principal', 15, 2)->default(0);
            $table->decimal('outstanding_interest', 15, 2)->default(0);
            $table->date('repayment_due_date')->nullable();
            $table->foreignId('coop_account_disbursement_id')->nullable()->constrained('cooperative_accounts', 'id');
            $table->string('member_bank_account_for_receipt')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['status', 'repayment_due_date']);
            $table->index('application_date');
            $table->index('disbursement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
