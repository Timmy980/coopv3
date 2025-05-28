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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->jsonb('withdrawal_rules'); // Using JSONB for PostgreSQL
            $table->decimal('interest_rate', 8, 2);
            $table->timestamps();
            $table->softDeletes(); // Adding soft deletes as the model uses SoftDeletes trait
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
