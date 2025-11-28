<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Add processed_at column
            $table->timestamp('processed_at')->nullable()->after('completed_at');
        });

        // Rename approved_by to action_by using DB facade since renameColumn might not work in all DBs
        DB::statement('ALTER TABLE withdrawal_requests CHANGE approved_by action_by BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE withdrawal_requests DROP FOREIGN KEY withdrawal_requests_approved_by_foreign');
        DB::statement('ALTER TABLE withdrawal_requests ADD CONSTRAINT withdrawal_requests_action_by_foreign FOREIGN KEY (action_by) REFERENCES admins(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Drop processed_at
            $table->dropColumn('processed_at');
        });

        // Rename back
        DB::statement('ALTER TABLE withdrawal_requests CHANGE action_by approved_by BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE withdrawal_requests DROP FOREIGN KEY withdrawal_requests_action_by_foreign');
        DB::statement('ALTER TABLE withdrawal_requests ADD CONSTRAINT withdrawal_requests_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES admins(id) ON DELETE SET NULL');
    }
};
