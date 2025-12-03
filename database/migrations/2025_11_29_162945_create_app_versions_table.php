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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version'); // e.g., "1.0.0"
            $table->enum('os', ['ios', 'android']); // Operating system
            $table->boolean('is_mandatory')->default(false); // Is update mandatory
            $table->text('release_notes')->nullable(); // Release notes
            $table->text('release_notes_ar')->nullable(); // Release notes in Arabic
            $table->boolean('is_active')->default(true); // Is this version active
            $table->timestamp('released_at')->nullable(); // Release date
            $table->timestamps();

            // Unique constraint: version must be unique per OS (can have same version for iOS and Android)
            $table->unique(['version', 'os']);

            // Indexes for faster queries
            $table->index(['os', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
