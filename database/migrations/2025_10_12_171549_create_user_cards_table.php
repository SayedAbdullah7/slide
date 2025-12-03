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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_token')->unique(); // Paymob card token
            $table->string('masked_pan'); // e.g., "xxxx-xxxx-xxxx-0008"
            $table->string('card_brand')->nullable(); // e.g., "Visa", "MasterCard"
            $table->integer('paymob_token_id')->nullable(); // Paymob's token ID
            $table->string('paymob_order_id')->nullable(); // Order ID when card was saved
            $table->integer('paymob_merchant_id')->nullable(); // Merchant ID
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'is_active']);

            // Prevent duplicate cards for same user (same token)
            $table->unique(['user_id', 'card_token']);

            // Prevent duplicate masked_pan for same user (extra safety)
            $table->unique(['user_id', 'masked_pan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
