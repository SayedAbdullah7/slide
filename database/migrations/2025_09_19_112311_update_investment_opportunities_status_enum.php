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
        // First, change the column to string to allow new values
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->string('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to enum with original values
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->enum('status', ['open', 'completed', 'suspended'])->change();
        });
    }
};
