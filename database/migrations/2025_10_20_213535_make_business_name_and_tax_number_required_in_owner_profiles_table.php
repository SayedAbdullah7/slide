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
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->string('business_name')->nullable(false)->change();
            $table->string('tax_number')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->string('business_name')->nullable()->change();
            $table->string('tax_number')->nullable()->change();
        });
    }
};
