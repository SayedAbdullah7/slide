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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('Content type: privacy_policy, terms_conditions, about_app');
            $table->string('title')->comment('Title');
            $table->text('content')->comment('Content');
            $table->date('last_updated')->nullable()->comment('Last update date');
            $table->boolean('is_active')->default(true)->comment('Whether content is active');
            $table->timestamps();

            $table->unique('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
