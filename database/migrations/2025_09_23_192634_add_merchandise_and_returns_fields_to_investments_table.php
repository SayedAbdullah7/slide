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
        Schema::table('investments', function (Blueprint $table) {
            // Merchandise tracking fields
            $table->enum('merchandise_status', ['pending', 'arrived'])->default('pending')->after('investment_date');
            $table->dateTime('expected_delivery_date')->nullable()->after('merchandise_status');
            $table->dateTime('merchandise_arrived_at')->nullable()->after('expected_delivery_date');

            // Actual returns fields (for authorize type)
            $table->decimal('actual_return_amount', 15, 2)->nullable()->after('merchandise_arrived_at');
            $table->decimal('actual_net_return', 15, 2)->nullable()->after('actual_return_amount');
            $table->dateTime('actual_returns_recorded_at')->nullable()->after('actual_net_return');

            // Distribution fields
            $table->enum('distribution_status', ['pending', 'distributed'])->default('pending')->after('actual_returns_recorded_at');
            $table->decimal('distributed_amount', 15, 2)->nullable()->after('distribution_status');
            $table->dateTime('distributed_at')->nullable()->after('distributed_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn([
                'merchandise_status',
                'expected_delivery_date',
                'merchandise_arrived_at',
                'actual_return_amount',
                'actual_net_return',
                'actual_returns_recorded_at',
                'distribution_status',
                'distributed_amount',
                'distributed_at',
            ]);
        });
    }
};
