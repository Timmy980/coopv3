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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->timestamp('transaction_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'failed'])->default('pending');
            $table->enum('source', ['member_gateway', 'member_deposit', 'admin_single', 'admin_bulk'])
                ->default('member_gateway');// Source of the saving transaction
            $table->string('reference_number')->unique();
            // Foreign keys for user actions and relationships
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('bulk_batch_id')->nullable()->constrained('saving_bulk_batches')->onDelete('set null');
            $table->foreignId('member_account_id')->constrained('member_accounts');
            $table->foreignId('cooperative_account_id')->constrained('cooperative_accounts');
            // Payment gateway related fields
            $table->foreignId('payment_gateway_transaction_id')->nullable()->constrained('payment_gateway_transactions')->onDelete('set null');
            $table->string('payment_gateway_reference')->nullable();
            $table->string('payment_proof_path')->nullable(); // Path to payment proof file
            
            $table->text('notes')->nullable(); // Additional notes or comments
            $table->text('rejection_reason')->nullable(); // Reason for rejection if applicable
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status', 'transaction_date']); // For filtering and sorting
            $table->index('reference_number');
            $table->index('status');
            $table->index('source');
            $table->index('member_account_id');
            $table->index('cooperative_account_id');
            $table->index('transaction_date');
            $table->index('initiated_by_id');
            $table->index('approved_by_id');
            $table->index('bulk_batch_id');
            $table->index('payment_gateway_transaction_id');
            $table->index('payment_gateway_reference');
            $table->index(['status', 'transaction_date']);
            $table->index(['member_account_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
