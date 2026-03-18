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
        Schema::create('expense_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('unit_expenses')->onDelete('cascade'); // ربط بالعروض الخاصة بالمصروف
            $table->string('company_name'); // اسم الشركة المقدمة للعرض
            $table->decimal('offer_amount', 10, 2); // قيمة العرض
            $table->boolean('status')->default(false); // الحالة (0 = مرفوض، 1 = مقبول)
            $table->text('description')->nullable(); // وصف إضافي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_offers');
    }
};
