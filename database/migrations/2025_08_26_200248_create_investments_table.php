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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('investor_id')->constrained('investor_profiles')->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');

            $table->integer('shares');
            $table->decimal('amount', 15, 2);
            $table->enum('investment_type', ['myself', 'authorize']);
            $table->string('status')->default('active'); // active, completed, cancelled, pending
            $table->dateTime('investment_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
