<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update unique constraint to allow same version for different OS
     */
    public function up(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            // Drop the old unique constraint on version column only
            // Laravel creates unique indexes with table name prefix
            $table->dropUnique(['version']);
        });

        Schema::table('app_versions', function (Blueprint $table) {
            // Add new unique constraint on combination of version and os
            // This allows same version for iOS and Android separately
            $table->unique(['version', 'os'], 'app_versions_version_os_unique');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_versions', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('app_versions_version_os_unique');
        });

        Schema::table('app_versions', function (Blueprint $table) {
            // Restore the old unique constraint on version only
            $table->unique('version');
        });
    }
};
