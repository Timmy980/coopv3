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
        Schema::table('account_types', function (Blueprint $table) {
            $table->boolean('auto_create')->default(true)->after('interest_rate')
                ->comment('Indicates if accounts of this type should be auto-created for new members');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')
                ->after('auto_create')
                ->comment('Status of the account type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_types', function (Blueprint $table) {
            $table->dropColumn('auto_create');
            $table->dropColumn('status');
        });
    }
};
