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
        Schema::create('opportunity_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');

            $table->string('type'); // مثل: summary, terms, brochure
            $table->string('file_path');
            $table->bigInteger('file_size')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunity_attachments');
    }
};
