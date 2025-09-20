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
            $table->string('guarantee')->nullable()->after('fund_goal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->dropColumn('guarantee');
        });
    }
};
