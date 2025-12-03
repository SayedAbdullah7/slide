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
        Schema::create('guarantees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_opportunity_id')->constrained()->onDelete('cascade');
            $table->string('type'); // نوع الضمان (رهن عقاري، كفالة بنكية، إلخ)
            $table->string('name');
            $table->text('description')->nullable(); // وصف الضمان
            $table->decimal('value', 15, 2)->nullable(); // قيمة الضمان
            $table->string('currency', 3)->default('SAR'); // العملة
            $table->boolean('is_verified')->default(false); // هل تم التحقق من الضمان
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الضمان
            $table->string('document_number')->nullable(); // رقم الوثيقة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantees');
    }
};
