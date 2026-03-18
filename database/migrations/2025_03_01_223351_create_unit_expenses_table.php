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
        Schema::create('unit_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_name');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade'); 
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('cascade'); 
            $table->foreignId('land_id')->nullable()->constrained('lands')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('allocation_type', ['unit', 'building', 'land'])->default('unit');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_expenses');
    }
};
