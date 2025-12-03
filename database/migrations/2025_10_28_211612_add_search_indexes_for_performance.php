<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adding indexes for search performance on frequently queried columns
     */
    public function up(): void
    {
        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            // Index on email for fast lookups
            $table->index('email');

            // Index on is_active for filtering active users
            $table->index('is_active');

            // Index on created_at for date filtering and sorting
            $table->index('created_at');
        });

        // Add indexes to investor_profiles table
        Schema::table('investor_profiles', function (Blueprint $table) {
            // Index on full_name for searching investor names
            $table->index('full_name');

            // Index on national_id for searching by national ID
            $table->index('national_id');
        });

        // Add indexes to owner_profiles table
        Schema::table('owner_profiles', function (Blueprint $table) {
            // Index on business_name for searching business names
            $table->index('business_name');

            // Index on tax_number for searching by tax number
            $table->index('tax_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });

        // Remove indexes from investor_profiles table
        Schema::table('investor_profiles', function (Blueprint $table) {
            $table->dropIndex(['full_name']);
            $table->dropIndex(['national_id']);
        });

        // Remove indexes from owner_profiles table
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropIndex(['business_name']);
            $table->dropIndex(['tax_number']);
        });
    }
};
