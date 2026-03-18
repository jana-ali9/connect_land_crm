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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('cascade');
            $table->foreignId('land_id')->nullable()->constrained('lands')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('base_rent', 10, 2);
            $table->decimal('increase_rate', 10, 2)->default(0);
            $table->integer('increase_frequency')->default(12);
            $table->string('insurance')->nullable();
            $table->string('contract_video')->nullable();
            $table->enum('contract_status', ['active', 'expired', 'suspended'])->default('active');
            $table->enum('contract_type', ['rent', 'sale'])->default('rent');
            $table->enum('property_type', ['building', 'land'])->default('building');
            $table->decimal('amount_for_services', 10, 2)->default(0);
            $table->date('services_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};


/**
 * ALTER TABLE contracts
* ADD COLUMN building_id BIGINT UNSIGNED NULL,
*ADD CONSTRAINT fk_contracts_building_id
*FOREIGN KEY (building_id)
*REFERENCES buildings(id)
*ON DELETE CASCADE;

 */