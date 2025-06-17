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
        Schema::create('payment_gateway_transactions', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship fields
            $table->string('transactionable_type');
            $table->unsignedBigInteger('transactionable_id');
            
            // Gateway information
            $table->string('gateway_provider'); // 'paystack', 'flutterwave', etc.
            $table->string('transaction_reference')->unique();
            $table->string('gateway_transaction_id')->nullable();
            
            // Financial details
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->decimal('gateway_fee', 15, 2)->default(0);
            
            // Transaction status and data
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->json('gateway_response')->nullable();
            $table->json('callback_data')->nullable();
            
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['transactionable_type', 'transactionable_id'], 'gateway_transactions_morphs');
            $table->index('transaction_reference');
            $table->index('gateway_transaction_id');
            $table->index('gateway_provider');
            $table->index('status');
            $table->index('created_at');
            $table->index('verified_at');
            $table->index('deleted_at');
            
            // Composite indexes for common queries
            $table->index(['gateway_provider', 'status']);
            // $table->index(['transactionable_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_transactions');
    }
};
