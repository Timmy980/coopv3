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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_user_id')->constrained('users', 'id');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users', 'id');
            $table->enum('message_type', ['announcement', 'individual', 'support_response']);
            $table->string('subject');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->foreignId('support_ticket_id')->nullable()->constrained('support_tickets');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['message_type', 'sent_at']);
            $table->index(['recipient_user_id', 'is_read']); // For unread messages queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
