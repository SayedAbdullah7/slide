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
        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
            $table->foreignId('investor_id')->constrained('investor_profiles')->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');

            // Transaction details
            $table->integer('shares')->unsigned()->comment('عدد الأسهم في هذه العملية');
            $table->decimal('share_price', 15, 2)->comment('سعر السهم وقت الشراء');
            $table->decimal('total_investment', 15, 2)->comment('المبلغ (shares × share_price)');
            $table->decimal('total_payment_required', 15, 2)->comment('إجمالي المبلغ المطلوب (بما في ذلك رسوم الشحن)');
            $table->enum('investment_type', ['myself', 'authorize']);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['investment_id', 'created_at']);
            $table->index(['investor_id', 'opportunity_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_transactions');
    }
};
