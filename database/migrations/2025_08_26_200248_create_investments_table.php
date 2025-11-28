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
            // Primary key and foreign keys
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // user_id that in investor_profile
            $table->foreignId('investor_id')->constrained('investor_profiles')->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');

            // Investment basic info
            $table->integer('shares')->unsigned(); // عدد الأسهم
            $table->decimal('share_price', 15, 2); // سعر السهم الواحد
            $table->decimal('total_investment', 15, 2); // إجمالي الاستثمار (shares × share_price)
            $table->decimal('total_payment_required', 15, 2); // إجمالي المبلغ المطلوب (بما في ذلك رسوم الشحن)
            $table->enum('investment_type', ['myself', 'authorize']);
            $table->string('status')->default('active'); // active, completed, cancelled, pending
            $table->dateTime('investment_date')->nullable();

            // Merchandise tracking fields
            $table->enum('merchandise_status', ['pending', 'arrived'])->default('pending');
            $table->dateTime('expected_delivery_date')->nullable();
            $table->dateTime('expected_distribution_date')->nullable();
            $table->dateTime('merchandise_arrived_at')->nullable();

            // Shipping and service fee (per share)
            $table->decimal('shipping_fee_per_share', 15, 2)->nullable();

            // Expected profits (per share)
            $table->decimal('expected_profit_per_share', 15, 2)->nullable(); // الربح المتوقع لكل سهم
            $table->decimal('expected_net_profit_per_share', 15, 2)->nullable(); // صافي الربح المتوقع لكل سهم

            // Actual profits (per share)
            $table->decimal('actual_profit_per_share', 15, 2)->nullable(); // الربح الفعلي لكل سهم
            $table->decimal('actual_net_profit_per_share', 15, 2)->nullable(); // صافي الربح الفعلي لكل سهم
            $table->dateTime('actual_returns_recorded_at')->nullable();

            // Distribution fields
            $table->enum('distribution_status', ['pending', 'distributed'])->default('pending');
            $table->decimal('distributed_profit', 15, 2)->nullable(); // الربح الموزع
            $table->dateTime('distributed_at')->nullable();

            // Timestamps and soft deletes
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
