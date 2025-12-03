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
        Schema::create('investment_opportunity_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_profile_id')->constrained()->onDelete('cascade');
            $table->string('company_age')->nullable(); // عمر الشركة حسب السجل
            $table->string('commercial_experience')->nullable(); // الخبرة التجارية الشخصية
            $table->string('net_profit_margins')->nullable(); // هوامش الأرباح الصافية
            $table->decimal('required_amount', 15, 2)->nullable(); // المبلغ المطلوب
            $table->text('description')->nullable(); // الوصف
            $table->string('guarantee_type')->nullable(); // نوع الرهن (اختياري)
            $table->string('status')->default('pending'); // حالة الطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunity_requests');
    }
};
