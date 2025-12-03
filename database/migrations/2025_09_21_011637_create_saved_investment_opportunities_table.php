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
        Schema::create('saved_investment_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_profile_id')->constrained('investor_profiles')->onDelete('cascade');
            $table->foreignId('investment_opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');
            $table->timestamps();

            // Ensure one save per investor per opportunity
            $table->unique(['investor_profile_id', 'investment_opportunity_id'], 'saved_io_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_investment_opportunities');
    }
};
