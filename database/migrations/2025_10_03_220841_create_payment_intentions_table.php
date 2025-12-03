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
        Schema::create('payment_intentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Core payment data
            $table->string('type')->index()->comment('investment or wallet_charge');
            $table->integer('amount_cents');
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['created', 'active', 'completed', 'failed', 'expired'])->default('created');
            $table->boolean('is_executed')->default(false)->index()->comment('Transaction executed flag');

            // Paymob integration
            $table->string('client_secret')->nullable();
            $table->string('paymob_intention_id')->nullable();
            $table->string('paymob_order_id')->nullable();
            $table->string('special_reference')->nullable();

            // Business data (JSON)
            $table->json('billing_data');
            $table->json('items')->nullable();
            $table->json('extras')->nullable()->comment('Business context: opportunity_id, shares, etc.');

            // Transaction data (from webhook)
            $table->string('transaction_id')->nullable()->unique();
            $table->string('merchant_order_id')->nullable();
            $table->string('payment_method')->nullable()->comment('From webhook: MasterCard, Visa, etc.');
            $table->json('paymob_response')->nullable()->comment('Full webhook response');

            // Timestamps
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->integer('refund_amount_cents')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('paymob_order_id');
            $table->index('special_reference');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intentions');
    }
};
