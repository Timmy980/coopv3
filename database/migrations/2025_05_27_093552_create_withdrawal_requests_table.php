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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('member_account_id')->constrained('member_accounts');
            $table->decimal('requested_amount', 15, 2);
            $table->timestamp('request_date');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'disbursed'
            ])->default('pending');
            $table->timestamp('approval_date')->nullable();
            $table->timestamp('disbursement_date')->nullable();
            $table->string('member_bank_account_for_receipt');
            $table->foreignId('coop_account_disbursement_id')->nullable()->constrained('cooperative_accounts');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['status', 'request_date']);
            $table->index('approval_date');
            $table->index('disbursement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
