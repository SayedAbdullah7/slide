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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('investor_id')->nullable()->constrained('investor_profiles')->onDelete('set null');
            $table->string('profile_type')->default('investor'); // investor or owner
            $table->unsignedBigInteger('profile_id'); // investor_profile_id or owner_profile_id
            $table->foreignId('bank_account_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2); // Withdrawal amount
            $table->decimal('available_balance', 15, 2)->default(0); // Balance at time of request
            $table->string('status')->default('pending'); // pending, processing, completed, rejected, cancelled
            $table->string('rejection_reason')->nullable();
            $table->string('admin_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('reference_number')->unique()->nullable(); // Unique reference for tracking
            $table->json('bank_details')->nullable(); // Store bank account details at time of request
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['investor_id', 'status']);
            $table->index(['profile_type', 'profile_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
