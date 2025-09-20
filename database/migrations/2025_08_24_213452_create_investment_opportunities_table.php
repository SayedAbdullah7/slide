<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\InvestmentStatusEnum;
use App\RiskLevelEnum;
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

            // Dynamic enum values
            $table->enum('status', InvestmentStatusEnum::values())
                ->default(InvestmentStatusEnum::OPEN->value);

            $table->string('risk_level')->nullable(); // Optionally switch to enum if you want

            $table->decimal('target_amount', 15, 2);
            $table->decimal('price_per_share', 10, 2);
            $table->integer('reserved_shares')->default(0);
            $table->integer('investment_duration')->nullable();

            // $table->decimal('expected_return_amount', 15, 2)->nullable();
            // $table->decimal('expected_net_return', 15, 2)->nullable();
            $table->decimal('expected_return_amount_by_myself', 15, 2)->nullable(); // return amount for one share by myself
            $table->decimal('expected_net_return_by_myself', 15, 2)->nullable();  // net return amount for one share by myself
            $table->decimal('expected_return_amount_by_authorize', 15, 2)->nullable(); // return amount for one share by authorize
            $table->decimal('expected_net_return_by_authorize', 15, 2)->nullable();  // net return amount for one share by authorize
            // $table->decimal('shipping_and_service_fee', 10, 2)->nullable(); // shipping_and_service_fee per share
            $table->decimal('min_investment', 10, 2)->default(0);
            $table->decimal('max_investment', 10, 2)->nullable();

            $table->string('fund_goal')->nullable(); // Optional: switch to enum

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
