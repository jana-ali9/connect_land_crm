<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('lands', function (Blueprint $table) {
            if (!Schema::hasColumn('lands','address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('lands','country')) {
                $table->string('country', 2)->nullable();
            }
            if (!Schema::hasColumn('lands','lat')) {
                $table->decimal('lat', 10, 7)->nullable()->index();
            }
            if (!Schema::hasColumn('lands','lng')) {
                $table->decimal('lng', 10, 7)->nullable()->index();
            }
        });

        Schema::table('buildings', function (Blueprint $table) {
            // buildings already has 'address', so only add the missing fields
            if (!Schema::hasColumn('buildings','country')) {
                $table->string('country', 2)->nullable()->after('address');
            }
            if (!Schema::hasColumn('buildings','lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('country')->index();
            }
            if (!Schema::hasColumn('buildings','lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat')->index();
            }
        });
    }

    public function down(): void {
        Schema::table('lands', function (Blueprint $table) {
            if (Schema::hasColumn('lands','country')) {
                $table->dropColumn(['country']);
            }
            if (Schema::hasColumn('lands','lat')) {
                $table->dropColumn(['lat']);
            }
            if (Schema::hasColumn('lands','lng')) {
                $table->dropColumn(['lng']);
            }
            if (Schema::hasColumn('lands','address')) {
                $table->dropColumn(['address']);
            }
        });

        Schema::table('buildings', function (Blueprint $table) {
            if (Schema::hasColumn('buildings','country')) {
                $table->dropColumn(['country']);
            }
            if (Schema::hasColumn('buildings','lat')) {
                $table->dropColumn(['lat']);
            }
            if (Schema::hasColumn('buildings','lng')) {
                $table->dropColumn(['lng']);
            }
        });
    }
};
