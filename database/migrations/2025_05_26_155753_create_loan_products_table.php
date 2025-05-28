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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id(); // Auto-incrementing integer
            $table->string('product_name');
            $table->decimal('interest_rate', 5, 2); // Percentage with 2 decimal places
            $table->integer('repayment_period_months');
            $table->decimal('application_fee_amount', 15, 2)->default(0);
            $table->decimal('application_fee_percentage', 5, 2)->default(0);
            $table->integer('required_guarantors')->default(0);
            $table->decimal('max_loan_amount_percentage_of_savings', 5, 2)->nullable();
            $table->decimal('max_loan_amount_fixed', 15, 2)->nullable();
            $table->text('eligibility_criteria')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
