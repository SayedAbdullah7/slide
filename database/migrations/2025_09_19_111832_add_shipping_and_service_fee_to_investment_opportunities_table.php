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
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->decimal('shipping_and_service_fee', 10, 2)->nullable()->after('expected_net_return_by_authorize');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->dropColumn('shipping_and_service_fee');
        });
    }
};
