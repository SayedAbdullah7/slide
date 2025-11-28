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
        Schema::create('bank_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('investor_id')->nullable()->constrained('investor_profiles')->onDelete('set null');
            $table->string('profile_type')->default('investor'); // investor or owner
            $table->unsignedBigInteger('profile_id'); // investor_profile_id or owner_profile_id

            // Bank transfer details - filled by investor
            $table->string('receipt_file')->nullable(); // Image or PDF uploaded by investor
            $table->string('receipt_file_name')->nullable(); // Original filename

            // Admin action fields
            $table->foreignId('bank_id')->nullable()->constrained('banks')->onDelete('restrict'); // Bank used for transfer
            $table->string('transfer_reference')->nullable()->unique(); // Unique transfer reference number
            $table->decimal('amount', 15, 2)->nullable(); // Amount transferred (filled by admin)
            $table->text('admin_notes')->nullable(); // Admin internal notes

            // Status tracking
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('rejection_reason')->nullable();
            $table->foreignId('action_by')->nullable()->constrained('admins')->onDelete('set null'); // Admin who took action
            $table->timestamp('processed_at')->nullable(); // When processed/approved/rejected

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['investor_id', 'status']);
            $table->index(['profile_type', 'profile_id']);
            $table->index('status');
            $table->index('created_at');
            $table->index('transfer_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfer_requests');
    }
};
