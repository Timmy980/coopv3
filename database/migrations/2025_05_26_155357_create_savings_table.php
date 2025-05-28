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
            $table->enum('status', ['pending', 'approved', 'failed'])->default('pending');
            $table->enum('source', ['member', 'admin']);
            $table->string('reference_number')->unique();
            $table->foreignId('member_account_id')->constrained('member_accounts');
            $table->foreignId('cooperative_account_id')->constrained('cooperative_accounts');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'transaction_date']); // For filtering and sorting
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
