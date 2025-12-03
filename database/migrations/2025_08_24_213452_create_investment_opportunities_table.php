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
        Schema::create('investment_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();

            $table->foreignId('category_id')->nullable()->constrained('investment_categories')->nullOnDelete();
            $table->foreignId('owner_profile_id')->nullable()->constrained('owner_profiles')->nullOnDelete();

            // Status as string to allow dynamic values
            $table->string('status')->default('open');

            $table->string('risk_level')->nullable(); // Optionally switch to enum if you want

            $table->decimal('target_amount', 15, 2);
            $table->decimal('share_price', 10, 2); // سعر السهم الواحد
            $table->integer('reserved_shares')->default(0);
            $table->integer('investment_duration')->nullable();

            // Expected profits per share
            $table->decimal('expected_profit', 15, 2)->nullable(); // الربح المتوقع لكل سهم
            $table->decimal('expected_net_profit', 15, 2)->nullable();  // صافي الربح المتوقع لكل سهم
            $table->decimal('actual_profit_per_share', 15, 2)->nullable(); // الربح الفعلي لكل سهم
            $table->decimal('actual_net_profit_per_share', 15, 2)->nullable(); // صافي الربح الفعلي لكل سهم
            $table->decimal('distributed_profit', 15, 2)->nullable(); // الربح الموزع
            $table->decimal('shipping_fee_per_share', 10, 2)->nullable(); // رسوم الشحن لكل سهم

            // Status tracking
            $table->boolean('all_merchandise_delivered')->default(false);
            $table->boolean('all_returns_distributed')->default(false);
            $table->dateTime('expected_delivery_date')->nullable();
            $table->dateTime('expected_distribution_date')->nullable();
            $table->integer('min_investment')->default(0);
            $table->integer('max_investment')->nullable();

            $table->string('fund_goal')->nullable(); // Optional: switch to enum
            $table->string('guarantee')->nullable(); // ضمان

            $table->boolean('show')->default(false);
            $table->dateTime('show_date')->nullable();
            $table->dateTime('offering_start_date')->nullable();
            $table->dateTime('offering_end_date')->nullable();
            $table->dateTime('profit_distribution_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunities');
    }
};
