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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('set null');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('invoice_date');
            $table->decimal('amount_due', 10);
            $table->decimal('services_cost', 10)->default(0);
            $table->decimal('amount_paid', 10)->default(0);
            $table->enum('status', ['pending', 'paid', 'overdue','suspended'])->default('pending');
            $table->enum('type', ['unit', 'service','land'])->default('unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
