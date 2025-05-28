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
        Schema::create('activities', function (Blueprint $table) {
            $table->id(); // Auto-incrementing integer
            $table->foreignId('user_id')->constrained('users');
            $table->string('action'); // e.g., 'create', 'update', 'delete', 'view'
            $table->text('description');
            $table->string('model_type'); // For polymorphic relationship
            $table->string('model_id'); // For polymorphic relationship
            $table->jsonb('old_data')->nullable(); // Using JSONB for PostgreSQL
            $table->jsonb('new_data')->nullable(); // Using JSONB for PostgreSQL
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_type', 'model_id']); // Index for polymorphic relationship
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
