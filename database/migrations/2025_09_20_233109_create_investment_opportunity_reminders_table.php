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
        Schema::create('investment_opportunity_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_profile_id')->constrained('investor_profiles', 'id', 'io_reminders_investor_fk')->onDelete('cascade');
            $table->foreignId('investment_opportunity_id')->constrained('investment_opportunities', 'id', 'io_reminders_opportunity_fk')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            // Ensure one reminder per investor per opportunity
            $table->unique(['investor_profile_id', 'investment_opportunity_id'], 'io_reminders_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunity_reminders');
    }
};
