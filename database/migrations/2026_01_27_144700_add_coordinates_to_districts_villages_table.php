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
        // Add latitude and longitude to districts table if they don't exist
        if (!Schema::hasColumn('districts', 'latitude')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
            });
        }
        
        // Add latitude and longitude to villages table if they don't exist
        if (!Schema::hasColumn('villages', 'latitude')) {
            Schema::table('villages', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('districts', 'latitude')) {
            Schema::table('districts', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }
        
        if (Schema::hasColumn('villages', 'latitude')) {
            Schema::table('villages', function (Blueprint $table) {
                $table->dropColumn(['latitude', 'longitude']);
            });
        }
    }
};
