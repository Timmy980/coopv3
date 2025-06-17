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
        Schema::create('saving_bulk_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_reference')->unique();
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('batch_reference');
            $table->index('status');
            $table->index('created_by_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_bulk_batches');
    }
};
