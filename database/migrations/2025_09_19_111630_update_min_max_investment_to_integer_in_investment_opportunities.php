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
            $table->integer('min_investment')->change();
            $table->integer('max_investment')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_opportunities', function (Blueprint $table) {
            $table->decimal('min_investment', 10, 2)->change();
            $table->decimal('max_investment', 10, 2)->change();
        });
    }
};
