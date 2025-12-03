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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('investor_id')->nullable()->constrained('investor_profiles')->onDelete('set null');
            // Keep bank_id as reference to banks table without FK to avoid migration order issues in tests
            $table->unsignedBigInteger('bank_id');
            $table->string('account_holder_name'); // Account holder full name
            $table->string('iban'); // IBAN number (SA format)
            $table->string('account_number')->nullable(); // Last 4 digits for display
            $table->boolean('is_default')->default(false); // Default bank account
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('investor_id');
            $table->index('bank_id');
            $table->index(['user_id', 'investor_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
